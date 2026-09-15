<?php

namespace App\Http\Resources\Media;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MediaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $isImage = str_starts_with((string) $this->mime_type, 'image/');

        return [
            'uuid'            => $this->uuid,
            'name'            => $this->name,
            'file_name'       => $this->file_name,
            'mime_type'       => $this->mime_type,
            'size'            => $this->size,
            'formatted_size'  => $this->formatSize($this->size),
            'collection_name' => $this->collection_name,
            'original_url'    => $this->getUrl(),
            'preview_url'     => $isImage ? ($this->hasGeneratedConversion('thumb') ? $this->getUrl('thumb') : $this->getUrl()) : null,
            'uploaded_by'     => $this->getCustomProperty('uploaded_by'),
            'original_name'   => $this->getCustomProperty('original_name'),
            'created_at'      => $this->created_at?->toIso8601String(),
        ];
    }

    /**
     * Định dạng kích thước file sang KB / MB
     */
    private function formatSize(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 2) . ' MB';
        }
        if ($bytes >= 1024) {
            return round($bytes / 1024, 2) . ' KB';
        }
        return $bytes . ' B';
    }
}
