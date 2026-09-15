<?php

namespace App\DTOs\Product;

use Illuminate\Http\Request;

class ProductDTO
{
    public function __construct(
        public readonly array $data,
        public readonly array $translations = []
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            data: $request->except('translations'),
            translations: $request->input('translations', [])
        );
    }

    public function toArray(): array
    {
        return $this->data;
    }
}