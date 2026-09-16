<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\Qr;
use App\Support\WhatsappReportBuilder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * Mark the one-time WhatsApp report as sent for a store. This can only
     * ever succeed once per store — the button is permanently disabled after.
     */
    public function markSent(Request $request, Qr $qr): RedirectResponse
    {
        abort_unless($qr->sales_id === $request->user()->id, 403);
        abort_unless(WhatsappReportBuilder::canSendReport($qr), 422, 'Laporan untuk toko ini belum bisa dikirim.');

        $qr->update(['last_report_sent_at' => now()]);

        return back();
    }
}
