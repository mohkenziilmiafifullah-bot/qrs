<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QrScanLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'qr_id', 'ip_address', 'user_agent', 'device_type', 'browser', 'scanned_at',
    ];

    protected function casts(): array
    {
        return [
            'scanned_at' => 'datetime',
        ];
    }

    public function qr(): BelongsTo
    {
        return $this->belongsTo(Qr::class);
    }
}
