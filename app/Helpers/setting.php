<?php

use App\Services\Admin\Setting\SettingService;

if (!function_exists('setting')) {
    /**
     * Get a setting value by key
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function setting(string $key, $default = null)
    {
        try {
            // Sử dụng helper app() để resolve Service
            $settingService = app(SettingService::class);
            return $settingService->get($key, $default);
        } catch (\Exception $e) {
            return $default;
        }
    }
}
