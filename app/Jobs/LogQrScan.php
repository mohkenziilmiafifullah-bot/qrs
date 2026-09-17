<?php

namespace App\Jobs;

use App\Models\QrScanLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Jenssegers\Agent\Agent;

class LogQrScan implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $qrId,
        public ?string $ip,
        public ?string $userAgent,
        public string $scannedAt,
        public string $source = 'qr',
    ) {}

    public function handle(): void
    {
        $agent = new Agent();
        $agent->setUserAgent($this->userAgent ?? '');

        QrScanLog::create([
            'qr_id' => $this->qrId,
            'ip_address' => $this->ip,
            'user_agent' => $this->userAgent,
            'device_type' => $agent->isMobile() ? 'mobile' : ($agent->isTablet() ? 'tablet' : 'desktop'),
            'browser' => $agent->browser() ?: null,
            'source' => $this->source === 'nfc' ? 'nfc' : 'qr',
            'scanned_at' => $this->scannedAt,
        ]);
    }
}
