<?php

namespace App\Services\Theme;

use App\Contracts\Theme\ThemeSettingsProvider;
use App\Repositories\Interfaces\ThemeSettingRepositoryInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ThemeSettingsService
{
    public function __construct(
        private readonly ThemeSettingRepositoryInterface $repository,
    ) {}

    public function schema(string $themeSlug): array
    {
        $providerPath = resource_path('themes/'.$themeSlug.'/SettingsProvider.php');

        if (! is_file($providerPath)) {
            return ['sections' => []];
        }

        $provider = require $providerPath;
        if (! $provider instanceof ThemeSettingsProvider) {
            return ['sections' => []];
        }

        $registry = new ThemeSettingsRegistry;
        $provider->register($registry);

        return $registry->schema();
    }

    public function values(string $themeSlug): array
    {
        return Cache::rememberForever($this->cacheKey($themeSlug), function () use ($themeSlug) {
            $values = $this->defaults($themeSlug);

            foreach ($this->repository->getByTheme($themeSlug) as $setting) {
                $values[$setting->key] = $this->decodeValue($setting->value);
            }

            return $values;
        });
    }

    public function save(string $themeSlug, array $input): void
    {
        $fields = $this->fields($themeSlug);
        $values = [];

        foreach ($fields as $key => $field) {
            if (! array_key_exists($key, $input)) {
                $input[$key] = $field['default'] ?? null;
            }

            $values[$key] = $this->normalize($key, $field, $input[$key]);
        }

        DB::transaction(function () use ($themeSlug, $values) {
            foreach ($values as $key => $value) {
                $this->repository->updateOrCreate($themeSlug, $key, $this->encodeValue($value));
            }
        });

        Cache::forget($this->cacheKey($themeSlug));
    }

    public function fields(string $themeSlug): array
    {
        $fields = [];

        foreach ($this->schema($themeSlug)['sections'] as $section) {
            foreach ($section['fields'] ?? [] as $field) {
                if (is_string($field['key'] ?? null) && is_string($field['type'] ?? null)) {
                    $fields[$field['key']] = $field;
                }
            }
        }

        return $fields;
    }

    private function defaults(string $themeSlug): array
    {
        $defaults = [];

        foreach ($this->fields($themeSlug) as $key => $field) {
            $defaults[$key] = $field['default'] ?? null;
        }

        return $defaults;
    }

    private function normalize(string $key, array $field, mixed $value): mixed
    {
        $type = $field['type'];

        if ($type === 'boolean') {
            return filter_var($value, FILTER_VALIDATE_BOOLEAN);
        }

        if ($type === 'color') {
            if (! is_string($value) || ! preg_match('/^#[0-9a-fA-F]{6}$/', $value)) {
                throw ValidationException::withMessages([
                    'settings.'.$key => 'Màu sắc không hợp lệ.',
                ]);
            }

            return $value;
        }

        if ($type === 'number') {
            if (! is_numeric($value)) {
                throw ValidationException::withMessages([
                    'settings.'.$key => 'Giá trị phải là số.',
                ]);
            }

            $number = (int) $value;
            $min = $field['min'] ?? null;
            $max = $field['max'] ?? null;

            if (($min !== null && $number < $min) || ($max !== null && $number > $max)) {
                throw ValidationException::withMessages([
                    'settings.'.$key => 'Giá trị nằm ngoài giới hạn cho phép.',
                ]);
            }

            return $number;
        }

        if ($type === 'select') {
            $options = array_keys($field['options'] ?? []);
            if (! is_string($value) || ! in_array($value, $options, true)) {
                throw ValidationException::withMessages([
                    'settings.'.$key => 'Lựa chọn không hợp lệ.',
                ]);
            }

            return $value;
        }

        if ($type === 'textarea' || $type === 'text') {
            if (! is_string($value)) {
                throw ValidationException::withMessages([
                    'settings.'.$key => 'Giá trị phải là chuỗi.',
                ]);
            }

            return $value;
        }

        throw ValidationException::withMessages([
            'settings.'.$key => 'Kiểu cấu hình chưa được hỗ trợ.',
        ]);
    }

    private function encodeValue(mixed $value): string
    {
        return is_bool($value) || is_array($value)
            ? json_encode($value, JSON_UNESCAPED_UNICODE)
            : (string) $value;
    }

    private function decodeValue(?string $value): mixed
    {
        if ($value === null) {
            return null;
        }

        $decoded = json_decode($value, true);

        return json_last_error() === JSON_ERROR_NONE && (is_bool($decoded) || is_array($decoded))
            ? $decoded
            : $value;
    }

    private function cacheKey(string $themeSlug): string
    {
        return 'theme_settings.'.$themeSlug;
    }
}
