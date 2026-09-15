<?php

namespace App\DTOs\Language;

use Illuminate\Http\Request;

readonly class LanguageDTO
{
    public function __construct(
        public string $code,
        public string $name,
        public string $native_name,
        public ?string $flag = null,
        public bool $is_default = false,
        public bool $is_active = true,
        public int $display_order = 0,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $flag = $request->input('flag');
        if ($request->input('remove_flag')) {
            $flag = null;
        } elseif ($uuid = $request->input('flag_media_uuid')) {
            $media = \App\Models\Media::where('uuid', $uuid)->first();
            if ($media) {
                $flag = $media->file_path;
            }
        }

        return new self(
            code: $request->validated('code'),
            name: $request->validated('name'),
            native_name: $request->validated('native_name'),
            flag: $flag,
            is_default: (bool) $request->input('is_default', false),
            is_active: (bool) $request->input('is_active', true),
            display_order: (int) $request->input('display_order', 0),
        );
    }
    
    public function toArray(): array
    {
        return [
            'code' => $this->code,
            'name' => $this->name,
            'native_name' => $this->native_name,
            'flag' => $this->flag,
            'is_default' => $this->is_default,
            'is_active' => $this->is_active,
            'display_order' => $this->display_order,
        ];
    }
}
