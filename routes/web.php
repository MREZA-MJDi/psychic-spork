<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Admin\AdminBrandController;
use App\Http\Controllers\Admin\AdminCategoryController;
use App\Http\Controllers\Admin\AdminCustomerController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminFinancialController;
use App\Http\Controllers\Admin\AdminInventoryController;
use App\Http\Controllers\Admin\AdminOrderController;
use App\Http\Controllers\Admin\AdminProductController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\SeoController;
use App\Http\Controllers\StoreBrandController;
use App\Http\Controllers\StoreCategoryController;
use App\Http\Controllers\StoreMediaController;
use App\Http\Controllers\StorePageController;
use App\Http\Controllers\StoreProductController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Store
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])
    ->name('home');

Route::get('/media/{path}', [StoreMediaController::class, 'show'])
    ->where('path', '.*')
    ->name('store.media');

Route::get('/robots.txt', [SeoController::class, 'robots'])
    ->name('seo.robots');

Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])
    ->name('seo.sitemap');

/*
|--------------------------------------------------------------------------
| Products
|--------------------------------------------------------------------------
*/

Route::get('/products', [StoreProductController::class, 'index'])
    ->name('products.index');

Route::get('/products/{product:slug}', [StoreProductController::class, 'show'])
    ->name('products.show');

/*
|--------------------------------------------------------------------------
| Categories
|--------------------------------------------------------------------------
*/

Route::get('/categories', [StoreCategoryController::class, 'index'])
    ->name('categories.index');

Route::get('/categories/{category:slug}', [StoreCategoryController::class, 'show'])
    ->name('categories.show');

/*
|--------------------------------------------------------------------------
| Brands
|--------------------------------------------------------------------------
*/

Route::get('/brands', [StoreBrandController::class, 'index'])
    ->name('brands.index');

Route::get('/brands/{brand:slug}', [StoreBrandController::class, 'show'])
    ->name('brands.show');

/*
|--------------------------------------------------------------------------
| Static Pages
|--------------------------------------------------------------------------
*/

Route::get('/about', [StorePageController::class, 'about'])
    ->name('about');

Route::get('/contact', [StorePageController::class, 'contact'])
    ->name('contact');

Route::post('/contact', [StorePageController::class, 'submitContact'])
    ->name('contact.submit');

Route::get('/shipping', [StorePageController::class, 'shipping'])
    ->name('shipping');

Route::get('/returns', [StorePageController::class, 'returns'])
    ->name('returns');

Route::get('/faq', [StorePageController::class, 'faq'])
    ->name('faq');

/*
|--------------------------------------------------------------------------
| Cart
|--------------------------------------------------------------------------
*/

Route::get('/cart', [CartController::class, 'index'])
    ->name('cart');

Route::post('/cart/{variant}', [CartController::class, 'store'])
    ->name('cart.store');

Route::put('/cart/{item}', [CartController::class, 'update'])
    ->name('cart.update');

Route::delete('/cart/{item}', [CartController::class, 'remove'])
    ->name('cart.remove');

/*
|--------------------------------------------------------------------------
| Checkout
|--------------------------------------------------------------------------
*/

Route::get('/checkout', [CheckoutController::class, 'create'])
    ->name('checkout');

Route::post('/checkout', [CheckoutController::class, 'store'])
    ->name('checkout.store');

Route::get('/checkout/success', [CheckoutController::class, 'success'])
    ->name('checkout.success');

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])
        ->name('login');

    Route::post('/login', [AuthController::class, 'login'])
        ->name('login.store');

    Route::get('/register', [AuthController::class, 'showRegister'])
        ->name('register');

    Route::post('/register', [AuthController::class, 'register'])
        ->name('register.store');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

/*
|--------------------------------------------------------------------------
| Customer Account
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'customer'])->group(function () {
    Route::get('/account', [AccountController::class, 'index'])
        ->name('account');
});

/*
|--------------------------------------------------------------------------
| Admin
|--------------------------------------------------------------------------
*/

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'admin'])
    ->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])
            ->name('dashboard');

        /*
        |--------------------------------------------------------------------------
        | Catalog
        |--------------------------------------------------------------------------
        */

        Route::resource('products', AdminProductController::class)
            ->except(['show']);

        Route::resource('categories', AdminCategoryController::class)
            ->except(['show']);

        Route::resource('brands', AdminBrandController::class)
            ->except(['show']);

        /*
        |--------------------------------------------------------------------------
        | Customers
        |--------------------------------------------------------------------------
        */

        Route::get('customers', [AdminCustomerController::class, 'index'])
            ->name('customers.index');

        /*
        |--------------------------------------------------------------------------
        | Orders
        |--------------------------------------------------------------------------
        */

        Route::get('orders', [AdminOrderController::class, 'index'])
            ->name('orders.index');

        Route::get('orders/{order}', [AdminOrderController::class, 'show'])
            ->name('orders.show');

        Route::put('orders/{order}', [AdminOrderController::class, 'update'])
            ->name('orders.update');

        /*
        |--------------------------------------------------------------------------
        | Inventory
        |--------------------------------------------------------------------------
        */

        Route::get('inventory', [AdminInventoryController::class, 'index'])
            ->name('inventory.index');

        Route::post('inventory', [AdminInventoryController::class, 'store'])
            ->name('inventory.store');

        /*
        |--------------------------------------------------------------------------
        | Accounting
        |--------------------------------------------------------------------------
        */

        Route::get('accounting', [AdminFinancialController::class, 'index'])
            ->name('accounting.index');

        Route::post('accounting', [AdminFinancialController::class, 'store'])
            ->name('accounting.store');

        Route::get('accounting/{transaction}', [AdminFinancialController::class, 'show'])
            ->name('accounting.show');
    });
