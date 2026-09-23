<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
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
        Route::post('/locale/switch', [\App\Http\Controllers\Admin\LocaleController::class, 'switch'])->name('locale.switch');
        
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
        
        Route::post('/media-folders', [\App\Http\Controllers\Admin\MediaFolderController::class, 'store'])->name('media-folders.store');
        Route::delete('/media-folders/{uuid}', [\App\Http\Controllers\Admin\MediaFolderController::class, 'destroy'])->name('media-folders.destroy');
        
        Route::get('/media', [MediaController::class, 'index'])->name('media.index');
        Route::post('/media', [MediaController::class, 'store'])->name('media.store');
        Route::delete('/media/{uuid}', [MediaController::class, 'destroy'])->name('media.destroy');
        Route::post('/media/bulk', [MediaController::class, 'bulkDestroy'])->name('media.bulk');
        
        // Quản lý Ngôn ngữ (Languages)
        Route::resource('languages', \App\Http\Controllers\Admin\LanguageController::class)->except(['show']);

        // Quản trị Nội dung (CMS)
        Route::post('post-categories/bulk', [\App\Http\Controllers\Admin\PostCategoryController::class, 'bulk'])->name('post-categories.bulk');
        Route::resource('post-categories', \App\Http\Controllers\Admin\PostCategoryController::class)->except(['show'])->parameters([
            'post-categories' => 'uuid'
        ]);
        Route::resource('tags', \App\Http\Controllers\Admin\TagController::class)->except(['show'])->parameters(['tags' => 'uuid']);
        Route::post('posts/bulk', [\App\Http\Controllers\Admin\PostController::class, 'bulk'])->name('posts.bulk');
        Route::resource('posts', \App\Http\Controllers\Admin\PostController::class)->except(['show'])->parameters(['posts' => 'uuid']);
        Route::post('pages/bulk', [\App\Http\Controllers\Admin\PageController::class, 'bulk'])->name('pages.bulk');
        Route::resource('pages', \App\Http\Controllers\Admin\PageController::class)->except(['show'])->parameters(['pages' => 'uuid']);
        Route::resource('banners', \App\Http\Controllers\Admin\BannerController::class)->except(['show'])->parameters(['banners' => 'uuid']);
        Route::resource('menus', \App\Http\Controllers\Admin\MenuController::class)->except(['show'])->parameters(['menus' => 'uuid']);
        
        // Quản lý Sản phẩm (Catalog)
        Route::post('categories/bulk', [\App\Http\Controllers\Admin\CategoryController::class, 'bulk'])->name('categories.bulk');
        Route::resource('categories', \App\Http\Controllers\Admin\CategoryController::class)->except(['show'])->parameters(['categories' => 'uuid']);
        Route::resource('brands', \App\Http\Controllers\Admin\BrandController::class)->except(['show'])->parameters(['brands' => 'uuid']);
        Route::post('products/bulk', [\App\Http\Controllers\Admin\ProductController::class, 'bulk'])->name('products.bulk');
        Route::post('products/{uuid}/attributes', [\App\Http\Controllers\Admin\ProductController::class, 'storeAttribute'])->name('products.attributes.store');
        Route::resource('products', \App\Http\Controllers\Admin\ProductController::class)->except(['show'])->parameters(['products' => 'uuid']);
        Route::resource('product-attributes', \App\Http\Controllers\Admin\ProductAttributeController::class)->except(['show'])->parameters(['product-attributes' => 'uuid']);
        Route::resource('product-reviews', \App\Http\Controllers\Admin\ProductReviewController::class)->except(['show'])->parameters(['product-reviews' => 'uuid']);

        // Giao dịch & Đơn hàng
        Route::resource('carts', \App\Http\Controllers\Admin\CartController::class)->except(['show'])->parameters(['carts' => 'uuid']);
        Route::resource('orders', \App\Http\Controllers\Admin\OrderController::class)->except(['show'])->parameters(['orders' => 'uuid']);
        Route::resource('payment-methods', \App\Http\Controllers\Admin\PaymentMethodController::class)->except(['show'])->parameters(['payment-methods' => 'uuid']);
        Route::resource('payments', \App\Http\Controllers\Admin\PaymentController::class)->except(['show'])->parameters(['payments' => 'uuid']);
        Route::resource('coupons', \App\Http\Controllers\Admin\CouponController::class)->except(['show'])->parameters(['coupons' => 'uuid']);
        Route::resource('flash-sales', \App\Http\Controllers\Admin\FlashSaleController::class)->except(['show'])->parameters(['flash-sales' => 'uuid']);

        // Kho hàng
        Route::resource('warehouses', \App\Http\Controllers\Admin\WarehouseController::class)->except(['show'])->parameters(['warehouses' => 'uuid']);
        Route::resource('inventories', \App\Http\Controllers\Admin\InventoryController::class)->except(['show'])->parameters(['inventories' => 'uuid']);
        
        // Cấu hình Hệ thống (Settings)
        Route::get('/settings/{group?}', [\App\Http\Controllers\Admin\SettingController::class, 'edit'])->name('settings.edit');
        Route::post('/settings/{group}', [\App\Http\Controllers\Admin\SettingController::class, 'update'])->name('settings.update');
    });
});
