<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Media;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class StoreFrontendTest extends TestCase
{
    use RefreshDatabase;

    public function test_local_admin_seeder_creates_a_working_admin_account(): void
    {
        Config::set('app.admin.name', 'Janan Admin');
        Config::set('app.admin.email', 'admin@janan.local');
        Config::set('app.admin.password', 'password');

        $this->seed(AdminUserSeeder::class);

        $this->post(route('login.store'), [
            'identifier' => 'admin@janan.local',
            'password' => 'password',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs(
            User::query()->where('email', 'admin@janan.local')->firstOrFail()
        );
    }

    public function test_login_page_renders_cleanly(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('خوش برگشتی.')
            ->assertSee('ایمیل یا نام کاربری')
            ->assertSee('رمز عبور')
            ->assertSee('ورود به جانان');
    }

    public function test_homepage_contains_the_product_carousel_and_rotation_data(): void
    {
        $category = Category::create([
            'name' => 'کالکشن تست',
            'slug' => 'test-collection-' . uniqid(),
            'is_active' => true,
            'sort_order' => 1,
        ]);

        for ($index = 1; $index <= 12; $index++) {
            $product = Product::create([
                'category_id' => $category->id,
                'name' => 'محصول تست ' . $index,
                'slug' => 'test-home-product-' . $index . '-' . uniqid(),
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => $index,
            ]);

            ProductVariant::create([
                'product_id' => $product->id,
                'sku' => 'HOME-TEST-' . $index . '-' . strtoupper(uniqid()),
                'price' => 100000 + ($index * 1000),
                'sale_price' => null,
                'stock' => 10,
                'low_stock_threshold' => 2,
                'is_active' => true,
                'sort_order' => 1,
            ]);
        }

        Model::preventLazyLoading(true);

        try {
            $this->get(route('home'))
                ->assertOk()
                ->assertSee('data-product-carousel', false)
                ->assertSee('data-product-prev', false)
                ->assertSee('data-product-next', false)
                ->assertSee('دیدن بیشتر محصولات')
                ->assertSee('محصول تست 1')
                ->assertSee('محصول تست 12');
        } finally {
            Model::preventLazyLoading(false);
        }
    }

    public function test_about_page_is_the_single_public_contact_destination(): void
    {
        $this->get(route('about'))
            ->assertOk()
            ->assertSee('درباره و تماس')
            ->assertSee('ارسال پیام')
            ->assertSee('id="contact"', false)
            ->assertSee(route('contact.submit'), false);

        $this->get(route('contact'))
            ->assertRedirect(route('about') . '#contact');
    }

    public function test_product_catalog_sort_and_page_size_are_preserved_in_pagination(): void
    {
        $category = Category::create([
            'name' => 'مرتب‌سازی تست',
            'slug' => 'sort-test-' . uniqid(),
            'is_active' => true,
            'sort_order' => 1,
        ]);

        foreach ([
            ['name' => 'محصول ارزان', 'price' => 50000],
            ['name' => 'محصول گران', 'price' => 250000],
        ] as $index => $item) {
            $product = Product::create([
                'category_id' => $category->id,
                'name' => $item['name'],
                'slug' => 'sort-product-' . $index . '-' . uniqid(),
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => $index + 1,
            ]);

            ProductVariant::create([
                'product_id' => $product->id,
                'sku' => 'SORT-' . $index . '-' . strtoupper(uniqid()),
                'price' => $item['price'],
                'sale_price' => null,
                'stock' => 10,
                'low_stock_threshold' => 2,
                'is_active' => true,
                'sort_order' => 1,
            ]);
        }

        $this->get(route('products.index', [
            'sort' => 'price_asc',
            'per_page' => 12,
        ]))
            ->assertOk()
            ->assertSeeInOrder([
                'محصول ارزان',
                'محصول گران',
            ])
            ->assertSee('name="sort"', false)
            ->assertSee('name="per_page"', false);
    }


    public function test_product_catalog_price_sort_uses_sale_price_when_present(): void
    {
        $category = Category::create([
            'name' => 'قیمت فروش تست',
            'slug' => 'sale-price-sort-' . uniqid(),
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $regularProduct = Product::create([
            'category_id' => $category->id,
            'name' => 'محصول قیمت عادی',
            'slug' => 'regular-price-' . uniqid(),
            'is_active' => true,
            'is_featured' => false,
            'sort_order' => 1,
        ]);

        ProductVariant::create([
            'product_id' => $regularProduct->id,
            'sku' => 'REGULAR-' . strtoupper(uniqid()),
            'price' => 100000,
            'sale_price' => null,
            'stock' => 10,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $saleProduct = Product::create([
            'category_id' => $category->id,
            'name' => 'محصول با تخفیف',
            'slug' => 'sale-price-' . uniqid(),
            'is_active' => true,
            'is_featured' => false,
            'sort_order' => 2,
        ]);

        ProductVariant::create([
            'product_id' => $saleProduct->id,
            'sku' => 'SALE-' . strtoupper(uniqid()),
            'price' => 250000,
            'sale_price' => 50000,
            'stock' => 10,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $this->get(route('products.index', ['sort' => 'price_asc']))
            ->assertOk()
            ->assertSeeInOrder([
                'محصول با تخفیف',
                'محصول قیمت عادی',
            ]);
    }

    public function test_product_catalog_stock_badge_only_renders_for_non_available_states(): void
    {
        $category = Category::create([
            'name' => 'وضعیت موجودی',
            'slug' => 'stock-state-' . uniqid(),
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $available = Product::create([
            'category_id' => $category->id,
            'name' => 'محصول موجود',
            'slug' => 'available-product-' . uniqid(),
            'is_active' => true,
            'is_featured' => false,
            'sort_order' => 1,
        ]);

        ProductVariant::create([
            'product_id' => $available->id,
            'sku' => 'AVAILABLE-' . strtoupper(uniqid()),
            'price' => 100000,
            'sale_price' => null,
            'stock' => 8,
            'low_stock_threshold' => 2,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $out = Product::create([
            'category_id' => $category->id,
            'name' => 'محصول ناموجود',
            'slug' => 'out-product-' . uniqid(),
            'is_active' => true,
            'is_featured' => false,
            'sort_order' => 2,
        ]);

        ProductVariant::create([
            'product_id' => $out->id,
            'sku' => 'OUT-' . strtoupper(uniqid()),
            'price' => 120000,
            'sale_price' => null,
            'stock' => 0,
            'low_stock_threshold' => 2,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $html = $this->get(route('products.index'))
            ->assertOk()
            ->getContent();

        $this->assertSame(
            1,
            substr_count($html, 'class="product-card__stock is-out"')
        );
        $this->assertStringContainsString('محصول موجود', $html);
        $this->assertStringContainsString('محصول ناموجود', $html);
    }

    public function test_product_detail_contains_purchase_panel_and_variant_data(): void
    {
        $category = Category::create([
            'name' => 'جزئیات محصول',
            'slug' => 'detail-test-' . uniqid(),
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'محصول جزئیات تست',
            'slug' => 'detail-product-' . uniqid(),
            'short_description' => 'توضیح کوتاه تست',
            'description' => 'توضیح کامل تست',
            'is_active' => true,
            'is_featured' => false,
            'sort_order' => 1,
        ]);

        ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'DETAIL-' . strtoupper(uniqid()),
            'price' => 150000,
            'sale_price' => 125000,
            'stock' => 5,
            'low_stock_threshold' => 2,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $this->get(route('products.show', $product))
            ->assertOk()
            ->assertSee('product-detail-v2__layout', false)
            ->assertSee('product-detail-v2__purchase', false)
            ->assertSee('محصول جزئیات تست')
            ->assertSee('125,000', false)
            ->assertSee('DETAIL-', false)
            ->assertSee('افزودن به سبد خرید');
    }


    public function test_product_detail_renders_variant_media_as_visual_options(): void
    {
        $category = Category::create([
            'name' => 'تصویر واریانت',
            'slug' => 'variant-media-' . uniqid(),
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'محصول با تصویر واریانت',
            'slug' => 'variant-media-product-' . uniqid(),
            'is_active' => true,
            'is_featured' => false,
            'sort_order' => 1,
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'VAR-MEDIA-' . strtoupper(uniqid()),
            'price' => 180000,
            'sale_price' => null,
            'stock' => 4,
            'low_stock_threshold' => 1,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        Media::create([
            'mediable_type' => $variant->getMorphClass(),
            'mediable_id' => $variant->id,
            'collection' => 'gallery',
            'disk' => 'public',
            'path' => 'variants/test-variant.webp',
            'original_name' => 'test-variant.webp',
            'mime_type' => 'image/webp',
            'sort_order' => 0,
        ]);

        $this->get(route('products.show', $product))
            ->assertOk()
            ->assertSee('product-variant-visual', false)
            ->assertSee(
                'data-variant-image="' . route('store.media', ['path' => 'variants/test-variant.webp']) . '"',
                false
            )
            ->assertSee($variant->display_name);
    }

}
