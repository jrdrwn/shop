<?php

use App\Http\Controllers\PosController;
use App\Http\Controllers\SubscriptionPaymentController;
use Illuminate\Support\Facades\Route;

// POS checkout endpoint used by the Filament Cashier POS page
Route::post('/cashier/pos/checkout', [PosController::class, 'checkout'])
    ->name('pos.checkout')
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

// require __DIR__.'/settings.php';
