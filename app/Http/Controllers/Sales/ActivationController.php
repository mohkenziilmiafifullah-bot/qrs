<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\Qr;
use App\Models\WalletTransaction;
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
            'target_url' => ['required', 'url'],
        ]);

        $sales = $request->user();
        $fee = self::ACTIVATION_FEE;

        if ($sales->wallet_balance < $fee) {
            return back()->withErrors(['code' => 'Saldo deposit tidak mencukupi, silakan Top-Up.']);
        }

        $qr = Qr::where('code', $data['code'])->first();

        if ($qr->status === 'active') {
            return back()->withErrors(['code' => 'QR Code ini sudah diaktivasi sebelumnya.']);
        }

        DB::transaction(function () use ($sales, $qr, $data, $fee) {
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
                'target_url' => $data['target_url'],
                'status' => 'active',
                'activated_at' => now(),
            ]);
        });

        return redirect()->route('sales.dashboard')->with('success', 'QR Code berhasil diaktivasi!');
    }

    public function history(Request $request): Response
    {
        $qrs = $request->user()->qrs()
            ->where('status', 'active')
            ->latest('activated_at')
            ->paginate(20);

        return Inertia::render('Sales/History', ['qrs' => $qrs]);
    }
}
