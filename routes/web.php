<?php

use App\Http\Controllers\RedirectController;
use App\Http\Controllers\Sales\ActivationController;
use App\Http\Controllers\Sales\DashboardController;
use App\Http\Controllers\Sales\DepositController;
use App\Http\Controllers\Sales\QrBatchController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Public Redirect Engine
|--------------------------------------------------------------------------
| This is what gets scanned off the physical acrylic QR sticker in the
| merchant's store. Must stay outside auth/session middleware for speed.
*/
Route::get('/q/{code}', RedirectController::class)->name('qr.redirect');
Route::get('/q-tidak-aktif', fn () => Inertia::render('QrInactive'))->name('qr.inactive');

/*
|--------------------------------------------------------------------------
| Sales Field PWA (Inertia + React)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:sales'])->prefix('sales')->name('sales.')->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');

    Route::get('/scan', [ActivationController::class, 'scanner'])->name('scan');
    Route::get('/activate/{code}', [ActivationController::class, 'form'])->name('activate.form');
    Route::post('/activate', [ActivationController::class, 'activate'])->name('activate.store');
    Route::get('/history', [ActivationController::class, 'history'])->name('history');

    Route::get('/deposit', [DepositController::class, 'create'])->name('deposit.create');
    Route::post('/deposit', [DepositController::class, 'store'])->name('deposit.store');

    Route::get('/batch', [QrBatchController::class, 'create'])->name('batch.create');
    Route::post('/batch', [QrBatchController::class, 'store'])->name('batch.store');
    Route::get('/batch/{batchReference}/download', [QrBatchController::class, 'download'])->name('batch.download');
});

// Filament serves the Admin panel itself at /admin (registered via AdminPanelProvider).

require __DIR__.'/auth.php';
