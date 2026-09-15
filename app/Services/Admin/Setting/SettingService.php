<?php

namespace App\Services\Admin\Setting;

use App\Repositories\Interfaces\SettingRepositoryInterface;
use Illuminate\Support\Facades\Cache;

class SettingService
{
    const CACHE_KEY = 'app_settings';

    public function __construct(
        private readonly SettingRepositoryInterface $settingRepository
    ) {}

    /**
     * Lấy toàn bộ settings theo nhóm
     */
    public function getGroup(string $group)
    {
        return $this->settingRepository->getByGroup($group);
    }

    /**
     * Cập nhật danh sách settings
     */
    public function saveGroup(string $group, array $data)
    {
        $settings = $this->getGroup($group);

        foreach ($settings as $setting) {
            $key = $setting->key;

            if ($setting->type === 'image') {
                $uuid = $data[$key . '_media_uuid'] ?? null;
                $remove = $data['remove_' . $key] ?? '0';

                if ($remove === '1') {
                    $this->settingRepository->updateOrCreateByKey($key, ['value' => null]);
                } elseif ($uuid) {
                    $media = \App\Models\Media::where('uuid', $uuid)->first();
                    if ($media) {
                        $this->settingRepository->updateOrCreateByKey($key, ['value' => $media->file_path]);
                    }
                } elseif (isset($data[$key])) {
                    $this->settingRepository->updateOrCreateByKey($key, ['value' => $data[$key]]);
                }
            } elseif (isset($data[$key])) {
                $value = $data[$key];

                // Nếu là field đa ngôn ngữ, value từ request gửi lên thường là mảng dạng ['vi' => '...', 'en' => '...']
                // Ta cần encode thành JSON trước khi lưu
                if ($setting->is_translatable && is_array($value)) {
                    $value = json_encode($value, JSON_UNESCAPED_UNICODE);
                }

                $this->settingRepository->updateOrCreateByKey($key, ['value' => $value]);
            } else {
                // Xử lý trường hợp checkbox/toggle không gửi lên khi off (nếu cần thiết)
                // Hoặc clear giá trị nếu form không gửi lên nhưng vẫn thuộc nhóm
                if ($setting->type === 'boolean') {
                    $this->settingRepository->updateOrCreateByKey($key, ['value' => '0']);
                }
            }
        }

        // Xóa cache sau khi cập nhật
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Lấy toàn bộ setting dạng key-value để cache
     */
    public function getAllCached()
    {
        return Cache::rememberForever(self::CACHE_KEY, function () {
            // Lấy tất cả setting, mapping sang dạng array key => value
            $all = $this->settingRepository->all();
            $result = [];
            
            foreach ($all as $item) {
                $val = $item->value;
                if ($item->is_translatable) {
                    $decoded = json_decode($val, true);
                    if (is_array($decoded)) {
                        $val = $decoded;
                    }
                }
                $result[$item->key] = $val;
            }
            
            return $result;
        });
    }

    /**
     * Lấy một giá trị setting
     */
    public function get(string $key, $default = null)
    {
        $settings = $this->getAllCached();
        
        if (!isset($settings[$key])) {
            return $default;
        }

        $value = $settings[$key];

        // Nếu value là mảng (do is_translatable parse từ JSON), lấy theo locale hiện tại
        if (is_array($value)) {
            $locale = app()->getLocale();
            $fallback = config('app.fallback_locale', 'vi');
            
            return $value[$locale] ?? $value[$fallback] ?? $default;
        }

        return $value;
    }
}
