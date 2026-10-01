<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Admin\AdminBrandController;
use App\Http\Controllers\Admin\AdminCategoryController;
use App\Http\Controllers\Admin\AdminChequeController;
use App\Http\Controllers\Admin\AdminWholesaleController;
use App\Http\Controllers\Admin\AdminWholesalePackController;
use App\Http\Controllers\Admin\AdminCustomerController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminFinancialController;
use App\Http\Controllers\Admin\AdminInventoryController;
use App\Http\Controllers\Admin\AdminHeroController;
use App\Http\Controllers\Admin\AdminMediaController;
use App\Http\Controllers\Admin\AdminNilaController;
use App\Http\Controllers\Admin\AdminOrderController;
use App\Http\Controllers\Admin\AdminProductController;
use App\Http\Controllers\Admin\AdminProductMediaController;
use App\Http\Controllers\Admin\AdminProductVariantController;
use App\Http\Controllers\Admin\AdminProfileController;
use App\Http\Controllers\Admin\AdminSiteContentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\SeoController;
use App\Http\Controllers\StoreBrandController;
use App\Http\Controllers\StoreCategoryController;
use App\Http\Controllers\StoreMediaController;
use App\Http\Controllers\StorePageController;
use App\Http\Controllers\StoreProductController;
use App\Http\Controllers\WholesaleController;
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

Route::get('/search/suggestions', [StoreProductController::class, 'suggestions'])
    ->name('search.suggestions');

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

Route::get('/cart/summary', [CartController::class, 'summary'])
    ->name('cart.summary');

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
| Wholesale
|--------------------------------------------------------------------------
*/

Route::get('/wholesale', [WholesaleController::class, 'show'])
    ->name('wholesale.show');

Route::post('/wholesale/apply', [WholesaleController::class, 'apply'])
    ->middleware(['auth', 'customer'])
    ->name('wholesale.apply');

/*
|--------------------------------------------------------------------------
| Online payment callbacks
|--------------------------------------------------------------------------
*/

Route::get(
    '/payment/zarinpal/callback/{order}',
    [PaymentController::class, 'zarinpalCallback']
)
    ->name('payment.zarinpal.callback');

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])
        ->name('login');

    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:login')
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
        Route::get('products/{product}/media', [AdminProductMediaController::class, 'index'])
            ->name('products.media.index');

        Route::post('products/{product}/media', [AdminProductMediaController::class, 'store'])
            ->name('products.media.store');

        Route::patch('products/{product}/media/{media}', [AdminProductMediaController::class, 'update'])
            ->name('products.media.update');

        Route::post('products/{product}/media/reorder', [AdminProductMediaController::class, 'reorder'])
            ->name('products.media.reorder');

        Route::delete('products/{product}/media/{media}', [AdminProductMediaController::class, 'destroy'])
            ->name('products.media.destroy');


        Route::resource('products.variants', AdminProductVariantController::class)
            ->except(['show']);

        Route::resource('categories', AdminCategoryController::class)
            ->except(['show']);

        Route::resource('brands', AdminBrandController::class)
            ->except(['show']);

        // Reusable media actions for brand/category/variant admin surfaces.
        Route::post('media/{type}/{id}', [AdminMediaController::class, 'store'])
            ->name('media.store');

        Route::delete('media/{type}/{id}/{media}', [AdminMediaController::class, 'destroy'])
            ->name('media.destroy');

        /*
        |--------------------------------------------------------------------------
        | Customers
        |--------------------------------------------------------------------------
        */

        Route::get('customers', [AdminCustomerController::class, 'index'])
            ->name('customers.index');

        Route::get('wholesale', [AdminWholesaleController::class, 'index'])
            ->name('wholesale.index');

        Route::resource('wholesale-packs', AdminWholesalePackController::class)
            ->except(['show']);

        Route::patch(
            'customers/{customer}/wholesale/approve',
            [AdminWholesaleController::class, 'approve']
        )->name('customers.wholesale.approve');

        Route::patch(
            'customers/{customer}/wholesale/reject',
            [AdminWholesaleController::class, 'reject']
        )->name('customers.wholesale.reject');

        Route::patch(
            'customers/{customer}/wholesale/terms',
            [AdminWholesaleController::class, 'updateTerms']
        )->name('customers.wholesale.terms');

        Route::patch(
            'customers/{customer}/wholesale/suspend',
            [AdminWholesaleController::class, 'suspend']
        )->name('customers.wholesale.suspend');

        Route::patch(
            'customers/{customer}/cheque/enable',
            [AdminWholesaleController::class, 'enableCheque']
        )->name('customers.cheque.enable');

        Route::patch(
            'customers/{customer}/cheque/disable',
            [AdminWholesaleController::class, 'disableCheque']
        )->name('customers.cheque.disable');

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

        Route::get('cheques', [AdminChequeController::class, 'index'])
            ->name('cheques.index');

        Route::patch(
            'cheques/{chequePayment}/review',
            [AdminChequeController::class, 'review']
        )->name('cheques.review');

        Route::patch(
            'cheques/{chequePayment}/accept',
            [AdminChequeController::class, 'accept']
        )->name('cheques.accept');

        Route::patch(
            'cheques/{chequePayment}/reject',
            [AdminChequeController::class, 'reject']
        )->name('cheques.reject');

        Route::patch(
            'cheques/{chequePayment}/deposit',
            [AdminChequeController::class, 'deposit']
        )->name('cheques.deposit');

        Route::patch(
            'cheques/{chequePayment}/clear',
            [AdminChequeController::class, 'clear']
        )->name('cheques.clear');

        Route::patch(
            'cheques/{chequePayment}/bounce',
            [AdminChequeController::class, 'bounce']
        )->name('cheques.bounce');

        /*
        |--------------------------------------------------------------------------
        | Inventory
        |--------------------------------------------------------------------------
        */

        Route::get('inventory', [AdminInventoryController::class, 'index'])
            ->name('inventory.index');

        Route::get('nila', [AdminNilaController::class, 'index'])
            ->name('nila.index');

        Route::get('hero', [AdminHeroController::class, 'index'])
            ->name('hero.index');

        Route::put('hero', [AdminHeroController::class, 'update'])
            ->name('hero.update');

        Route::post('inventory', [AdminInventoryController::class, 'store'])
            ->name('inventory.store');

        /*
        |--------------------------------------------------------------------------
        | Store content / support
        |--------------------------------------------------------------------------
        */

        Route::get('content/about', [AdminSiteContentController::class, 'about'])
            ->name('content.about');

        Route::post('content/about', [AdminSiteContentController::class, 'updateAbout'])
            ->name('content.about.update');

        Route::get('contact', [AdminSiteContentController::class, 'contact'])
            ->name('contact.index');

        Route::post('contact/settings', [AdminSiteContentController::class, 'updateContact'])
            ->name('content.contact.update');

        Route::get('contact/{message}', [AdminSiteContentController::class, 'showContact'])
            ->name('contact.show');

        Route::patch('contact/{message}/status', [AdminSiteContentController::class, 'updateContactStatus'])
            ->name('contact.status');

        /*
        |--------------------------------------------------------------------------
        | Admin profile
        |--------------------------------------------------------------------------
        */

        Route::get('profile', [AdminProfileController::class, 'edit'])
            ->name('profile.edit');

        Route::patch('profile', [AdminProfileController::class, 'update'])
            ->name('profile.update');

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
