<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CartController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FlashSaleController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\LanguageController;
use App\Http\Controllers\Admin\LocaleController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\MediaFolderController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\PaymentMethodController;
use App\Http\Controllers\Admin\PostCategoryController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\ProductAttributeController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductReviewController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\TagController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\WarehouseController;
use App\Http\Middleware\AdminMiddleware;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin/login');
});

// ─── Admin Web Routes ────────────────────────────────────────────────────────
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/', function () {
        return redirect()->route('admin.dashboard');
    });

    // Xác thực (Không bảo mật)
    Route::get('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/login', [AuthController::class, 'authenticate'])->name('login.post');

    // Các trang Quản trị (Bảo mật qua Session Auth & Quyền Admin)
    Route::middleware(['auth', AdminMiddleware::class])->group(function () {

        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

        // Locale Switch
        Route::post('/locale/switch', [LocaleController::class, 'switch'])->name('locale.switch');

        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Quản lý Người dùng (Users)
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users/bulk-action', [UserController::class, 'bulkAction'])->name('users.bulk');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{uuid}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{uuid}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{uuid}', [UserController::class, 'destroy'])->name('users.destroy');
        Route::post('/users/{uuid}/restore', [UserController::class, 'restore'])->name('users.restore');
        Route::delete('/users/{uuid}/force-delete', [UserController::class, 'forceDelete'])->name('users.force-delete');
        Route::post('/users/{uuid}/roles', [UserController::class, 'assignRoles'])->name('users.roles');

        // Vai trò & Quyền (Roles & Permissions)
        Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
        Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
        Route::put('/roles/{role}', [RoleController::class, 'update'])->name('roles.update');
        Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');
        Route::get('/roles/{role}/permissions', [RoleController::class, 'editPermissions'])->name('roles.permissions');
        Route::post('/roles/{role}/permissions', [RoleController::class, 'updatePermissions'])->name('roles.permissions.update');

        Route::post('/media-folders', [MediaFolderController::class, 'store'])->name('media-folders.store');
        Route::delete('/media-folders/{uuid}', [MediaFolderController::class, 'destroy'])->name('media-folders.destroy');

        Route::get('/media', [MediaController::class, 'index'])->name('media.index');
        Route::post('/media', [MediaController::class, 'store'])->name('media.store');
        Route::delete('/media/{uuid}', [MediaController::class, 'destroy'])->name('media.destroy');
        Route::post('/media/bulk', [MediaController::class, 'bulkDestroy'])->name('media.bulk');

        // Quản lý Ngôn ngữ (Languages)
        Route::resource('languages', LanguageController::class)->except(['show']);

        // Quản trị Nội dung (CMS)
        Route::post('post-categories/bulk', [PostCategoryController::class, 'bulk'])->name('post-categories.bulk');
        Route::resource('post-categories', PostCategoryController::class)->except(['show'])->parameters([
            'post-categories' => 'uuid',
        ]);
        Route::resource('tags', TagController::class)->except(['show'])->parameters(['tags' => 'uuid']);
        Route::post('posts/bulk', [PostController::class, 'bulk'])->name('posts.bulk');
        Route::resource('posts', PostController::class)->except(['show'])->parameters(['posts' => 'uuid']);
        Route::post('pages/bulk', [PageController::class, 'bulk'])->name('pages.bulk');
        Route::resource('pages', PageController::class)->except(['show'])->parameters(['pages' => 'uuid']);
        Route::resource('banners', BannerController::class)->except(['show'])->parameters(['banners' => 'uuid']);
        Route::resource('menus', MenuController::class)->except(['show'])->parameters(['menus' => 'uuid']);

        // Quản lý Sản phẩm (Catalog)
        Route::post('categories/bulk', [CategoryController::class, 'bulk'])->name('categories.bulk');
        Route::resource('categories', CategoryController::class)->except(['show'])->parameters(['categories' => 'uuid']);
        Route::resource('brands', BrandController::class)->except(['show'])->parameters(['brands' => 'uuid']);
        Route::post('products/bulk', [ProductController::class, 'bulk'])->name('products.bulk');
        Route::post('products/{uuid}/attributes', [ProductController::class, 'storeAttribute'])->name('products.attributes.store');
        // Read routes (index/create/edit) yêu cầu products.view — write route vẫn do FormRequest đảm nhiệm
        Route::get('products', [ProductController::class, 'index'])->middleware('can:products.view')->name('products.index');
        Route::get('products/create', [ProductController::class, 'create'])->middleware('can:products.view')->name('products.create');
        Route::get('products/{uuid}/edit', [ProductController::class, 'edit'])->middleware('can:products.view')->name('products.edit');
        Route::resource('products', ProductController::class)->only(['store', 'update', 'destroy'])->parameters(['products' => 'uuid']);
        Route::resource('product-attributes', ProductAttributeController::class)->except(['show'])->parameters(['product-attributes' => 'uuid']);
        Route::resource('product-reviews', ProductReviewController::class)->except(['show'])->parameters(['product-reviews' => 'uuid']);

        // Giao dịch & Đơn hàng
        Route::resource('carts', CartController::class)->except(['show'])->parameters(['carts' => 'uuid']);
        Route::resource('orders', OrderController::class)->except(['show'])->parameters(['orders' => 'uuid']);
        Route::resource('payment-methods', PaymentMethodController::class)->except(['show'])->parameters(['payment-methods' => 'uuid']);
        Route::resource('payments', PaymentController::class)->except(['show'])->parameters(['payments' => 'uuid']);
        Route::resource('coupons', CouponController::class)->except(['show'])->parameters(['coupons' => 'uuid']);
        Route::resource('flash-sales', FlashSaleController::class)->except(['show'])->parameters(['flash-sales' => 'uuid']);

        // Kho hàng
        Route::resource('warehouses', WarehouseController::class)->except(['show'])->parameters(['warehouses' => 'uuid']);
        Route::resource('inventories', InventoryController::class)->except(['show'])->parameters(['inventories' => 'uuid']);

        // Cấu hình Hệ thống (Settings)
        Route::get('/settings/{group?}', [SettingController::class, 'edit'])->name('settings.edit');
        Route::post('/settings/{group}', [SettingController::class, 'update'])->name('settings.update');
    });
});
