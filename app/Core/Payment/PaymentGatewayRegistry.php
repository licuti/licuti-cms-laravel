<?php

namespace App\Core\Payment;

use App\Core\Payment\Contracts\PaymentGatewayInterface;
use InvalidArgumentException;

class PaymentGatewayRegistry
{
    /**
     * Danh sách các Gateway đã đăng ký: [code => class_or_instance]
     */
    protected array $gateways = [];

    /**
     * Cache các instance đã khởi tạo theo code: [code => instance]
     */
    protected array $instances = [];

    /**
     * Đăng ký một Gateway mới vào hệ thống.
     *
     * @param string $code Mã cổng thanh toán (ví dụ: 'cod', 'vnpay', 'zalopay')
     * @param string|PaymentGatewayInterface $gateway Tên class hoặc object instance
     */
    public function register(string $code, string|PaymentGatewayInterface $gateway): void
    {
        $this->gateways[strtolower($code)] = $gateway;
        unset($this->instances[strtolower($code)]);
    }

    /**
     * Kiểm tra cổng thanh toán đã được đăng ký chưa.
     */
    public function has(string $code): bool
    {
        return isset($this->gateways[strtolower($code)]);
    }

    /**
     * Lấy Gateway Driver theo mã và nạp cấu hình (nếu có).
     */
    public function get(string $code, array $config = []): ?PaymentGatewayInterface
    {
        $code = strtolower($code);
        if (!$this->has($code)) {
            return null;
        }

        $gateway = $this->gateways[$code];

        if ($gateway instanceof PaymentGatewayInterface) {
            $instance = clone $gateway;
        } elseif (is_string($gateway) && class_exists($gateway)) {
            $instance = app($gateway);
        } else {
            throw new InvalidArgumentException("Gateway [{$code}] không hợp lệ.");
        }

        if (!empty($config)) {
            $instance->setConfig($config);
        }

        return $instance;
    }

    /**
     * Lấy toàn bộ danh sách Gateways đã đăng ký.
     *
     * @return array<string, PaymentGatewayInterface>
     */
    public function all(): array
    {
        $all = [];
        foreach (array_keys($this->gateways) as $code) {
            $all[$code] = $this->get($code);
        }
        return $all;
    }

    /**
     * Lấy danh sách options [code => Name] để hiển thị dropdown Admin.
     */
    public function getOptions(): array
    {
        $options = [];
        foreach ($this->all() as $code => $gateway) {
            $options[$code] = $gateway->getName();
        }
        return $options;
    }

    /**
     * Lấy danh sách trường cấu hình của một Gateway cụ thể.
     */
    public function getConfigFieldsFor(string $code): array
    {
        $gateway = $this->get($code);
        return $gateway ? $gateway->getConfigFields() : [];
    }

    /**
     * Lấy toàn bộ schema cấu hình của tất cả các gateway (để truyền ra Blade/JS).
     */
    public function getAllConfigSchemas(): array
    {
        $schemas = [];
        foreach ($this->all() as $code => $gateway) {
            $schemas[$code] = [
                'name'        => $gateway->getName(),
                'description' => $gateway->getDescription(),
                'fields'      => $gateway->getConfigFields(),
            ];
        }
        return $schemas;
    }
}
