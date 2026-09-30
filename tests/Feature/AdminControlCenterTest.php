<?php

namespace Tests\Feature;

use App\Models\ChequePermission;
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
            ->assertSee('مرز داده نیلا');
    }

    public function test_cheque_permission_can_be_enabled_without_wholesale_approval(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $customer = User::factory()->create([
            'is_admin' => false,
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
