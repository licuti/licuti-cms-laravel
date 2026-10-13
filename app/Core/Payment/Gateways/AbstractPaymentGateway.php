<?php

namespace App\Core\Payment\Gateways;

use App\Core\Payment\Contracts\PaymentGatewayInterface;
use App\Core\Payment\DTOs\RefundResult;
use Illuminate\Support\Arr;

abstract class AbstractPaymentGateway implements PaymentGatewayInterface
{
    protected array $config = [];

    public function setConfig(array $config): static
    {
        $this->config = $config;
        return $this;
    }

    public function getConfig(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->config;
        }

        return Arr::get($this->config, $key, $default);
    }

    public function getDescription(): string
    {
        return '';
    }

    public function getConfigFields(): array
    {
        return [];
    }

    public function refund(object $payment, float $amount): RefundResult
    {
        return RefundResult::failed(__('Cổng thanh toán này không hỗ trợ hoàn tiền tự động qua API.'));
    }

    public function handleWebhook(\Illuminate\Http\Request $request): \App\Core\Payment\DTOs\PaymentResult
    {
        return \App\Core\Payment\DTOs\PaymentResult::failed(__('Cổng thanh toán này không hỗ trợ Webhook.'));
    }
}
