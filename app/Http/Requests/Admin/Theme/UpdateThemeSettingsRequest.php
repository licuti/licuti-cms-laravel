<?php

namespace App\Http\Requests\Admin\Theme;

use App\Core\Traits\AuthorizesWithPermission;
use App\Services\Theme\ThemeManager;
use App\Services\Theme\ThemeSettingsService;
use Illuminate\Foundation\Http\FormRequest;

class UpdateThemeSettingsRequest extends FormRequest
{
    use AuthorizesWithPermission;

    protected function permission(): ?string
    {
        return 'themes.update';
    }

    public function rules(): array
    {
        $slug = (string) $this->route('slug');
        $themeManager = app(ThemeManager::class);
        $theme = $themeManager->find($slug);

        abort_unless($theme, 404);

        $rules = [];
        foreach (app(ThemeSettingsService::class)->fields($slug) as $key => $field) {
            $rules['settings.'.$key] = match ($field['type']) {
                'boolean' => ['sometimes', 'boolean'],
                'color' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
                'number' => array_values(array_filter([
                    'required',
                    'integer',
                    isset($field['min']) ? 'min:'.$field['min'] : null,
                    isset($field['max']) ? 'max:'.$field['max'] : null,
                ])),
                'select' => ['required', 'string', 'in:'.implode(',', array_keys($field['options'] ?? []))],
                'text', 'textarea' => ['nullable', 'string', 'max:5000'],
                default => ['prohibited'],
            };
        }

        return $rules;
    }
}
