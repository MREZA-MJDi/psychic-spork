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

    public function test_product_hero_selection_is_capped_at_thirty_six_products(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $products = Product::factory()->count(36)->create([
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

        $this->assertDatabaseCount('products', $products->count() + 1);
        $this->assertDatabaseHas('products', [
            'id' => $candidate->id,
            'is_hero' => false,
        ]);

        $this->assertSame(
            36,
            Product::query()->where('is_hero', true)->count()
        );

    public function test_inactive_product_cannot_be_added_to_homepage_hero(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $product = Product::factory()->create([
            'is_active' => false,
            'is_hero' => false,
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.products.hero.toggle', $product))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'is_hero' => false,
        ]);
    }

    public function test_homepage_can_render_thirty_six_hero_products(): void
    {
        Product::factory()->count(36)->create([
            'is_active' => true,
            'is_hero' => true,
        ]);

        $response = $this->get(route('home'));

        $response->assertOk()->assertViewHas('heroSlides', function (array $slides): bool {
            return count($slides) === 36
                && $slides[0]['number'] === '01'
                && $slides[35]['number'] === '36';
        });
    }
    }
}
