<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WholesaleProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminWholesaleWorkspaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_workspace_lists_profiles_and_only_renders_selected_editor(): void
    {
        $this->withoutVite();
        $admin = User::factory()->create(['is_admin' => true]);
        $first = User::factory()->create(['name' => 'Wholesale Alpha']);
        $second = User::factory()->create(['name' => 'Wholesale Beta']);
        $profile = WholesaleProfile::create(['user_id' => $first->id, 'status' => 'approved', 'minimum_order_amount' => 3000000, 'minimum_order_quantity' => 6]);
        WholesaleProfile::create(['user_id' => $second->id, 'status' => 'pending']);

        $this->actingAs($first)->get(route('admin.wholesale.index'))->assertForbidden();
        $this->actingAs($admin)->get(route('admin.wholesale.index'))->assertOk()
            ->assertSee('خرید عمده آنلاین برای همه مشتریان باز است')
            ->assertSee('Wholesale Alpha')->assertSee('Wholesale Beta')
            ->assertDontSee('name="minimum_order_amount"', false);

        $this->get(route('admin.wholesale.index', ['q' => 'Wholesale Alpha', 'status' => 'approved', 'edit' => $profile->id]))
            ->assertOk()->assertViewHas('editing', fn ($editing) => $editing->id === $profile->id)
            ->assertViewHas('profiles', fn ($profiles) => $profiles->pluck('id')->all() === [$profile->id])
            ->assertSee('name="minimum_order_amount"', false)
            ->assertSee('خرید عمده آنلاین یا مجوز چک مشتری را مسدود نمی‌کند');

        $this->patch(route('admin.customers.wholesale.terms', $first), [
            'minimum_order_amount' => '۴٬۰۰۰٬۰۰۰',
            'minimum_order_quantity' => 8,
            'note' => 'Updated wholesale terms',
        ])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertDatabaseHas('wholesale_profiles', [
            'id' => $profile->id,
            'minimum_order_amount' => 4000000,
            'minimum_order_quantity' => 8,
            'admin_note' => 'Updated wholesale terms',
        ]);
    }
}
