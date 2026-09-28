<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('data-product-carousel', false)
            ->assertSee('data-product-prev', false)
            ->assertSee('data-product-next', false)
            ->assertSee('دیدن بیشتر محصولات')
            ->assertSee('محصول تست 1')
            ->assertSee('محصول تست 12');
    }
}
