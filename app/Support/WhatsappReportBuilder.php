<?php

namespace App\Support;

use App\Models\Qr;
use Carbon\Carbon;
use Illuminate\Support\Carbon as SupportCarbon;

class WhatsappReportBuilder
{
    /**
     * A store's one-time WhatsApp report unlocks only after it has been active
     * (aktif) for this many days. It is then sent at most once, ever.
     */
    protected const ELIGIBILITY_DAYS = 30;

    /**
     * Compute the one-time scan summary for a store: total scans since activation,
     * its busiest 2-hour window, and its single busiest day.
     */
    public static function analytics(Qr $qr): array
    {
        $scans = $qr->scanLogs()->get(['scanned_at', 'source']);

        return [
            'total_scan' => $scans->count(),
            'peak_hours' => self::peakHours($scans),
            'top_day' => self::topDay($scans),
            // Additive: breakdown by tap source. Existing keys above are
            // untouched, so any code still reading only those three keeps working.
            'total_scan_qr' => $scans->where('source', 'qr')->count(),
            'total_scan_nfc' => $scans->where('source', 'nfc')->count(),
        ];
    }

    /**
     * Busiest 2-hour window (00-02, 01-03, ... 22-24) across all of the store's scans.
     */
    protected static function peakHours($scans): string
    {
        if ($scans->isEmpty()) {
            return 'Belum ada data';
        }

        $countsByHour = array_fill(0, 24, 0);

        foreach ($scans as $scan) {
            $countsByHour[SupportCarbon::parse($scan->scanned_at)->hour]++;
        }

        $windowSums = [];
        for ($start = 0; $start <= 22; $start++) {
            $windowSums[$start] = $countsByHour[$start] + $countsByHour[$start + 1];
        }

        $peakStart = array_search(max($windowSums), $windowSums);

        return sprintf('%02d.00 - %02d.00 WIB', $peakStart, $peakStart + 2);
    }

    /**
     * The single day name (Indonesian) with the most scans across the store's history.
     */
    protected static function topDay($scans): string
    {
        if ($scans->isEmpty()) {
            return 'Belum ada data';
        }

        $dayNames = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $countsByDay = array_fill(0, 7, 0);

        foreach ($scans as $scan) {
            $countsByDay[SupportCarbon::parse($scan->scanned_at)->dayOfWeek]++;
        }

        arsort($countsByDay);
        $topDayIndex = array_key_first($countsByDay);

        return $countsByDay[$topDayIndex] > 0 ? $dayNames[$topDayIndex] : 'Belum ada data';
    }

    /**
     * Whether the one-time "Kirim Laporan WA" button may be clicked right now.
     * Once a report has been sent, this is false forever for that store.
     */
    public static function canSendReport(Qr $qr): bool
    {
        if ($qr->last_report_sent_at !== null) {
            return false;
        }

        return $qr->activated_at !== null
            && $qr->activated_at->diffInDays(Carbon::now()) >= self::ELIGIBILITY_DAYS;
    }

    /**
     * The date the report unlocks, for a store that hasn't reached 30 days active yet.
     * Null once the store is eligible or has already sent its (one and only) report.
     */
    public static function eligibleDate(Qr $qr): ?string
    {
        if ($qr->last_report_sent_at !== null || $qr->activated_at === null) {
            return null;
        }

        $eligibleAt = $qr->activated_at->copy()->addDays(self::ELIGIBILITY_DAYS);

        return $eligibleAt->isFuture() ? $eligibleAt->format('d M Y') : null;
    }

    /**
     * Build the plain-text one-time report message for a store.
     */
    public static function message(Qr $qr, array $a): string
    {
        return <<<TEXT
Halo Kak {$qr->merchant_name}! 👋
Berikut ringkasan performa QR Review Google Maps kamu bulan ini:

* 📊 Total Scan: {$a['total_scan']} kali
* ⏰ Jam Paling Rame: {$a['peak_hours']}
* 📅 Hari Teramai: {$a['top_day']}

Semakin banyak scan, semakin besar peluang tempat kamu dapat Bintang 5 di Google! 🚀 — Tim Bherung
TEXT;
    }

    /**
     * Build the full wa.me deep link (phone + URL-encoded message) for a store.
     */
    public static function waLink(Qr $qr, array $a): string
    {
        return 'https://wa.me/'.$qr->phone_number.'?text='.rawurlencode(self::message($qr, $a));
    }
}
