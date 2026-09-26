<?php

namespace Tests\Feature\Admin;

use App\Core\BulkAction\BulkActionRegistry;
use App\Models\Page;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Feature test cho việc enforce quyền trên bulk actions (Pha 6).
 *
 * Permission được resolve qua BulkActionRegistry — đăng ký action kèm permission
 * thì BulkActionRequest::authorize() mới check được. Các quyền `.delete` đã seed
 * từ trước nhưng trước Pha 6 là dead code.
 */
class BulkActionAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Tạo user thuộc role `editor` với đúng các permission truyền vào
     * (luôn kèm admin.access để qua được AdminMiddleware).
     */
    private function makeEditor(array $permissions): User
    {
        $names = array_merge(['admin.access'], $permissions);

        foreach ($names as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        $role = Role::firstOrCreate(['name' => 'editor', 'guard_name' => 'web']);
        $role->syncPermissions($names);

        $user = User::factory()->create(['is_admin' => false]);
        $user->assignRole($role);

        return $user;
    }

    public function test_editor_without_pages_delete_is_forbidden_from_bulk_delete(): void
    {
        $editor = $this->makeEditor(['pages.update']);
        $page = Page::factory()->create();

        $this->actingAs($editor)
            ->post(route('admin.pages.bulk'), [
                'bulk_module' => 'pages',
                'action' => 'delete',
                'ids' => [(string) $page->id],
            ])
            ->assertForbidden();

        $this->assertDatabaseHas('pages', ['id' => $page->id]);
    }

    public function test_editor_with_pages_update_can_bulk_change_status(): void
    {
        $editor = $this->makeEditor(['pages.update']);
        $page = Page::factory()->draft()->create();

        $this->actingAs($editor)
            ->post(route('admin.pages.bulk'), [
                'bulk_module' => 'pages',
                'action' => 'status_published',
                'ids' => [(string) $page->id],
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('pages', [
            'id' => $page->id,
            'status' => 'published',
        ]);
    }

    public function test_editor_without_products_delete_is_forbidden_from_bulk_delete(): void
    {
        $editor = $this->makeEditor(['products.update']);
        $product = Product::create([
            'uuid' => Str::uuid()->toString(),
            'sku' => 'BULK-TEST-01',
            'price' => 1000000,
            'stock_quantity' => 5,
            'status' => 'published',
        ]);

        $this->actingAs($editor)
            ->post(route('admin.products.bulk'), [
                'bulk_module' => 'products',
                'action' => 'delete',
                'ids' => [(string) $product->id],
            ])
            ->assertForbidden();

        $this->assertDatabaseHas('products', ['id' => $product->id]);
    }

    public function test_is_admin_flag_can_bulk_delete_products_via_gate_before(): void
    {
        $admin = User::factory()->admin()->create();
        $product = Product::create([
            'uuid' => Str::uuid()->toString(),
            'sku' => 'BULK-TEST-02',
            'price' => 1000000,
            'stock_quantity' => 5,
            'status' => 'published',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.products.bulk'), [
                'bulk_module' => 'products',
                'action' => 'delete',
                'ids' => [(string) $product->id],
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSoftDeleted('products', ['id' => $product->id]);
    }

    public function test_editor_with_products_update_can_bulk_change_product_status(): void
    {
        $editor = $this->makeEditor(['products.update']);
        $product = Product::create([
            'uuid' => Str::uuid()->toString(),
            'sku' => 'BULK-STATUS-01',
            'price' => 1000000,
            'stock_quantity' => 5,
            'status' => 'draft',
        ]);

        $this->actingAs($editor)
            ->post(route('admin.products.bulk'), [
                'bulk_module' => 'products',
                'action' => 'published',
                'ids' => [(string) $product->id],
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'status' => 'published',
        ]);
    }

    public function test_editor_without_products_update_is_forbidden_from_bulk_status_change(): void
    {
        $editor = $this->makeEditor(['products.delete']);
        $product = Product::create([
            'uuid' => Str::uuid()->toString(),
            'sku' => 'BULK-STATUS-02',
            'price' => 1000000,
            'stock_quantity' => 5,
            'status' => 'draft',
        ]);

        $this->actingAs($editor)
            ->post(route('admin.products.bulk'), [
                'bulk_module' => 'products',
                'action' => 'published',
                'ids' => [(string) $product->id],
            ])
            ->assertForbidden();

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'status' => 'draft',
        ]);
    }

    public function test_get_action_options_hides_actions_the_user_cannot_perform(): void
    {
        $editor = $this->makeEditor(['pages.update']);

        $this->actingAs($editor);

        $options = app(BulkActionRegistry::class)->getActionOptions('pages');

        $this->assertArrayNotHasKey('delete', $options);
        $this->assertArrayHasKey('status_published', $options);
    }
}
