<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class Qr extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'sales_id', 'merchant_name', 'google_place_id', 'target_url', 'status', 'batch_reference', 'activated_at',
    ];

    protected function casts(): array
    {
        return [
            'activated_at' => 'datetime',
        ];
    }

    public function sales(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sales_id');
    }

    public function scanLogs(): HasMany
    {
        return $this->hasMany(QrScanLog::class);
    }

    /**
     * Cache key for the redirect engine. Cache driver is set via CACHE_STORE in .env
     * (use "redis" on infra that supports it, "database" as a safe default on shared hosting).
     */
    public static function cacheKey(string $code): string
    {
        return "qr:redirect:{$code}";
    }

    public static function cachedLookup(string $code): ?array
    {
        return Cache::remember(self::cacheKey($code), now()->addMinutes(10), function () use ($code) {
            $qr = self::where('code', $code)->first(['id', 'status', 'target_url']);

            if (! $qr) {
                return null;
            }

            return [
                'id' => $qr->id,
                'status' => $qr->status,
                'target_url' => $qr->target_url,
            ];
        });
    }

    protected static function booted(): void
    {
        // Bust the redirect cache whenever a QR's status/URL changes (e.g. on activation or suspension).
        static::saved(fn (Qr $qr) => Cache::forget(self::cacheKey($qr->code)));
        static::deleted(fn (Qr $qr) => Cache::forget(self::cacheKey($qr->code)));
    }
}
