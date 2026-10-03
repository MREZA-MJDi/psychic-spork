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
            ->assertSee('ACTION CENTER')
            ->assertSee('کارهایی که الان باید کنترل شوند')
            ->assertSee('کنترل موجودی')
            ->assertSee('واردسازی و نگاشت نیلا / هلو');
    }

    public function test_nila_control_center_loads_without_claiming_api_sync(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.nila.index'))
            ->assertOk()
            ->assertSee('مرکز کنترل نیلا')
            ->assertSee('Adapter متصل به سرویس بیرونی یا ابزار بارگذاری CSV/XLSX وجود ندارد')
            ->assertSee('مرز مالکیت داده');
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

    public function test_customer_list_does_not_offer_cheque_approval_before_a_customer_request(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        User::factory()->create(['is_admin' => false]);

        $this->actingAs($admin)
            ->get(route('admin.customers.index'))
            ->assertOk()
            ->assertSee('خرید چکی مجاز باشد')
            ->assertSee('مدیر می‌تواند بدون درخواست مشتری هم مجوز بدهد.')
            ->assertSee('سقف هر سفارش (تومان)');
    }

    public function test_admin_variant_lookup_is_searchable_and_returns_a_bounded_page(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $product = Product::factory()->create(['name' => 'پیراهن ایزابلا']);

        foreach (range(1, 25) as $number) {
            ProductVariant::factory()->create([
                'product_id' => $product->id,
                'sku' => 'JAN-LOOKUP-' . $number,
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
