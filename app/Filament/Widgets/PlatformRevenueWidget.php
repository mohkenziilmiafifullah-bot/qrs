<?php

namespace App\Filament\Widgets;

use App\Models\Qr;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PlatformRevenueWidget extends BaseWidget
{
    protected const ACTIVATION_FEE = 10000;

    protected function getStats(): array
    {
        $totalActivations = Qr::where('status', 'active')->count();
        $todayActivations = Qr::where('status', 'active')->whereDate('activated_at', today())->count();
        $monthActivations = Qr::where('status', 'active')->whereMonth('activated_at', now()->month)->whereYear('activated_at', now()->year)->count();

        return [
            Stat::make('Total Revenue Platform', 'Rp ' . number_format($totalActivations * self::ACTIVATION_FEE, 0, ',', '.'))
                ->description("{$totalActivations} QR aktif × Rp10.000")
                ->color('success'),
            Stat::make('Revenue Bulan Ini', 'Rp ' . number_format($monthActivations * self::ACTIVATION_FEE, 0, ',', '.'))
                ->description("{$monthActivations} aktivasi bulan ini")
                ->color('primary'),
            Stat::make('Aktivasi Hari Ini', (string) $todayActivations)
                ->color('warning'),
            Stat::make('Sales Aktif', (string) User::where('role', 'sales')->where('status', 'active')->count()),
        ];
    }
}
