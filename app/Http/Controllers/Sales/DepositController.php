<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\DepositRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class DepositController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Sales/DepositCreate', [
            // Destination account is app-level config, not hardcoded here — see config/deposit.php
            'destination' => config('deposit.destination'),
            'walletBalance' => auth()->user()->wallet_balance,
            'pending' => auth()->user()->depositRequests()->where('status', 'pending')->latest()->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:10000'],
            'proof' => ['required', 'image', 'max:5120'], // 5MB max
        ]);

        $path = $request->file('proof')->store('deposit-proofs', 'public');

        DepositRequest::create([
            'sales_id' => $request->user()->id,
            'amount' => $data['amount'],
            'proof_path' => $path,
            'status' => 'pending',
        ]);

        return redirect()->route('sales.deposit.create')
            ->with('success', 'Bukti transfer terkirim, menunggu approval Admin.');
    }
}
