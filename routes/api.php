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

    // Giỏ hàng (Cart API)
    Route::middleware('throttle:cart')->prefix('cart')->name('cart.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\CartController::class, 'index'])->name('index');
        Route::post('/items', [\App\Http\Controllers\Api\CartController::class, 'addItem'])->name('items.add');
        Route::patch('/items/{itemUuid}', [\App\Http\Controllers\Api\CartController::class, 'updateItem'])->name('items.update');
        Route::delete('/items/{itemUuid}', [\App\Http\Controllers\Api\CartController::class, 'removeItem'])->name('items.remove');
        Route::delete('/', [\App\Http\Controllers\Api\CartController::class, 'clear'])->name('clear');
        Route::post('/coupon', [\App\Http\Controllers\Api\CartController::class, 'applyCoupon'])->name('coupon.apply');
        Route::delete('/coupon', [\App\Http\Controllers\Api\CartController::class, 'removeCoupon'])->name('coupon.remove');
        Route::post('/merge', [\App\Http\Controllers\Api\CartController::class, 'merge'])->middleware('auth:sanctum')->name('merge');
    });

    // Luồng thanh toán Checkout (API)
    Route::post('/checkout', [\App\Http\Controllers\Api\CheckoutController::class, 'checkout'])
        ->name('checkout');
});
