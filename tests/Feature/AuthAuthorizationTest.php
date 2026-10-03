<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_enter_admin_dashboard(): void
    {
        $this->get(route('admin.dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_customer_cannot_enter_admin_dashboard(): void
    {
        $customer = User::factory()->create([
            'is_admin' => false,
        ]);

        $this->actingAs($customer)
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_admin_login_lands_on_admin_dashboard(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin-test@janan.local',
            'phone' => '09120000003',
            'password' => Hash::make('AdminPass123!'),
            'is_admin' => true,
        ]);

        $this->post(route('login.store'), [
            'phone' => $admin->phone,
            'password' => 'AdminPass123!',
        ])
            ->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($admin);
    }

    public function test_new_registration_is_never_created_as_admin(): void
    {
        $this->post(route('register.store'), [
            'name' => 'Customer Test',
            'email' => 'customer-test@janan.local',
            'phone' => '09120000004',
            'password' => 'CustomerPass123!',
            'password_confirmation' => 'CustomerPass123!',
        ])
            ->assertRedirect(route('account'));

        $this->assertDatabaseHas('users', [
            'email' => 'customer-test@janan.local',
            'is_admin' => false,
        ]);
    }
}
