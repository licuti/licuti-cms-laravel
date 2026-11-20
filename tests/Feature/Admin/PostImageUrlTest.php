<?php

namespace Tests\Feature\Admin;

use App\Models\Media;
use App\Models\Post;
use App\Services\Shared\Media\MediaUrlResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
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

    public function test_invalid_variant_is_rejected(): void
    {
        $post = Post::factory()->create(['image' => 'uploads/photo.jpg']);

        $this->expectException(\InvalidArgumentException::class);

        $post->imageUrl('large');
    }

    public function test_prime_loads_all_media_in_one_query(): void
    {
        $posts = collect([
            Post::factory()->create(['image' => $this->makeMedia('media/a.jpg')->uuid]),
            Post::factory()->create(['image' => $this->makeMedia('media/b.jpg')->uuid]),
            Post::factory()->create(['image' => $this->makeMedia('media/c.jpg')->uuid]),
        ]);

        app()->forgetScopedInstances();
        $resolver = app(MediaUrlResolver::class);

        // Gom 1 query cho cả 3 ảnh
        $queries = 0;
        DB::listen(function () use (&$queries) {
            $queries++;
        });
        $resolver->prime($posts->pluck('image'));
        $queriesAfterPrime = $queries;
        $queries = 0;

        // Sau prime, accessor không phát sinh query nào
        foreach ($posts as $post) {
            $this->assertStringEndsWith('.jpg', (string) $post->image_url);
        }

        $this->assertSame(1, $queriesAfterPrime, 'prime() phải gom về đúng 1 query');
        $this->assertSame(0, $queries, 'Sau prime, accessor không được query thêm');
    }

    private function makeMedia(string $path): Media
    {
        return Media::create([
            'disk'          => 'public',
            'file_path'     => $path,
            'thumb_path'    => null,
            'file_name'     => basename($path),
            'original_name' => basename($path),
            'mime_type'     => 'image/jpeg',
            'size'          => 1000,
        ]);
    }
}
