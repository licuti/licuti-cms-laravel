<?php

namespace App\Providers;

use App\Core\Payment\Gateways\BankTransferGateway;
use App\Core\Payment\Gateways\CODGateway;
use App\Core\Payment\Gateways\MoMoGateway;
use App\Core\Payment\Gateways\PayPalGateway;
use App\Core\Payment\Gateways\PayOSGateway;
use App\Core\Payment\Gateways\SePayGateway;
use App\Core\Payment\Gateways\StripeGateway;
use App\Core\Payment\Gateways\VNPayGateway;
use App\Core\Payment\PaymentGatewayRegistry;
use Illuminate\Support\ServiceProvider;

class PaymentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PaymentGatewayRegistry::class, function () {
            return new PaymentGatewayRegistry();
        });
    }

    public function boot(): void
    {
        $registry = $this->app->make(PaymentGatewayRegistry::class);

        // Cổng nội địa Việt Nam
        $registry->register('cod', CODGateway::class);
        $registry->register('bank_transfer', BankTransferGateway::class);
        $registry->register('vnpay', VNPayGateway::class);
        $registry->register('sepay', SePayGateway::class);
        $registry->register('payos', PayOSGateway::class);
        $registry->register('momo', MoMoGateway::class);

        // Cổng quốc tế
        $registry->register('stripe', StripeGateway::class);
        $registry->register('paypal', PayPalGateway::class);
    }
}
