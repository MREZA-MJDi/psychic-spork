<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\InventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccessAndCheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_cannot_enter_admin_but_can_enter_account(): void
    {
        $customer = User::factory()->create(['is_admin' => false]);

        $this->actingAs($customer)
            ->get('/admin')
            ->assertForbidden();

        $this->actingAs($customer)
            ->get('/account')
            ->assertOk();
    }

    public function test_admin_can_enter_admin_and_is_redirected_from_customer_account(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->get('/admin')
            ->assertOk();

        $this->actingAs($admin)
            ->get('/account')
            ->assertRedirect('/admin');
    }

    public function test_checkout_creates_order_and_deducts_inventory_atomically(): void
    {
        $category = Category::create([
            'name' => 'ست',
            'slug' => 'sets',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'محصول تست',
            'slug' => 'test-product',
            'sku' => 'TEST-001',
            'price' => 100000,
            'sale_price' => null,
            'stock' => 5,
            'low_stock_threshold' => 2,
            'is_active' => true,
            'is_featured' => false,
        ]);

        $response = $this
            ->withSession(['cart' => [$product->id => 2]])
            ->post('/checkout', [
                'customer_name' => 'کاربر تست',
                'customer_phone' => '09120000000',
                'customer_email' => 'test@example.com',
                'shipping_address' => 'تهران',
                'shipping_city' => 'تهران',
                'postal_code' => '1111111111',
                'customer_note' => null,
            ]);

        $response->assertRedirect('/checkout/success');

        $this->assertDatabaseHas('orders', [
            'customer_phone' => '09120000000',
            'status' => 'confirmed',
            'payment_status' => 'pending',
            'total' => 200000,
        ]);

        $this->assertDatabaseHas('inventory_movements', [
            'product_id' => $product->id,
            'type' => 'sale',
            'quantity' => -2,
        ]);

        $this->assertSame(3, $product->fresh()->stock);
        $this->assertSame(1, Order::count());
    }

    public function test_cancelling_an_order_restores_inventory_once(): void
    {
        $category = Category::create([
            'name' => 'ست',
            'slug' => 'sets',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'محصول تست',
            'slug' => 'test-product-cancel',
            'sku' => 'TEST-002',
            'price' => 100000,
            'stock' => 5,
            'low_stock_threshold' => 2,
            'is_active' => true,
            'is_featured' => false,
        ]);

        $order = Order::create([
            'order_number' => 'TEST-ORDER-001',
            'customer_name' => 'تست',
            'customer_phone' => '09120000000',
            'shipping_address' => 'تهران',
            'shipping_city' => 'تهران',
            'status' => 'confirmed',
            'payment_status' => 'pending',
            'payment_method' => 'cash_on_delivery',
            'subtotal' => 200000,
            'discount' => 0,
            'shipping_cost' => 0,
            'total' => 200000,
            'placed_at' => now(),
        ]);

        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'sku' => $product->sku,
            'unit_price' => 100000,
            'quantity' => 2,
            'line_total' => 200000,
        ]);

        $admin = User::factory()->create(['is_admin' => true]);
        app(InventoryService::class)->deductForOrder($order, $admin->id);

        $this->actingAs($admin)
            ->put("/admin/orders/{$order->id}", [
                'status' => 'cancelled',
                'payment_status' => 'pending',
                'customer_note' => null,
            ])
            ->assertRedirect();

        $this->assertSame(5, $product->fresh()->stock);

        $this->actingAs(User::factory()->create(['is_admin' => true]))
            ->put("/admin/orders/{$order->id}", [
                'status' => 'cancelled',
                'payment_status' => 'pending',
                'customer_note' => null,
            ])
            ->assertRedirect();

        $this->assertSame(5, $product->fresh()->stock);
    }
}
