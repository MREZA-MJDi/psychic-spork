<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class AuthenticationAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_admin_area(): void
    {
        $this->get(route('admin.dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_customer_cannot_access_admin_area(): void
    {
        $customer = User::factory()->create([
            'is_admin' => false,
        ]);

        $this->actingAs($customer)
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_admin_cannot_access_customer_account(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('account'))
            ->assertForbidden();
    }

    public function test_registration_cannot_escalate_privileges(): void
    {
        $response = $this->post(route('register.store'), [
            'name' => 'Security Test',
            'email' => 'security@example.test',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'is_admin' => true,
        ]);

        $response->assertRedirect(route('account'));

        $user = User::query()
            ->where('email', 'security@example.test')
            ->firstOrFail();

        $this->assertFalse($user->is_admin);
        $this->assertAuthenticatedAs($user);
    }

    public function test_valid_customer_login_authenticates_and_redirects_to_account(): void
    {
        $password = 'Password123!';

        $customer = User::factory()->create([
            'email' => 'customer@example.test',
            'password' => Hash::make($password),
            'is_admin' => false,
        ]);

        $response = $this->post(route('login.store'), [
            'identifier' => $customer->email,
            'password' => $password,
        ]);

        $response->assertRedirect(route('account'));
        $this->assertAuthenticatedAs($customer);
    }

    public function test_valid_admin_login_authenticates_and_redirects_to_dashboard(): void
    {
        $password = 'Password123!';

        $admin = User::factory()->create([
            'email' => 'admin@example.test',
            'password' => Hash::make($password),
            'is_admin' => true,
        ]);

        $response = $this->post(route('login.store'), [
            'identifier' => $admin->email,
            'password' => $password,
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin);
    }

    public function test_login_attempts_are_rate_limited(): void
    {
        RateLimiter::clear('wrong@example.test|127.0.0.1');

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post(route('login.store'), [
                'identifier' => 'wrong@example.test',
                'password' => 'wrong-password',
            ])->assertRedirect();
        }

        $this->post(route('login.store'), [
            'identifier' => 'wrong@example.test',
            'password' => 'wrong-password',
        ])->assertTooManyRequests();
    }

    public function test_logout_invalidates_the_authenticated_session(): void
    {
        $customer = User::factory()->create([
            'is_admin' => false,
        ]);

        $this->actingAs($customer);

        $this->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }
}
