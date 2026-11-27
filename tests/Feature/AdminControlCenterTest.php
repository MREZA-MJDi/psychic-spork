<?php

namespace Tests\Feature;

use App\Models\ChequePermission;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Models\WholesaleProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminControlCenterTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_loads_as_operational_control_center(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $this->actingAs($admin)
            ->get('/admin')
            ->assertOk()
            ->assertSee('نیازمند پیگیری')
            ->assertSee('موجودی در محدودهٔ هشدار')
            ->assertSee('ورود فایل هلو')
            ->assertSee(route('admin.inventory.index'))
            ->assertSee(route('admin.nila.index'));
    }

    public function test_nila_control_center_loads_without_claiming_api_sync(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.nila.index'))
            ->assertOk()
            ->assertSee('ورود ماهانه اطلاعات نیلا')
            ->assertSee('فایل خروجی نیلا')
            ->assertSee('جانان چه چیزی را تغییر می‌دهد؟');
    }

    public function test_admin_can_approve_a_pending_cheque_request_without_wholesale_approval(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $customer = User::factory()->create([
            'is_admin' => false,
        ]);

        ChequePermission::create([
            'user_id' => $customer->id,
            'enabled' => false,
            'requested_amount' => 5000000,
            'requested_at' => now(),
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.customers.cheque.enable', $customer), [
                'max_order_amount' => 5000000,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('cheque_permissions', [
            'user_id' => $customer->id,
            'enabled' => true,
            'max_order_amount' => 5000000,
        ]);

        $this->assertDatabaseMissing('wholesale_profiles', [
            'user_id' => $customer->id,
        ]);
    }

    public function test_admin_can_grant_cheque_access_and_set_limit_without_customer_request(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $customer = User::factory()->create(['is_admin' => false]);

        $this->actingAs($admin)
            ->patch(route('admin.customers.cheque.enable', $customer), [
                'enabled' => '1',
                'max_order_amount' => '5,000,000',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('cheque_permissions', [
            'user_id' => $customer->id,
            'enabled' => true,
            'max_order_amount' => 5000000,
        ]);
    }

    public function test_customer_list_links_to_centralized_cheque_permission_controls(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $customer = User::factory()->create(['is_admin' => false]);
        $hubUrl = route('admin.cheques.index', [
            'tab' => 'customers', 'access' => 'all', 'customer_id' => $customer->id,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.customers.index'))
            ->assertOk()
            ->assertSee('مدیریت مجوز')
            ->assertSee($hubUrl)
            ->assertDontSee('name="max_order_amount"', false);

        $this->get($hubUrl)
            ->assertOk()
            ->assertSee('تأیید مجوز')
            ->assertSee('سقف هر سفارش (تومان)')
            ->assertSee(route('admin.customers.cheque.enable', $customer));
    }

    public function test_admin_variant_lookup_is_searchable_and_returns_a_bounded_page(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $product = Product::factory()->create(['name' => 'پیراهن ایزابلا']);

        foreach (range(1, 25) as $number) {
            ProductVariant::factory()->create([
                'product_id' => $product->id,
                'sku' => 'JAN-LOOKUP-' . $number,
                'color' => 'مشکی',
                'stock' => $number === 1 ? 3 : 0,
                'wholesale_price' => $number === 1 ? 800000 : null,
            ]);
        }

        $this->actingAs($admin)
            ->getJson(route('admin.variant-lookup'))
            ->assertOk()
            ->assertJsonCount(20, 'data')
            ->assertJsonPath('current_page', 1)
            ->assertJsonStructure(['data', 'next_page_url', 'current_page']);

        $this->actingAs($admin)
            ->getJson(route('admin.variant-lookup', ['q' => 'JAN-LOOKUP-1', 'wholesale' => 1]))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.sku', 'JAN-LOOKUP-1');

        $this->getJson(route('admin.variant-lookup', ['q' => 'ایزابلا مشكي', 'in_stock' => 1]))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.sku', 'JAN-LOOKUP-1');

        $this->getJson(route('admin.variant-lookup', ['q' => 'JAN-LOOKUP-۱', 'wholesale' => 1]))
            ->assertOk()
            ->assertJsonPath('data.0.sku', 'JAN-LOOKUP-1');

        $this->getJson(route('admin.variant-lookup', ['q' => 'ایزا', 'pack' => 1]))
            ->assertOk()
            ->assertJsonCount(20, 'data')
            ->assertJsonPath('data.0.pack_block_reason', null)
            ->assertJsonPath('data.1.pack_block_reason', 'قیمت عمده ثبت نشده است.')
            ->assertJsonStructure(['data' => [['edit_url', 'product_edit_url']]]);
    }

    public function test_suspending_wholesale_profile_does_not_revoke_cheque_permission(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $customer = User::factory()->create([
            'is_admin' => false,
        ]);

        WholesaleProfile::create([
            'user_id' => $customer->id,
            'status' => 'approved',
        ]);

        ChequePermission::create([
            'user_id' => $customer->id,
            'enabled' => true,
            'max_order_amount' => 5000000,
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.customers.wholesale.suspend', $customer))
            ->assertRedirect();

        $this->assertDatabaseHas('wholesale_profiles', [
            'user_id' => $customer->id,
            'status' => 'suspended',
        ]);

        $this->assertDatabaseHas('cheque_permissions', [
            'user_id' => $customer->id,
            'enabled' => true,
        ]);
    }
}
