<?php

use App\Http\Controllers\PosController;
use Illuminate\Support\Facades\Route;

// POS checkout endpoint used by the Filament Cashier POS page
Route::post('/cashier/pos/checkout', [PosController::class, 'checkout'])
	->name('pos.checkout')
	->middleware(['auth']);

// require __DIR__.'/settings.php';
