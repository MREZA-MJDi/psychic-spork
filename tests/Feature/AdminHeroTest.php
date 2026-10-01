<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminHeroTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_toggle_a_product_in_the_homepage_hero(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $product = Product::factory()->create([
            'is_active' => true,
            'is_hero' => false,
        ]);

        ProductVariant::factory()->create([
            'product_id' => $product->id,
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.products.hero.toggle', $product))
            ->assertRedirect();

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'is_hero' => true,
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee($product->name);
    }

    public function test_product_hero_selection_is_capped_at_forty_eight_products(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $products = Product::factory()->count(48)->create([
            'is_active' => true,
            'is_hero' => true,
        ]);

        $candidate = Product::factory()->create([
            'is_active' => true,
            'is_hero' => false,
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.products.hero.toggle', $candidate))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseCount('products', 49);
        $this->assertDatabaseHas('products', [
            'id' => $candidate->id,
            'is_hero' => false,
        ]);

        $this->assertSame(
            48,
            Product::query()->where('is_hero', true)->count()
        );
    }
}
