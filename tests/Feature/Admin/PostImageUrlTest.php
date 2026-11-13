<?php

namespace Tests\Feature\Admin;

use App\Models\Media;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostImageUrlTest extends TestCase
{
    use RefreshDatabase;

    public function test_media_uuid_exposes_full_and_thumbnail_urls(): void
    {
        $media = Media::create([
            'disk' => 'public',
            'file_path' => 'media/photo.jpg',
            'thumb_path' => 'media/photo-thumb.jpg',
            'file_name' => 'photo.jpg',
            'original_name' => 'photo.jpg',
            'mime_type' => 'image/jpeg',
            'size' => 1000,
        ]);

        $post = Post::factory()->create(['image' => $media->uuid]);
        $post->unsetRelation('imageMedia');

        $this->assertStringEndsWith('/storage/media/photo.jpg', $post->image_url);
        $this->assertStringEndsWith('/storage/media/photo-thumb.jpg', $post->thumbnail_url);
    }

    public function test_storage_path_and_external_url_are_supported(): void
    {
        $post = Post::factory()->create(['image' => 'uploads/photo.jpg']);

        $this->assertStringEndsWith('/storage/uploads/photo.jpg', $post->image_url);

        $post->update(['image' => 'https://cdn.example.test/photo.png']);

        $this->assertSame('https://cdn.example.test/photo.png', $post->fresh()->image_url);
    }

    public function test_legacy_paths_use_the_configured_disk(): void
    {
        config([
            'filesystems.disks.legacy_media' => [
                'driver' => 'local',
                'root' => storage_path('app/legacy-media'),
                'url' => 'https://cdn.example.test/legacy',
            ],
            'media.legacy_disk' => 'legacy_media',
        ]);

        $post = Post::factory()->create(['image' => '/uploads/photo.jpg']);

        $this->assertSame('https://cdn.example.test/legacy/uploads/photo.jpg', $post->image_url);
    }

    public function test_missing_image_returns_null(): void
    {
        $post = Post::factory()->create(['image' => null]);

        $this->assertNull($post->image_url);
        $this->assertNull($post->thumbnail_url);
    }
}
