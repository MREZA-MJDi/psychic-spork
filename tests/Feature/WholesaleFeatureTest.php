<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Models\WholesaleProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WholesaleFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_apply_for_wholesale_access(): void
    {
        $customer = User::factory()->create([
            'is_admin' => false,
        ]);

        $this->actingAs($customer)
            ->post('/wholesale/apply', [
                'business_name' => 'فروشگاه تست',
                'business_type' => 'پوشاک',
                'business_phone' => '02112345678',
                'business_address' => 'تهران',
            ])
            ->assertRedirect('/wholesale');

        $this->assertDatabaseHas('wholesale_profiles', [
            'user_id' => $customer->id,
            'status' => 'pending',
            'business_name' => 'فروشگاه تست',
        ]);
    }

    public function test_unapproved_customer_cannot_place_wholesale_order(): void
    {
        $customer = User::factory()->create([
            'is_admin' => false,
        ]);

        [, $variant] = $this->makeProduct(
            stock: 10,
            wholesalePrice: 70000
        );

        $cart = Cart::create([
            'user_id' => $customer->id,
            'last_activity_at' => now(),
        ]);

        CartItem::create([
            'cart_id' => $cart->id,
            'product_variant_id' => $variant->id,
            'quantity' => 2,
        ]);

        $this->actingAs($customer)
            ->post('/checkout', $this->checkoutData([
                'order_type' => 'wholesale',
            ]))
            ->assertForbidden();

        $this->assertDatabaseCount('orders', 0);
        $this->assertSame(10, $variant->fresh()->stock);
    }

    public function test_approved_customer_uses_wholesale_price_and_minimums(): void
    {
        Config::set('payment.driver', 'zarinpal');
        Config::set('payment.zarinpal.merchant_id', 'test-merchant');

        Http::fake([
            'https://api.zarinpal.com/pg/v4/payment/request.json' =>
                Http::response([
                    'data' => [
                        'code' => 100,
                        'authority' => 'A000000000000000000000000099',
                    ],
                    'errors' => [],
                ], 200),
        ]);

        $customer = User::factory()->create([
            'is_admin' => false,
        ]);

        WholesaleProfile::create([
            'user_id' => $customer->id,
            'status' => 'approved',
            'approved_at' => now(),
            'minimum_order_amount' => 100000,
            'minimum_order_quantity' => 2,
        ]);

        [, $variant] = $this->makeProduct(
            stock: 10,
            wholesalePrice: 70000
        );

        $cart = Cart::create([
            'user_id' => $customer->id,
            'last_activity_at' => now(),
        ]);

        CartItem::create([
            'cart_id' => $cart->id,
            'product_variant_id' => $variant->id,
            'quantity' => 2,
        ]);

        $this->actingAs($customer)
            ->post('/checkout', $this->checkoutData([
                'order_type' => 'wholesale',
            ]))
            ->assertRedirect();

        $order = Order::query()->firstOrFail();

        $this->assertSame('wholesale', $order->order_type);
        $this->assertSame('140000.00', (string) $order->total);
        $this->assertSame('70000.00', (string) $order->items()->firstOrFail()->unit_price);
        $this->assertSame(8, $variant->fresh()->stock);
    }

    public function test_wholesale_minimum_amount_is_enforced_before_payment(): void
    {
        $customer = User::factory()->create([
            'is_admin' => false,
        ]);

        WholesaleProfile::create([
            'user_id' => $customer->id,
            'status' => 'approved',
            'approved_at' => now(),
            'minimum_order_amount' => 200000,
        ]);

        [, $variant] = $this->makeProduct(
            stock: 10,
            wholesalePrice: 70000
        );

        $cart = Cart::create([
            'user_id' => $customer->id,
            'last_activity_at' => now(),
        ]);

        CartItem::create([
            'cart_id' => $cart->id,
            'product_variant_id' => $variant->id,
            'quantity' => 2,
        ]);

        $this->actingAs($customer)
            ->post('/checkout', $this->checkoutData([
                'order_type' => 'wholesale',
            ]))
            ->assertStatus(422);

        $this->assertDatabaseCount('orders', 0);
        $this->assertSame(10, $variant->fresh()->stock);
    }

    public function test_wholesale_order_requires_a_wholesale_price(): void
    {
        $customer = User::factory()->create([
            'is_admin' => false,
        ]);

        WholesaleProfile::create([
            'user_id' => $customer->id,
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        [, $variant] = $this->makeProduct(
            stock: 10,
            wholesalePrice: null
        );

        $cart = Cart::create([
            'user_id' => $customer->id,
            'last_activity_at' => now(),
        ]);

        CartItem::create([
            'cart_id' => $cart->id,
            'product_variant_id' => $variant->id,
            'quantity' => 2,
        ]);

        $this->actingAs($customer)
            ->post('/checkout', $this->checkoutData([
                'order_type' => 'wholesale',
            ]))
            ->assertStatus(422);

        $this->assertDatabaseCount('orders', 0);
        $this->assertSame(10, $variant->fresh()->stock);
    }

    private function checkoutData(array $overrides = []): array
    {
        return array_merge([
            'customer_name' => 'کاربر عمده',
            'customer_phone' => '09120000000',
            'customer_email' => 'wholesale@example.com',
            'shipping_address' => 'تهران',
            'shipping_city' => 'تهران',
            'postal_code' => '1111111111',
            'payment_method' => 'online',
            'order_type' => 'retail',
        ], $overrides);
    }

    private function makeProduct(
        int $stock,
        ?float $wholesalePrice
    ): array {
        $category = Category::create([
            'name' => 'ست عمده تست',
            'slug' => 'wholesale-sets-' . uniqid(),
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'محصول عمده تست',
            'slug' => 'wholesale-product-' . uniqid(),
            'is_active' => true,
            'is_featured' => false,
            'sort_order' => 1,
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'WHOLE-' . strtoupper(uniqid()),
            'price' => 100000,
            'sale_price' => null,
            'wholesale_price' => $wholesalePrice,
            'stock' => $stock,
            'low_stock_threshold' => 2,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        return [$product, $variant];
    }
}
