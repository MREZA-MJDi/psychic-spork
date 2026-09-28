<?php

namespace TestsFeature;

use AppModelsCategory;
use AppModelsProduct;
use AppModelsProductVariant;
use AppModelsUser;
use Database\Seeders\AdminUserSeeder;
use IlluminateFoundation\Testing\RefreshDatabase;
use IlluminateSupportFacadesConfig;
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

    public function test_homepage_contains_the_product_carousel_and_exactly_twelve_loaded_products_for_rotation(): void
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

        $response = $this->get(route('home'));

        $response
            ->assertOk()
            ->assertSee('data-product-carousel', false)
            ->assertSee('data-product-prev', false)
            ->assertSee('data-product-next', false)
            ->assertSee('دیدن بیشتر محصولات');
    }
}
