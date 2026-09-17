<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\Qr;
use App\Models\WalletTransaction;
use App\Support\PhoneNumber;
use App\Support\WhatsappReportBuilder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ActivationController extends Controller
{
    protected const ACTIVATION_FEE = 10000;

    public function scanner(): Response
    {
        return Inertia::render('Sales/Scanner');
    }

    public function form(string $code): Response
    {
        $qr = Qr::where('code', $code)->firstOrFail();

        if ($qr->status === 'active') {
            abort(422, 'QR Code ini sudah diaktivasi sebelumnya.');
        }

        return Inertia::render('Sales/ActivationForm', [
            'qr' => ['code' => $qr->code],
            'fee' => self::ACTIVATION_FEE,
            'walletBalance' => auth()->user()->wallet_balance,
        ]);
    }

    public function activate(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'exists:qrs,code'],
            'merchant_name' => ['required', 'string', 'max:255'],
            'phone_number' => ['required', 'string', 'min:10', 'max:15'],
            'google_place_id' => ['nullable', 'string', 'max:255', 'required_without:target_url'],
            'target_url' => ['nullable', 'url', 'required_without:google_place_id'],
        ]);

        $phoneNumber = PhoneNumber::sanitizeToWhatsapp($data['phone_number']);

        // Prefer the Google Place ID: it lets the QR jump straight to the
        // "write a review" star-rating popup instead of the plain Maps page.
        // A manually pasted target_url (legacy flow) is kept as a fallback
        // for merchants without a discoverable Google Business listing.
        $targetUrl = ! empty($data['google_place_id'])
            ? 'https://search.google.com/local/writereview?placeid='.$data['google_place_id']
            : $data['target_url'];

        $sales = $request->user();
        $fee = self::ACTIVATION_FEE;

        if ($sales->wallet_balance < $fee) {
            return back()->withErrors(['code' => 'Saldo deposit tidak mencukupi, silakan Top-Up.']);
        }

        $qr = Qr::where('code', $data['code'])->first();

        if ($qr->status === 'active') {
            return back()->withErrors(['code' => 'QR Code ini sudah diaktivasi sebelumnya.']);
        }

        DB::transaction(function () use ($sales, $qr, $data, $fee, $targetUrl, $phoneNumber) {
            $sales->decrement('wallet_balance', $fee);

            WalletTransaction::create([
                'user_id' => $sales->id,
                'type' => 'debit',
                'amount' => $fee,
                'balance_after' => $sales->fresh()->wallet_balance,
                'description' => "Aktivasi QR {$qr->code} - {$data['merchant_name']}",
                'reference_type' => Qr::class,
                'reference_id' => $qr->id,
            ]);

            $qr->update([
                'sales_id' => $sales->id,
                'merchant_name' => $data['merchant_name'],
                'phone_number' => $phoneNumber,
                'google_place_id' => $data['google_place_id'] ?? null,
                'target_url' => $targetUrl,
                'status' => 'active',
                'activated_at' => now(),
            ]);
        });

        return redirect()->route('sales.dashboard')->with('success', 'QR Code berhasil diaktivasi!');
    }

    public function history(Request $request): Response
    {
        $filter = $request->query('report_filter', 'all');

        $query = $request->user()->qrs()->where('status', 'active');

        if ($filter === 'sent') {
            $query->whereNotNull('last_report_sent_at');
        } elseif ($filter === 'ready') {
            $query->whereNull('last_report_sent_at')
                ->where('activated_at', '<=', now()->subDays(30));
        } elseif ($filter === 'waiting') {
            $query->whereNull('last_report_sent_at')
                ->where('activated_at', '>', now()->subDays(30));
        }

        $qrs = $query->latest('activated_at')->paginate(20)->withQueryString();

        $qrs->getCollection()->transform(function (Qr $qr) {
            $analytics = WhatsappReportBuilder::analytics($qr);
            $canSendReport = WhatsappReportBuilder::canSendReport($qr);

            return array_merge($qr->toArray(), [
                'analytics' => $analytics,
                'can_send_report' => $canSendReport,
                'already_sent' => $qr->last_report_sent_at !== null,
                'eligible_date' => $canSendReport ? null : WhatsappReportBuilder::eligibleDate($qr),
                'wa_report_link' => $canSendReport && $qr->phone_number
                    ? WhatsappReportBuilder::waLink($qr, $analytics)
                    : null,
                // Link siap-salin buat ditulis ke tag NFC lewat app seperti NFC Tools.
                // Sama persis dengan link redirect QR, ditambah ?src=nfc supaya
                // analytics-nya kebaca beda sumber (lihat RedirectController).
                'nfc_link' => route('qr.redirect', $qr->code) . '?src=nfc',
            ]);
        });

        return Inertia::render('Sales/History', [
            'qrs' => $qrs,
            'reportFilter' => $filter,
        ]);
    }
}
