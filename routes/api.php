<?php

use App\Http\Controllers\Api\Payment\PaymentWebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->name('api.v1.')->group(function () {
    Route::get('/status', function () {
        return response()->json([
            'status' => 'online',
            'version' => '1.0.0',
            'framework' => 'Laravel ' . app()->version()
        ]);
    });

    // Webhook nhận thông báo thanh toán từ các cổng
    Route::post('/payments/webhook/{gateway}', [PaymentWebhookController::class, 'handle'])
        ->name('payments.webhook');

    // Luồng thanh toán Checkout (API)
    Route::post('/checkout', [\App\Http\Controllers\Api\CheckoutController::class, 'checkout'])
        ->name('checkout');
});
