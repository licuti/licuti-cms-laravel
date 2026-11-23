<?php

namespace App\Http\Requests\Admin\ContentBlock;

use App\Models\Language;

class UpdateContentBlockRequest extends StoreContentBlockRequest
{
    protected function permission(): ?string
    {
        return 'content-blocks.update';
    }

    public function rules(): array
    {
        $rules = parent::rules();
        $defaultLocale = Language::where('is_default', true)->value('code') ?? app()->getLocale();
        $currentLocale = request()->query('lang') ?: $defaultLocale;

        $rules["translations.{$defaultLocale}.title"] = ['nullable', 'string', 'max:255'];
        $rules["translations.{$currentLocale}.title"] = ['required', 'string', 'max:255'];

        return $rules;
    }
}
