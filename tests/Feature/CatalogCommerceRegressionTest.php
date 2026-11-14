<?php

namespace Tests\Feature;

use App\Contracts\PaymentGateway;
use App\Models\Brand;
use App\Models\Cart;
use App\Models\Category;
use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\CartService;
use App\Services\InventoryService;
use App\Services\MediaService;
use App\Services\ZarinPalPaymentGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Mockery;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class CatalogCommerceRegressionTest extends TestCase
{
    use RefreshDatabase;

    public function test_inventory_movements_keep_stock_and_ledger_in_sync(): void
    {
        $variant = ProductVariant::factory()->create(['stock' => 10]);
        $inventory = app(InventoryService::class);

        $sale = $inventory->move($variant, -3, 'sale', 'Regression sale');
        $this->assertSame(7, $variant->fresh()->stock);
        $this->assertSame(-3, $sale->quantity);
        $this->assertSame(7, $sale->stock_after);

        $adjustment = $inventory->setStock($variant->fresh(), 12, 'Regression count');
        $this->assertNotNull($adjustment);
        $this->assertSame('adjustment', $adjustment->type);
        $this->assertSame(5, $adjustment->quantity);
        $this->assertSame(12, $variant->fresh()->stock);
        $this->assertNull($inventory->setStock($variant->fresh(), 12, 'No change'));

        $this->assertSame(2, InventoryMovement::query()
            ->where('product_variant_id', $variant->id)
            ->count());
    }

    public function test_cart_enforces_distinct_line_and_per_variant_quantity_limits(): void
    {
        $product = Product::factory()->create();
        $cart = Cart::query()->create([
            'session_id' => (string) Str::uuid(),
            'last_activity_at' => now(),
        ]);
        $variants = ProductVariant::factory()
            ->count(CartService::MAX_ITEMS + 1)
            ->create([
                'product_id' => $product->id,
                'stock' => 500,
                'is_active' => true,
            ]);

        $service = app(CartService::class);

        foreach ($variants->take(CartService::MAX_ITEMS) as $variant) {
            $service->add($cart, $variant, 1);
        }

        $this->assertSame(CartService::MAX_ITEMS, $cart->items()->count());

        try {
            $service->add($cart, $variants->last(), 1);
            $this->fail('The cart must reject a 101st distinct variant.');
        } catch (HttpException $exception) {
            $this->assertSame(422, $exception->getStatusCode());
        }

        try {
            $service->add($cart, $variants->first(), CartService::MAX_QUANTITY_PER_ITEM + 1);
            $this->fail('The cart must reject a quantity above its configured cap.');
        } catch (HttpException $exception) {
            $this->assertSame(422, $exception->getStatusCode());
        }

        $this->assertSame(CartService::MAX_ITEMS, $cart->items()->count());
        $this->assertSame(1, (int) $cart->items()->where('product_variant_id', $variants->first()->id)->value('quantity'));
    }

    public function test_admin_can_update_wholesale_price_on_the_selected_variant_only(): void
    {
        $category = Category::factory()->create();
        $brand = Brand::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'brand_id' => $brand->id,
        ]);
        $first = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'sku' => 'REG-FIRST',
            'price' => 500000,
            'wholesale_price' => 400000,
            'stock' => 10,
        ]);
        $selected = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'sku' => 'REG-SELECTED',
            'price' => 800000,
            'wholesale_price' => 700000,
            'stock' => 20,
            'sort_order' => 1,
        ]);

        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->put(
            route('admin.products.update', $product),
            [
                'category_id' => $category->id,
                'brand_id' => $brand->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'short_description' => $product->short_description,
                'description' => $product->description,
                'meta_title' => $product->meta_title,
                'meta_description' => $product->meta_description,
                'attributes_json' => '{}',
                'is_active' => 1,
                'is_featured' => 0,
                'sort_order' => 0,
                'variant_id' => $selected->id,
                'sku' => 'REG-SELECTED',
                'size' => $selected->size,
                'color' => $selected->color,
                'color_code' => $selected->color_code,
                'price' => 825000,
                'sale_price' => null,
                'wholesale_price' => 612500,
                'stock' => 20,
                'low_stock_threshold' => 5,
            ]
        );

        $response->assertRedirect(route('admin.products.index'));

        $this->assertSame('400000.00', $first->fresh()->wholesale_price);
        $this->assertSame('612500.00', $selected->fresh()->wholesale_price);
        $this->assertSame('825000.00', $selected->fresh()->price);
    }

    public function test_media_replacement_and_removal_clean_up_database_and_files(): void
    {
        Storage::fake('public');

        $product = Product::factory()->create();
        $categoryId = $product->category_id;
        $brandId = $product->brand_id;

        // Close RefreshDatabase's outer test transaction so the service's
        // afterCommit file cleanup runs at the same boundary as production.
        DB::commit();

        try {
            $media = app(MediaService::class);

            $old = $media->attach(
                $product,
                'gallery',
                UploadedFile::fake()->create('old.png', 10, 'image/png'),
                'products',
                'Old image'
            );

            Storage::disk('public')->assertExists($old->path);

            $new = $media->replace(
                $product,
                'gallery',
                UploadedFile::fake()->create('new.png', 12, 'image/png'),
                'products',
                'New image'
            );

            $this->assertDatabaseMissing('media', ['id' => $old->id]);
            Storage::disk('public')->assertMissing($old->path);
            Storage::disk('public')->assertExists($new->path);

            $media->remove($new);

            $this->assertDatabaseMissing('media', ['id' => $new->id]);
            Storage::disk('public')->assertMissing($new->path);
        } finally {
            DB::transaction(function () use ($product, $categoryId, $brandId): void {
                DB::table('media')
                    ->where('mediable_type', $product->getMorphClass())
                    ->where('mediable_id', $product->id)
                    ->delete();
                DB::table('products')->where('id', $product->id)->delete();
                DB::table('categories')->where('id', $categoryId)->delete();
                DB::table('brands')->where('id', $brandId)->delete();
            });

            DB::beginTransaction();
        }
    }

    public function test_price_sort_uses_the_same_minimum_active_variant_price_as_the_catalog_card(): void
    {
        $cheaper = Product::factory()->create(['name' => 'Cheaper product']);
        $moreExpensive = Product::factory()->create(['name' => 'More expensive product']);

        ProductVariant::factory()->create([
            'product_id' => $cheaper->id,
            'price' => 900000,
            'sale_price' => null,
            'is_active' => true,
            'sort_order' => 0,
        ]);
        ProductVariant::factory()->create([
            'product_id' => $cheaper->id,
            'price' => 250000,
            'sale_price' => 200000,
            'is_active' => true,
            'sort_order' => 1,
        ]);
        ProductVariant::factory()->create([
            'product_id' => $moreExpensive->id,
            'price' => 300000,
            'sale_price' => null,
            'is_active' => true,
        ]);

        $results = Product::query()
            ->active()
            ->withCatalogMetrics()
            ->orderByEffectivePrice('asc')
            ->get();

        $this->assertSame($cheaper->id, $results->first()->id);
        $this->assertEquals(200000, (float) $results->first()->minimum_effective_price);
        $this->assertSame($moreExpensive->id, $results->get(1)->id);
    }

    public function test_payment_verify_transport_failure_keeps_the_order_and_reserved_inventory_pending(): void
    {
        $variant = ProductVariant::factory()->create(['stock' => 4]);
        $order = Order::query()->create([
            'order_number' => 'PAY-REG-' . strtoupper(Str::random(8)),
            'customer_name' => 'Regression Customer',
            'customer_phone' => '09120000000',
            'shipping_address' => 'Test address',
            'status' => 'pending',
            'payment_status' => 'pending',
            'payment_method' => 'online',
            'order_type' => 'retail',
            'subtotal' => 100000,
            'discount' => 0,
            'shipping_cost' => 0,
            'total' => 100000,
            'placed_at' => now(),
        ]);

        $order->payments()->create([
            'gateway' => 'zarinpal',
            'idempotency_key' => 'regression:' . $order->id,
            'authority' => 'AUTH-REGRESSION',
            'amount' => 100000,
            'status' => 'pending',
        ]);

        InventoryMovement::query()->create([
            'product_variant_id' => $variant->id,
            'type' => 'sale',
            'quantity' => -1,
            'stock_after' => 4,
            'note' => 'Inventory reserved before callback',
        ]);

        $gateway = Mockery::mock(PaymentGateway::class);
        $gateway->shouldReceive('name')->andReturn('zarinpal');
        $gateway->shouldReceive('verify')
            ->once()
            ->andThrow(new RuntimeException('Temporary gateway timeout'));
        $this->app->instance(PaymentGateway::class, $gateway);

        $signedUrl = URL::temporarySignedRoute(
            'payment.zarinpal.callback',
            now()->addMinutes(5),
            ['order' => $order->id]
        );

        $response = $this->get($signedUrl . '&Authority=AUTH-REGRESSION&Status=OK');

        $response->assertRedirect(route('checkout.success'));
        $response->assertSessionHas('warning');
        $this->assertSame('pending', $order->fresh()->status);
        $this->assertSame('pending', $order->fresh()->payment_status);
        $this->assertSame('pending', $order->payments()->first()->fresh()->status);
        $this->assertSame(4, $variant->fresh()->stock);
        $this->assertSame(1, InventoryMovement::query()
            ->where('product_variant_id', $variant->id)
            ->count());
    }

    public function test_zarinpal_non_success_verification_response_does_not_mark_payment_failed(): void
    {
        $order = Order::query()->create([
            'order_number' => 'PAY-ZP-' . strtoupper(Str::random(8)),
            'customer_name' => 'Regression Customer',
            'customer_phone' => '09120000000',
            'shipping_address' => 'Test address',
            'status' => 'pending',
            'payment_status' => 'pending',
            'payment_method' => 'online',
            'order_type' => 'retail',
            'subtotal' => 100000,
            'discount' => 0,
            'shipping_cost' => 0,
            'total' => 100000,
            'placed_at' => now(),
        ]);
        $payment = $order->payments()->create([
            'gateway' => 'zarinpal',
            'idempotency_key' => 'regression-zp:' . $order->id,
            'authority' => 'AUTH-ZP-REGRESSION',
            'amount' => 100000,
            'status' => 'pending',
        ]);

        config([
            'payment.zarinpal.merchant_id' => '00000000-0000-0000-0000-000000000000',
            'payment.zarinpal.verify_url' => 'https://zarinpal.test/verify',
        ]);
        Http::fake([
            'https://zarinpal.test/verify' => Http::response([
                'data' => ['code' => -21],
                'errors' => [],
            ], 200),
        ]);

        try {
            app(ZarinPalPaymentGateway::class)->verify($payment);
            $this->fail('A non-success verification code must remain pending.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('نتیجه قطعی', $exception->getMessage());
        }

        $this->assertSame('pending', $payment->fresh()->status);
    }
}
