<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class AccessAndCheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_cannot_enter_admin_but_can_enter_account(): void
    {
        $customer = User::factory()->create([
            'is_admin' => false,
        ]);

        $this->actingAs($customer)
            ->get('/admin')
            ->assertForbidden();

        $this->actingAs($customer)
            ->get('/account')
            ->assertOk();
    }

    public function test_admin_can_enter_admin_and_is_redirected_from_customer_account(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $this->actingAs($admin)
            ->get('/admin')
            ->assertOk();

        $this->actingAs($admin)
            ->get('/account')
            ->assertRedirect('/admin');
    }

    public function test_checkout_creates_pending_order_payment_and_redirects_to_gateway(): void
    {
        Config::set('payment.driver', 'zarinpal');
        Config::set(
            'payment.zarinpal.merchant_id',
            'test-merchant'
        );

        Http::fake([
            'https://api.zarinpal.com/pg/v4/payment/request.json' =>
                Http::response([
                    'data' => [
                        'code' => 100,
                        'authority' => 'A000000000000000000000000001',
                    ],
                    'errors' => [],
                ], 200),
        ]);

        $user = User::factory()->create();

        [$product, $variant] = $this->makeProduct(stock: 5);

        $cart = Cart::create([
            'user_id' => $user->id,
            'last_activity_at' => now(),
        ]);

        CartItem::create([
            'cart_id' => $cart->id,
            'product_variant_id' => $variant->id,
            'quantity' => 2,
        ]);

        $response = $this
            ->actingAs($user)
            ->post('/checkout', [
                'customer_name' => 'کاربر تست',
                'customer_phone' => '09120000000',
                'customer_email' => 'test@example.com',
                'shipping_address' => 'تهران',
                'shipping_city' => 'تهران',
                'postal_code' => '1111111111',
            ]);

        $response->assertRedirect(
            'https://www.zarinpal.com/pg/StartPay/A000000000000000000000000001'
        );

        $order = Order::query()->firstOrFail();
        $payment = $order->payments()->firstOrFail();

        $this->assertSame('pending', $order->status);
        $this->assertSame('pending', $order->payment_status);
        $this->assertSame('200000.00', (string) $order->total);
        $this->assertSame('zarinpal', $payment->gateway);
        $this->assertSame(
            'A000000000000000000000000001',
            $payment->authority
        );
        $this->assertSame(3, $variant->fresh()->stock);
        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_gateway_request_failure_rolls_back_inventory_and_restores_cart(): void
    {
        Config::set('payment.driver', 'zarinpal');
        Config::set(
            'payment.zarinpal.merchant_id',
            'test-merchant'
        );

        Http::fake([
            'https://api.zarinpal.com/pg/v4/payment/request.json' =>
                Http::response([
                    'data' => [],
                    'errors' => [
                        'code' => -1,
                        'message' => 'test gateway failure',
                    ],
                ], 200),
        ]);

        $user = User::factory()->create();

        [, $variant] = $this->makeProduct(stock: 5);

        $cart = Cart::create([
            'user_id' => $user->id,
            'last_activity_at' => now(),
        ]);

        CartItem::create([
            'cart_id' => $cart->id,
            'product_variant_id' => $variant->id,
            'quantity' => 2,
        ]);

        $this
            ->actingAs($user)
            ->post('/checkout', [
                'customer_name' => 'کاربر تست',
                'customer_phone' => '09120000000',
                'customer_email' => 'test@example.com',
                'shipping_address' => 'تهران',
            ])
            ->assertRedirect();

        $order = Order::query()->firstOrFail();

        $this->assertSame('cancelled', $order->status);
        $this->assertSame('failed', $order->payment_status);
        $this->assertSame(5, $variant->fresh()->stock);
        $this->assertDatabaseHas('cart_items', [
            'cart_id' => $cart->id,
            'product_variant_id' => $variant->id,
            'quantity' => 2,
        ]);
    }

    public function test_successful_callback_confirms_order_and_payment_once(): void
    {
        Config::set('payment.driver', 'zarinpal');
        Config::set(
            'payment.zarinpal.merchant_id',
            'test-merchant'
        );

        Http::fakeSequence()
            ->push([
                'data' => [
                    'code' => 100,
                    'authority' => 'A000000000000000000000000002',
                ],
                'errors' => [],
            ], 200)
            ->push([
                'data' => [
                    'code' => 100,
                    'ref_id' => 987654321,
                ],
                'errors' => [],
            ], 200);

        $user = User::factory()->create();

        [, $variant] = $this->makeProduct(stock: 5);

        $cart = Cart::create([
            'user_id' => $user->id,
            'last_activity_at' => now(),
        ]);

        CartItem::create([
            'cart_id' => $cart->id,
            'product_variant_id' => $variant->id,
            'quantity' => 2,
        ]);

        $this
            ->actingAs($user)
            ->post('/checkout', [
                'customer_name' => 'کاربر تست',
                'customer_phone' => '09120000000',
                'customer_email' => 'test@example.com',
                'shipping_address' => 'تهران',
            ])
            ->assertRedirect();

        $order = Order::query()->firstOrFail();

        $callbackUrl = URL::signedRoute(
            'payment.zarinpal.callback',
            ['order' => $order->id]
        );

        $response = $this
            ->actingAs($user)
            ->get($callbackUrl . '&Authority=A000000000000000000000000002&Status=OK');

        $response->assertRedirect('/checkout/success');

        $this->assertSame(
            'confirmed',
            $order->fresh()->status
        );

        $this->assertSame(
            'paid',
            $order->fresh()->payment_status
        );

        $this->assertSame(
            '987654321',
            (string) $order->payments()->firstOrFail()->reference_number
        );

        $this->assertSame(3, $variant->fresh()->stock);
        $this->assertDatabaseCount('cart_items', 0);
        $this->assertDatabaseCount('financial_transactions', 1);
    }

    private function makeProduct(int $stock): array
    {
        $category = Category::create([
            'name' => 'ست تست',
            'slug' => 'sets-' . uniqid(),
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'محصول تست',
            'slug' => 'test-product-' . uniqid(),
            'is_active' => true,
            'is_featured' => false,
            'sort_order' => 1,
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'TEST-' . strtoupper(uniqid()),
            'price' => 100000,
            'sale_price' => null,
            'stock' => $stock,
            'low_stock_threshold' => 2,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        return [$product, $variant];
    }
}
