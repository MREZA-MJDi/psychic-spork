<?php

namespace Tests\Feature;

use App\Models\Category;
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

}
