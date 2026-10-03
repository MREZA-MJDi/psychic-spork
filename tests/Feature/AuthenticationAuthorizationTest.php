<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Cart;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class AuthenticationAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_and_registration_pages_render(): void
    {
        $this->get(route('login'))->assertOk()->assertSee('خوش برگشتی.');
        $this->get(route('register'))->assertOk()->assertSee('فضای خودت را بساز.');
    }

    public function test_local_admin_credentials_are_shown_only_when_they_match_a_real_admin_account(): void
    {
        $phone = '09129990001';
        $password = 'LocalAdminPass42!';
        config()->set('app.admin.phone', $phone);
        config()->set('app.admin.password', $password);

        User::factory()->create([
            'phone' => $phone,
            'password' => Hash::make($password),
            'is_admin' => true,
        ]);

        $this->get(route('login'))
            ->assertOk()
            ->assertSee($phone)
            ->assertSee($password)
            ->assertSee('فقط در حالت لوکال یا تست نمایش داده می‌شود.');
    }

    public function test_guest_is_redirected_from_admin_area(): void
    {
        $this->get(route('admin.dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_customer_cannot_access_admin_area(): void
    {
        $customer = User::factory()->create();
        $customer->forceFill(['is_admin' => false])->save();

        $this->actingAs($customer)
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_customer_cannot_open_cheque_administration(): void
    {
        $customer = User::factory()->create(['is_admin' => false]);

        $this->actingAs($customer)
            ->get(route('admin.cheques.index'))
            ->assertForbidden();
    }

    public function test_admin_cannot_access_customer_account(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true])->save();

        $this->actingAs($admin)
            ->get(route('account'))
            ->assertForbidden();
    }

    public function test_registration_cannot_escalate_privileges(): void
    {
        $response = $this->post(route('register.store'), [
            'name' => 'Security Test',
            'phone' => '۰۹۱۲۳۴۵۶۷۸۹',
            'email' => 'security@example.test',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'is_admin' => true,
        ]);

        $response->assertRedirect(route('account'));

        $user = User::query()
            ->where('phone', '09123456789')
            ->firstOrFail();

        $this->assertFalse($user->is_admin);
        $this->assertAuthenticatedAs($user);
    }

    public function test_valid_customer_login_authenticates_and_redirects_to_account(): void
    {
        $password = 'Password123!';

        $customer = User::factory()->create([
            'email' => 'customer@example.test',
            'phone' => '09120000001',
            'password' => Hash::make($password),
        ]);
        $customer->forceFill(['is_admin' => false])->save();

        $response = $this->post(route('login.store'), [
            'phone' => '+98 912 000 0001',
            'password' => $password,
        ]);

        $response->assertRedirect(route('account'));
        $this->assertAuthenticatedAs($customer);
    }

    public function test_cheque_login_continuation_returns_customer_to_request_section(): void
    {
        $password = 'Password123!';
        $customer = User::factory()->create([
            'phone' => '09120000009',
            'password' => Hash::make($password),
            'is_admin' => false,
        ]);

        $this->get(route('login', ['continue' => 'cheque']))->assertOk();
        $this->post(route('login.store'), [
            'phone' => $customer->phone,
            'password' => $password,
        ])->assertRedirect(route('wholesale.show').'#cheque-application');
    }

    public function test_registration_uses_normalized_mobile_number_without_email(): void
    {
        $this->post(route('register.store'), [
            'name' => 'Mobile Customer',
            'phone' => '+98 912 345 6789',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ])->assertRedirect(route('account'));

        $this->assertDatabaseHas('users', [
            'phone' => '09123456789',
            'email' => null,
            'is_admin' => false,
        ]);
    }

    public function test_registration_merges_the_guest_cart_into_the_new_account(): void
    {
        $variant = ProductVariant::factory()->create(['stock' => 4]);
        $cartResponse = $this->post(route('cart.store', $variant), ['quantity' => 2]);
        $cartResponse->assertRedirect();
        $sessionCookie = $cartResponse->getCookie(config('session.cookie'));
        if ($sessionCookie) {
            $this->withCookie(config('session.cookie'), $sessionCookie->getValue());
        }
        $guestCart = Cart::query()->whereNull('user_id')->firstOrFail();

        $this->post(route('register.store'), [
            'name' => 'Cart Customer',
            'phone' => '09121112222',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ])->assertRedirect(route('account'));

        $customer = User::query()->where('phone', '09121112222')->firstOrFail();
        $this->assertDatabaseHas('cart_items', [
            'cart_id' => Cart::query()->where('user_id', $customer->id)->value('id'),
            'product_variant_id' => $variant->id,
            'quantity' => 2,
        ]);
        $this->assertDatabaseMissing('carts', ['id' => $guestCart->id]);
    }

    public function test_email_is_not_accepted_as_login_identifier(): void
    {
        $customer = User::factory()->create([
            'email' => 'legacy@example.test',
            'phone' => '09123456780',
            'password' => Hash::make('Password123!'),
        ]);

        $this->post(route('login.store'), [
            'phone' => 'legacy@example.test',
            'password' => 'Password123!',
        ])->assertSessionHasErrors('phone');

        $this->assertGuest();
    }

    public function test_valid_admin_login_authenticates_and_redirects_to_dashboard(): void
    {
        $password = 'Password123!';

        $admin = User::factory()->create([
            'email' => 'admin@example.test',
            'phone' => '09120000002',
            'password' => Hash::make($password),
        ]);
        $admin->forceFill(['is_admin' => true])->save();

        $response = $this->post(route('login.store'), [
            'phone' => $admin->phone,
            'password' => $password,
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin);
    }

    public function test_login_attempts_are_rate_limited(): void
    {
        RateLimiter::clear('09129999999|127.0.0.1');

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post(route('login.store'), [
                'phone' => '09129999999',
                'password' => 'wrong-password',
            ])->assertRedirect();
        }

        $this->post(route('login.store'), [
            'phone' => '09129999999',
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
