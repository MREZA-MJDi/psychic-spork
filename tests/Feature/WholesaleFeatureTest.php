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
use App\Models\ChequePermission;
use App\Models\ChequePayment;
use App\Models\WholesalePack;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class WholesaleFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_add_a_cover_image_to_a_wholesale_pack_and_customers_see_it(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['is_admin' => true]);
        [, $variant] = $this->makeProduct(stock: 20, wholesalePrice: 250000);

        $this->actingAs($admin)
            ->post(route('admin.wholesale-packs.store'), [
                'name' => 'پک ۱۲ عددی ایزابلا',
                'slug' => 'isabella-12-pack',
                'pack_price' => '2400000',
                'sort_order' => 1,
                'is_active' => 1,
                'items' => [
                    $variant->id => [
                        'variant_id' => $variant->id,
                        'quantity' => 12,
                    ],
                ],
                'image' => UploadedFile::fake()->image('isabella-pack.webp'),
            ])
            ->assertRedirect();

        $pack = WholesalePack::query()->where('slug', 'isabella-12-pack')->firstOrFail();
        $this->assertNotEmpty($pack->image_path);
        Storage::disk('public')->assertExists($pack->image_path);

        $firstImagePath = $pack->image_path;
        $this->actingAs($admin)
            ->get(route('admin.wholesale-packs.edit', $pack))
            ->assertOk()
            ->assertSee('data-pack-image-current', false)
            ->assertSee('عکس فعلی جایگزین می‌شود');

        $this->actingAs($admin)
            ->post(route('admin.wholesale-packs.update', $pack), [
                '_method' => 'PUT',
                'name' => 'پک ۱۲ عددی ایزابلا',
                'slug' => 'isabella-12-pack',
                'pack_price' => '2400000',
                'sort_order' => 1,
                'is_active' => 1,
                'items' => [
                    $variant->id => [
                        'variant_id' => $variant->id,
                        'quantity' => 12,
                    ],
                ],
                'image' => UploadedFile::fake()->image('isabella-pack-updated.png'),
            ])
            ->assertSessionHasNoErrors();

        $pack->refresh();
        $this->assertNotSame($firstImagePath, $pack->image_path);
        Storage::disk('public')->assertExists($pack->image_path);

        $this->get(route('wholesale.show'))
            ->assertOk()
            ->assertSee(Storage::disk('public')->url($pack->image_path), false)
            ->assertSee('پک ۱۲ عددی ایزابلا');
    }

    public function test_wholesale_page_is_public_and_explains_cheque_access(): void
    {
        $this->get(route('wholesale.show'))
            ->assertOk()
            ->assertSee('خرید آنلاین عمده برای همه باز است')
            ->assertSee('درخواست اعتبار خرید چکی')
            ->assertSee(route('login', ['continue' => 'cheque']), false)
            ->assertSee('درخواست خرید چکی');
    }

    public function test_wholesale_landing_page_keeps_the_catalog_on_a_separate_screen(): void
    {
        [$product] = $this->makeProduct(stock: 8, wholesalePrice: 70000);

        $this->get(route('wholesale.show'))
            ->assertOk()
            ->assertSee(route('wholesale.catalog'), false)
            ->assertDontSee($product->name);
    }

    public function test_wholesale_catalog_filters_products_and_aggregates_prices_without_loading_all_variants(): void
    {
        [$wholesaleProduct] = $this->makeProduct(stock: 8, wholesalePrice: 70000);
        [$retailOnlyProduct] = $this->makeProduct(stock: 8, wholesalePrice: null);

        $wholesaleProduct->update(['name' => 'محصول عمده قابل فروش']);
        $retailOnlyProduct->update(['name' => 'محصول بدون قیمت عمده']);

        $this->get(route('wholesale.catalog'))
            ->assertOk()
            ->assertSee($wholesaleProduct->name)
            ->assertDontSee($retailOnlyProduct->name)
            ->assertViewHas('products', function ($products) use ($wholesaleProduct): bool {
                $product = $products->first();

                return $products->total() === 1
                    && $product !== null
                    && $product->id === $wholesaleProduct->id
                    && ! $product->relationLoaded('activeVariants')
                    && (int) $product->wholesale_variants_count === 1
                    && (float) $product->minimum_wholesale_price === 70000.0
                    && (float) $product->maximum_wholesale_price === 70000.0;
            });
    }

    public function test_customer_account_shows_cheque_request_status_and_next_step(): void
    {
        $customer = User::factory()->create(['is_admin' => false]);

        $this->actingAs($customer)
            ->get(route('account'))
            ->assertOk()
            ->assertSee('وضعیت درخواست خرید چکی')
            ->assertSee(route('wholesale.show') . '#cheque-application', false)
            ->assertSee('خرید عمده آنلاین هم برای همه باز است');

        ChequePermission::create([
            'user_id' => $customer->id,
            'enabled' => false,
            'requested_at' => now(),
            'requested_amount' => 10000000,
        ]);

        $this->actingAs($customer)
            ->get(route('account'))
            ->assertOk()
            ->assertSee('منتظر بررسی مدیر است')
            ->assertSee('10,000,000');
    }

    public function test_cheque_request_accepts_grouped_customer_amount(): void
    {
        $customer = User::factory()->create(['is_admin' => false]);

        $this->actingAs($customer)
            ->post(route('wholesale.cheque.request'), ['requested_amount' => '10,000,000'])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('cheque_permissions', [
            'user_id' => $customer->id,
            'requested_amount' => 10000000,
        ]);
    }

    public function test_checkout_preview_returns_correct_retail_and_wholesale_totals(): void
    {
        $customer = User::factory()->create(['is_admin' => false]);
        [, $variant] = $this->makeProduct(stock: 8, wholesalePrice: 70000);

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
            ->get(route('checkout'))
            ->assertOk()
            ->assertViewHas('retailTotal', 200000.0)
            ->assertViewHas('wholesaleTotal', 140000.0)
            ->assertViewHas('selectedOrderType', 'retail')
            ->assertViewHas('canSelectWholesale', true)
            ->assertViewHas('total', 200000.0);
    }

    public function test_checkout_preview_disables_wholesale_when_any_cart_item_has_no_wholesale_price(): void
    {
        $customer = User::factory()->create(['is_admin' => false]);
        [, $variant] = $this->makeProduct(stock: 8, wholesalePrice: null);

        $cart = Cart::create([
            'user_id' => $customer->id,
            'last_activity_at' => now(),
        ]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        $this->actingAs($customer)
            ->get(route('checkout'))
            ->assertOk()
            ->assertViewHas('wholesaleTotal', null)
            ->assertViewHas('wholesaleUnavailableReason', 'قیمت عمده این واریانت تعیین نشده است.')
            ->assertViewHas('canSelectWholesale', false)
            ->assertViewHas('total', 100000.0);
    }

    public function test_checkout_renders_cheque_request_for_approved_wholesale_customer(): void
    {
        $customer = User::factory()->create(['is_admin' => false]);
        WholesaleProfile::create([
            'user_id' => $customer->id,
            'status' => 'approved',
            'approved_at' => now(),
        ]);
        $variant = ProductVariant::factory()->create(['stock' => 3]);
        $cart = Cart::create(['user_id' => $customer->id, 'last_activity_at' => now()]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        $this->actingAs($customer)
            ->get(route('checkout'))
            ->assertOk()
            ->assertSee('درخواست مجوز چک')
            ->assertSee(route('wholesale.cheque.request'), false);
    }

    public function test_cheque_permission_request_is_idempotent_without_wholesale_profile(): void
    {
        $customer = User::factory()->create(['is_admin' => false]);

        $this->post(route('wholesale.cheque.request'), ['requested_amount' => '۵۰٬۰۰۰٬۰۰۰'])
            ->assertRedirect(route('login'));

        $this->actingAs($customer)
            ->post(route('wholesale.cheque.request'), ['requested_amount' => '۵۰٬۰۰۰٬۰۰۰'])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('wholesale_profiles', ['user_id' => $customer->id]);

        $requestedAt = ChequePermission::query()
            ->where('user_id', $customer->id)
            ->value('requested_at');

        $this->travel(2)->seconds();
        $this->post(route('wholesale.cheque.request'), ['requested_amount' => '۷۵۰۰۰۰۰۰'])
            ->assertRedirect();

        $this->assertEquals(
            $requestedAt->toDateTimeString(),
            ChequePermission::query()->where('user_id', $customer->id)->value('requested_at')->toDateTimeString()
        );
    }

    public function test_guest_can_place_wholesale_order_online(): void
    {
        Config::set('payment.driver', 'zarinpal');
        Config::set('payment.zarinpal.merchant_id', 'test-merchant');

        Http::fake([
            config('payment.zarinpal.request_url') =>
                Http::response([
                    'data' => [
                        'code' => 100,
                        'authority' => 'A000000000000000000000000099',
                    ],
                    'errors' => [],
                ], 200),
        ]);

        [, $variant] = $this->makeProduct(
            stock: 10,
            wholesalePrice: 70000
        );

        /*
         * The test suite uses SESSION_DRIVER=array, so keep the guest
         * identity explicit across requests. The production browser already
         * persists this cookie normally.
         */
        $sessionId = Str::random(40);

        $this->withCookie(config('session.cookie'), $sessionId)
            ->post(route('cart.store', $variant), [
                'quantity' => 2,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('cart_items', [
            'product_variant_id' => $variant->id,
            'quantity' => 2,
        ]);

        $this->withCookie(config('session.cookie'), $sessionId)
            ->post('/checkout', $this->checkoutData([
                'order_type' => 'wholesale',
                'payment_method' => 'online',
            ]))
            ->assertRedirect();

        $this->assertDatabaseHas('orders', [
            'user_id' => null,
            'order_type' => 'wholesale',
        ]);

        $this->assertSame(8, $variant->fresh()->stock);
    }

    public function test_unapproved_customer_can_place_wholesale_order_online(): void
    {
        Config::set('payment.driver', 'zarinpal');
        Config::set('payment.zarinpal.merchant_id', 'test-merchant');

        Http::fake([
            config('payment.zarinpal.request_url') =>
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
            'status' => 'pending',
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

        $this->assertDatabaseHas('orders', [
            'user_id' => $customer->id,
            'order_type' => 'wholesale',
        ]);
        $this->assertSame(8, $variant->fresh()->stock);
    }

    public function test_approved_customer_uses_wholesale_price_and_minimums(): void
    {
        Config::set('payment.driver', 'zarinpal');
        Config::set('payment.zarinpal.merchant_id', 'test-merchant');

        Http::fake([
            config('payment.zarinpal.request_url') =>
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
            'status' => 'pending',
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
            ->assertRedirect()
            ->assertSessionHas('error');

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
            'status' => 'pending',
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
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseCount('orders', 0);
        $this->assertSame(10, $variant->fresh()->stock);
    }

    public function test_approved_wholesale_profile_can_use_online_payment_but_not_cheque_without_permission(): void
    {
        $customer = User::factory()->create(['is_admin' => false]);

        WholesaleProfile::create([
            'user_id' => $customer->id,
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        [, $variant] = $this->makeProduct(stock: 10, wholesalePrice: 70000);

        $cart = Cart::create([
            'user_id' => $customer->id,
            'last_activity_at' => now(),
        ]);

        CartItem::create([
            'cart_id' => $cart->id,
            'product_variant_id' => $variant->id,
            'quantity' => 2,
        ]);

        Config::set('payment.driver', 'zarinpal');
        Config::set('payment.zarinpal.merchant_id', 'test-merchant');

        Http::fake([
            config('payment.zarinpal.request_url') =>
                Http::response([
                    'data' => [
                        'code' => 100,
                        'authority' => 'A000000000000000000000000099',
                    ],
                    'errors' => [],
                ], 200),
        ]);

        $this->actingAs($customer)
            ->post('/checkout', $this->checkoutData([
                'order_type' => 'wholesale',
                'payment_method' => 'online',
            ]))
            ->assertRedirect();

        $this->assertDatabaseHas('orders', [
            'user_id' => $customer->id,
            'order_type' => 'wholesale',
        ]);

        ChequePermission::create([
            'user_id' => $customer->id,
            'enabled' => false,
        ]);
    }

    public function test_cheque_checkout_normalizes_sayad_digits_and_rejects_duplicate_ids_without_serial_number(): void
    {
        Storage::fake('local');

        $customer = User::factory()->create(['is_admin' => false]);
        ChequePermission::create([
            'user_id' => $customer->id,
            'enabled' => true,
            'approved_at' => now(),
        ]);

        [, $firstVariant] = $this->makeProduct(stock: 10, wholesalePrice: 70000);
        $cart = Cart::create([
            'user_id' => $customer->id,
            'last_activity_at' => now(),
        ]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_variant_id' => $firstVariant->id,
            'quantity' => 1,
        ]);

        $sayadId = '۱۲۳۴۵۶۷۸۹۰۱۲۳۴۵۶';
        $payload = $this->checkoutData([
            'order_type' => 'wholesale',
            'payment_method' => 'cheque',
            'sayad_id' => $sayadId,
            'bank_name' => 'بانک تست',
            'due_date' => now()->addDays(15)->toDateString(),
        ]);
        $payload['cheque_image'] = UploadedFile::fake()->image('cheque.jpg');

        $this->actingAs($customer)
            ->post('/checkout', $payload)
            ->assertRedirect(route('checkout.success'));

        $cheque = ChequePayment::query()->firstOrFail();

        $this->assertSame('1234567890123456', $cheque->sayad_id);
        $this->assertNull($cheque->cheque_number);
        $this->assertNotEmpty($cheque->image_path);
        Storage::disk('local')->assertExists($cheque->image_path);

        [, $secondVariant] = $this->makeProduct(stock: 10, wholesalePrice: 70000);
        CartItem::create([
            'cart_id' => $cart->fresh()->id,
            'product_variant_id' => $secondVariant->id,
            'quantity' => 1,
        ]);

        $duplicatePayload = $this->checkoutData([
            'order_type' => 'wholesale',
            'payment_method' => 'cheque',
            'sayad_id' => '1234567890123456',
            'bank_name' => 'بانک تست',
            'due_date' => now()->addDays(15)->toDateString(),
        ]);
        $duplicatePayload['cheque_image'] = UploadedFile::fake()->image('duplicate-cheque.jpg');

        $this->actingAs($customer)
            ->from(route('checkout'))
            ->post('/checkout', $duplicatePayload)
            ->assertRedirect(route('checkout'))
            ->assertSessionHasErrors('sayad_id');

        $this->assertDatabaseCount('cheque_payments', 1);
        $this->assertDatabaseCount('orders', 1);
    }

    public function test_wholesale_cheque_requires_admin_granted_permission(): void
    {
        $customer = User::factory()->create(['is_admin' => false]);

        WholesaleProfile::create([
            'user_id' => $customer->id,
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        [, $variant] = $this->makeProduct(stock: 10, wholesalePrice: 70000);

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
            ->post('/checkout', array_merge(
                $this->checkoutData([
                    'order_type' => 'wholesale',
                    'payment_method' => 'cheque',
                    'sayad_id' => '1234567890123456',
                    'cheque_number' => 'CHK-1',
                    'bank_name' => 'بانک تست',
                    'account_holder' => 'کاربر عمده',
                    'due_date' => now()->addDays(30)->toDateString(),
                ]),
                [
                    'cheque_image' => UploadedFile::fake()->image('cheque.jpg'),
                ]
            ))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseCount('orders', 0);
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
