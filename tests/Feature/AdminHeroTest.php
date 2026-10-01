<?php

namespace Tests\Feature;

use App\Models\HeroSlide;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminHeroTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_select_products_for_homepage_hero(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $products = collect(range(1, 3))->map(function (int $index): Product {
            $product = Product::factory()->create([
                'name' => 'Hero Product ' . $index,
                'is_active' => true,
            ]);

            ProductVariant::factory()->create([
                'product_id' => $product->id,
                'is_active' => true,
            ]);

            return $product;
        });

        $this->actingAs($admin)
            ->put(route('admin.hero.update'), [
                'product_ids' => $products->pluck('id')->all(),
            ])
            ->assertRedirect();

        $this->assertDatabaseCount('hero_slides', 3);

        $this->assertDatabaseHas('hero_slides', [
            'product_id' => $products[0]->id,
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Hero Product 1')
            ->assertSee('Hero Product 3');
    }

    public function test_hero_selection_is_capped_at_six_products(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $products = Product::factory()->count(8)->create([
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->put(route('admin.hero.update'), [
                'product_ids' => $products->pluck('id')->all(),
            ])
            ->assertRedirect();

        $this->assertDatabaseCount('hero_slides', 6);
    }
}
