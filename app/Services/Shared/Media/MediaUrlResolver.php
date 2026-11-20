<?php

namespace App\Services\Shared\Media;

use App\Models\Media;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * Chuyển "giá trị lưu trong cột ảnh" thành URL, KHÔNG phụ thuộc tên cột/model.
 *
 * Hỗ trợ 3 định dạng như dữ liệu hiện có:
 *  - Media UUID  → URL lấy từ Media (full/thumb)
 *  - http(s) URL → giữ nguyên
 *  - path cũ     → disk cấu hình trong config('media.legacy_disk')
 *
 * Scoped singleton: memo theo request để nhiều accessor trên cùng model không query lặp.
 * Model/list nào không eager-load quan hệ media thì gọi prime() trước để gom 1 query.
 */
class MediaUrlResolver
{
    public const VARIANTS = ['full', 'thumb'];

    /** @var array<string, Media|null> uuid => Media (null = đã tra nhưng không tồn tại) */
    private array $media = [];

    /** Đưa một Media đã có sẵn (vd: từ quan hệ eager-load) vào memo. */
    public function register(?Media $media): void
    {
        if ($media) {
            $this->media[$media->uuid] = $media;
        }
    }

    /**
     * Nạp trước nhiều giá trị trong MỘT query (dùng cho list page để tránh N+1).
     *
     * @param iterable<mixed> $values
     */
    public function prime(iterable $values): void
    {
        $uuids = collect($values)
            ->filter(fn ($value) => is_string($value) && Str::isUuid($value))
            ->unique()
            ->reject(fn (string $uuid) => array_key_exists($uuid, $this->media))
            ->values();

        if ($uuids->isEmpty()) {
            return;
        }

        Media::query()
            ->whereIn('uuid', $uuids->all())
            ->get()
            ->each(fn (Media $media) => $this->media[$media->uuid] = $media);

        foreach ($uuids as $uuid) {
            if (! array_key_exists($uuid, $this->media)) {
                $this->media[$uuid] = null;
            }
        }
    }

    public function url(?string $value, string $variant = 'full'): ?string
    {
        if (! in_array($variant, self::VARIANTS, true)) {
            throw new InvalidArgumentException("Biến thể ảnh không hợp lệ: {$variant}");
        }

        if (blank($value)) {
            return null;
        }

        if (Str::isUuid($value)) {
            if (! array_key_exists($value, $this->media)) {
                $this->prime([$value]);
            }

            $media = $this->media[$value];

            return $media ? ($variant === 'thumb' ? $media->getThumbUrl() : $media->getUrl()) : null;
        }

        if (Str::startsWith($value, ['http://', 'https://'])) {
            return $value;
        }

        return Storage::disk(config('media.legacy_disk', 'public'))->url(ltrim($value, '/'));
    }
}
