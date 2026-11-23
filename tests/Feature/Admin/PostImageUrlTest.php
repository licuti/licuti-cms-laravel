<?php

namespace Tests\Feature\Admin;

use App\Models\Media;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostImageUrlTest extends TestCase
{
    use RefreshDatabase;

    public function test_media_relation_exposes_full_and_thumbnail_urls(): void
    {
        $media = $this->makeMedia('media/photo.jpg', 'media/photo-thumb.jpg');
        $post = Post::factory()->create(['image' => $media->id]);

        $post->load('imageMedia');

        $this->assertSame($media->id, $post->imageMedia->id);
        $this->assertStringEndsWith('/storage/media/photo.jpg', $post->image_url);
        $this->assertStringEndsWith('/storage/media/photo-thumb.jpg', $post->thumbnail_url);
    }

    public function test_missing_image_returns_null(): void
    {
        $post = Post::factory()->create(['image' => null]);

        $this->assertNull($post->image_url);
        $this->assertNull($post->thumbnail_url);
    }

    public function test_post_cannot_delete_media_that_is_still_referenced(): void
    {
        $media = $this->makeMedia('media/photo.jpg');
        Post::factory()->create(['image' => $media->id]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        $media->delete();
    }

    private function makeMedia(string $path, ?string $thumbPath = null): Media
    {
        return Media::create([
            'disk'          => 'public',
            'file_path'     => $path,
            'thumb_path'    => $thumbPath,
            'file_name'     => basename($path),
            'original_name' => basename($path),
            'mime_type'     => 'image/jpeg',
            'size'          => 1000,
        ]);
    }
}
