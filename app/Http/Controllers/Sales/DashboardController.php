<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user();

        return Inertia::render('Sales/Dashboard', [
            'walletBalance' => $user->wallet_balance,
            'activatedCount' => $user->qrs()->where('status', 'active')->count(),
            'unassignedCount' => $user->qrs()->where('status', 'unassigned')->count(),
            'inactiveCount' => $user->qrs()->whereNotIn('status', ['active', 'unassigned'])->count(),
            'latestQrStatus' => $user->qrs()
                ->latest()
                ->first(['code', 'status', 'merchant_name', 'created_at']),
            'recentActivations' => $user->qrs()
                ->where('status', 'active')
                ->latest('activated_at')
                ->limit(5)
                ->get(['code', 'merchant_name', 'activated_at']),
        ]);
    }
}
