<?php

namespace Tests\Feature\Admin;

use App\Models\Language;
use App\Models\Media;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaPickerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['is_admin' => true]);

        Language::create([
            'code'        => 'vi',
            'name'        => 'Tiếng Việt',
            'native_name' => 'Tiếng Việt',
            'is_default'  => true,
            'is_active'   => true,
        ]);
    }

    private function createMedia(string $fileName, string $mimeType, ?string $createdAt = null): Media
    {
        Storage::fake('public');

        $media = Media::create([
            'uuid'         => \Illuminate\Support\Str::uuid(),
            'disk'         => 'public',
            'file_path'    => 'media/original/' . $fileName,
            'thumb_path'   => 'media/thumb/' . pathinfo($fileName, PATHINFO_FILENAME) . '.webp',
            'file_name'    => $fileName,
            'original_name'=> $fileName,
            'mime_type'    => $mimeType,
            'size'         => 1024,
        ]);

        // created_at không nằm trong $fillable nên phải gán trực tiếp
        if ($createdAt) {
            $media->created_at = $createdAt;
            $media->save();
        }

        return $media;
    }

    public function test_media_index_sorts_newest_and_oldest(): void
    {
        $old = $this->createMedia('oldest.jpg', 'image/jpeg', now()->subDays(10));
        $mid = $this->createMedia('middle.jpg', 'image/jpeg', now()->subDays(5));
        $new = $this->createMedia('newest.jpg', 'image/jpeg', now());

        // Sắp xếp mới nhất (desc)
        $desc = $this->actingAs($this->admin)
            ->getJson('/admin/media?type=image&sort=desc');
        $desc->assertStatus(200);
        $descIds = collect($desc->json('media.data'))->pluck('id')->all();
        $this->assertSame($new->id, $descIds[0], 'Mới nhất phải nằm đầu tiên khi sort=desc');
        $this->assertSame($old->id, end($descIds), 'Cũ nhất phải nằm cuối khi sort=desc');

        // Sắp xếp cũ nhất (asc)
        $asc = $this->actingAs($this->admin)
            ->getJson('/admin/media?type=image&sort=asc');
        $asc->assertStatus(200);
        $ascIds = collect($asc->json('media.data'))->pluck('id')->all();
        $this->assertSame($old->id, $ascIds[0], 'Cũ nhất phải nằm đầu tiên khi sort=asc');
        $this->assertSame($new->id, end($ascIds), 'Mới nhất phải nằm cuối khi sort=asc');
    }

    public function test_media_index_returns_thumb_and_url_keys_for_picker(): void
    {
        $this->createMedia('photo.jpg', 'image/jpeg');

        $response = $this->actingAs($this->admin)
            ->getJson('/admin/media?type=image&sort=desc');
        $response->assertStatus(200);

        $item = $response->json('media.data.0');
        $this->assertNotEmpty($item['url']);
        $this->assertNotEmpty($item['conversions']['thumb']);
        $this->assertSame($item['uuid'], $item['uuid']);
    }

    public function test_product_edit_page_renders_valid_picker_selector(): void
    {
        $media = $this->createMedia('gallery-1.jpg', 'image/jpeg');

        $product = Product::create([
            'sku'            => 'PICKER-01',
            'price'          => 1000000,
            'status'         => 'published',
            'primary_image'  => $media->uuid,
        ]);
        $product->translations()->create([
            'locale' => 'vi',
            'name'   => 'Sản phẩm test picker',
            'slug'   => 'san-pham-test-picker',
        ]);
        $product->images()->create(['image' => $media->uuid]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.products.edit', $product));
        $response->assertStatus(200);

        // Regression guard: selector không hợp lệ gây SyntaxError khi click đổi ảnh
        $response->assertDontSee("data-id=' + String(JSON.stringify(item.id))", false);
        // Selector mới đã được quote đúng
        $response->assertSee('data-id="\'+String(item.id)+\'"]', false);
        // Multi mode phải xoá currentId thừa để không tự chọn ảnh cũ
        $response->assertSee('delete modal.dataset.currentId', false);
        // Gallery: item draggable + ảnh preview không draggable (kéo thả sort mượt)
        $this->assertStringContainsString('draggable="true"', $response->getContent());
        $this->assertStringContainsString('draggable="false"', $response->getContent());
        // Hiệu ứng trượt FLIP khi sort
        $response->assertSee('firstPos', false);
    }
}
