<?php

namespace App\DTOs\ContentBlock;

use Illuminate\Http\Request;

class ContentBlockDTO
{
    public function __construct(
        public readonly string $status,
        public readonly ?int $imageMediaId,
        public readonly ?string $publishedAt,
        public readonly array $translations,
        public readonly bool $imageMediaProvided = false,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            status: $request->input('status', 'draft'),
            imageMediaId: $request->boolean('image_remove')
                ? null
                : ($request->filled('image_media_id') ? (int) $request->input('image_media_id') : null),
            publishedAt: $request->input('published_at') ?: null,
            translations: $request->input('translations', []),
            imageMediaProvided: $request->has('image_media_id') || $request->boolean('image_remove'),
        );
    }

    public function toArray(): array
    {
        $data = [
            'status' => $this->status,
            'published_at' => $this->publishedAt,
        ];

        if ($this->imageMediaProvided) {
            $data['image'] = $this->imageMediaId;
        }

        return $data;
    }
}
