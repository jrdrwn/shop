<?php

use App\Http\Controllers\PosController;
use App\Http\Controllers\SubscriptionPaymentController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::redirect('/login', '/owner/login')->name('login');

Route::get('/', function () {
    $user = Auth::user();

    if (! $user) {
        return redirect()->route('filament.manager.auth.login');
    }

    return match ($user->role) {
        'super_admin', 'admin' => redirect('/admin'),
        'owner', 'manager' => redirect('/owner'),
        'kasir', 'cashier' => redirect('/cashier'),
        'gudang', 'warehouse' => redirect('/warehouse'),
        default => redirect('/owner/login'),
    };
})->middleware(['auth'])->name('dashboard');

// POS checkout endpoint used by the Filament Cashier POS page
Route::post('/cashier/pos/checkout', [PosController::class, 'checkout'])
    ->name('pos.checkout')
    ->middleware(['auth']);

Route::get('/cashier/pos/check-status/{transactionNumber}', [PosController::class, 'checkStatus'])
    ->name('pos.check-status')
    ->middleware(['auth']);

Route::get('/cashier/pos/finish', [PosController::class, 'finish'])
    ->name('pos.finish');

Route::post('/cashier/pos/cancel/{transactionNumber}', [PosController::class, 'cancelOrder'])
    ->name('pos.cancel')
    ->middleware(['auth']);

// Receipt printing route
Route::get('/transactions/{transaction}/receipt', [TransactionController::class, 'receipt'])
    ->name('transactions.receipt')
    ->middleware(['auth']);

// Subscription payment routes
Route::prefix('subscription')->name('subscription.')->group(function () {
    Route::post('/snap-token', [SubscriptionPaymentController::class, 'getSnapToken'])
        ->name('snap-token')
        ->middleware(['auth']);

    Route::post('/notification', [SubscriptionPaymentController::class, 'handleNotification'])
        ->name('notification');

    Route::get('/finish', [SubscriptionPaymentController::class, 'finish'])
        ->name('finish');

    Route::get('/error', [SubscriptionPaymentController::class, 'error'])
        ->name('error');
});

require __DIR__.'/settings.php';
