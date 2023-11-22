<?php
use App\Http\Controllers\KapitalPaymentController;
use Illuminate\Support\Facades\Route;

Route::prefix('payment')->group(function () {
    Route::get('/create-order', [KapitalPaymentController::class, 'createOrder']);
    Route::post('/approve', [KapitalPaymentController::class, 'approve']);
    Route::post('/cancel', [KapitalPaymentController::class, 'cancel']);
    Route::post('/decline', [KapitalPaymentController::class, 'decline']);
    Route::get('/order-status', [KapitalPaymentController::class, 'getOrderStatus']);
});