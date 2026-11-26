<?php

namespace App\Services\Theme;

use App\Enums\ThemeSettingScope;
use LogicException;

class ThemeSettingsRegistry
{
    private array $sections = [];

    private ?string $activeSection = null;

    private string $activeScope = ThemeSettingScope::THEME_SPECIFIC->value;

    public function section(
        string $key,
        string $label,
        ?string $description = null,
        string $scope = 'theme_specific',
    ): self {
        $resolvedScope = ThemeSettingScope::tryFrom($scope)?->value;
        if ($resolvedScope === null) {
            throw new LogicException('Theme setting scope không hợp lệ: '.$scope);
        }

        $this->sections[$key] = [
            'key' => $key,
            'label' => $label,
            'description' => $description,
            'scope' => $resolvedScope,
            'fields' => $this->sections[$key]['fields'] ?? [],
        ];
        $this->activeSection = $key;
        $this->activeScope = $resolvedScope;

        return $this;
    }

    public function globalSection(string $key, string $label, ?string $description = null): self
    {
        return $this->section($key, $label, $description, ThemeSettingScope::GLOBAL->value);
    }

    public function commonThemeSection(string $key, string $label, ?string $description = null): self
    {
        return $this->section($key, $label, $description, ThemeSettingScope::COMMON_THEME->value);
    }

    public function themeSpecificSection(string $key, string $label, ?string $description = null): self
    {
        return $this->section($key, $label, $description, ThemeSettingScope::THEME_SPECIFIC->value);
    }

    public function text(string $key, string $label, mixed $default = null, ?string $description = null): self
    {
        return $this->field($key, $label, 'text', compact('default', 'description'));
    }

    public function textarea(string $key, string $label, mixed $default = null, ?string $description = null): self
    {
        return $this->field($key, $label, 'textarea', compact('default', 'description'));
    }

    public function color(string $key, string $label, string $default = '#000000', ?string $description = null): self
    {
        return $this->field($key, $label, 'color', compact('default', 'description'));
    }

    public function number(string $key, string $label, int $default = 0, ?int $min = null, ?int $max = null, ?string $description = null): self
    {
        return $this->field($key, $label, 'number', compact('default', 'min', 'max', 'description'));
    }

    public function boolean(string $key, string $label, bool $default = false, ?string $description = null): self
    {
        return $this->field($key, $label, 'boolean', compact('default', 'description'));
    }

    public function select(string $key, string $label, array $options, mixed $default = null, ?string $description = null): self
    {
        return $this->field($key, $label, 'select', compact('default', 'options', 'description'));
    }

    public function schema(): array
    {
        return ['sections' => array_values($this->sections)];
    }

    private function field(string $key, string $label, string $type, array $attributes): self
    {
        if (! $this->activeSection) {
            throw new LogicException('Theme settings must define a section before registering fields.');
        }

        foreach ($this->sections as $section) {
            foreach ($section['fields'] as $field) {
                if ($field['key'] === $key) {
                    throw new LogicException('Theme setting key bị trùng: '.$key);
                }
            }
        }

        $this->sections[$this->activeSection]['fields'][] = [
            'key' => $key,
            'label' => $label,
            'type' => $type,
            'scope' => $this->activeScope,
            ...array_filter($attributes, static fn (mixed $value): bool => $value !== null),
        ];

        return $this;
    }
}
