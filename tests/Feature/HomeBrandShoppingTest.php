<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeBrandShoppingTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_brand_discovery_is_bounded_and_only_links_to_active_catalogs(): void
    {
        $this->withoutVite();
        $featured = Brand::factory()->create(['name' => 'QA Brand Featured', 'slug' => 'qa-brand-featured', 'is_active' => true]);
        Product::factory()->count(3)->create(['brand_id' => $featured->id, 'is_active' => true]);
        $empty = Brand::factory()->create(['name' => 'QA Brand Empty', 'slug' => 'qa-brand-empty', 'is_active' => true]);
        Product::factory()->create(['brand_id' => $empty->id, 'is_active' => false]);
        $disabled = Brand::factory()->create(['name' => 'QA Brand Disabled', 'slug' => 'qa-brand-disabled', 'is_active' => false]);
        Product::factory()->create(['brand_id' => $disabled->id, 'is_active' => true]);

        foreach (range(1, 9) as $index) {
            $brand = Brand::factory()->create(['is_active' => true]);
            Product::factory()->create(['brand_id' => $brand->id, 'is_active' => true]);
        }

        $response = $this->get(route('home'))->assertOk()
            ->assertViewHas('brands', fn ($brands) => $brands->count() === 8
                && $brands->first()->id === $featured->id
                && (int) $brands->first()->active_products_count === 3
                && ! $brands->contains('id', $empty->id)
                && ! $brands->contains('id', $disabled->id))
            ->assertSee('خرید بر اساس برند')
            ->assertSee(route('products.index', ['brand' => $featured->slug]))
            ->assertDontSee('href="' . route('brands.index') . '"', false);

        $this->assertSame(8, substr_count($response->getContent(), 'class="home-brand-shop__card"'));
        $this->get(route('products.index', ['brand' => $featured->slug]))
            ->assertOk()->assertViewHas('products', fn ($products) => $products->total() === 3);
    }
}
