<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Brand;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicSeoTest extends TestCase
{
    use RefreshDatabase;

    public function test_brand_destinations_are_catalog_filters_not_standalone_pages(): void
    {
        $brand = Brand::factory()->create([
            'is_active' => true,
        ]);

        $this->get(route('brands.index'))
            ->assertStatus(301)
            ->assertRedirect(route('products.index'));

        $this->get(route('brands.show', $brand))
            ->assertStatus(301)
            ->assertRedirect(route('products.index', ['brand' => $brand->slug]));

        $this->get(route('seo.sitemap.segment', ['type' => 'brands', 'page' => 1]))
            ->assertNotFound();

        $this->get(route('seo.sitemap'))
            ->assertOk()
            ->assertDontSee('/sitemap/brands/', false);
    }

    public function test_public_seo_endpoints_and_product_metadata_work(): void
    {
        $category = Category::create([
            'name' => 'ست',
            'slug' => 'sets',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'محصول SEO',
            'slug' => 'seo-product',
            'sku' => 'SEO-001',
            'description' => 'توضیح تست برای محصول.',
            'price' => 100000,
            'stock' => 5,
            'low_stock_threshold' => 2,
            'is_active' => true,
            'is_featured' => true,
        ]);

        $this->get('/robots.txt')
            ->assertOk()
            ->assertSee('Sitemap:');

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertSee('<sitemapindex', false)
            ->assertSee(route('seo.sitemap.segment', [
                'type' => 'products',
                'page' => 1,
            ]), false);

        $this->get(route('seo.sitemap.segment', [
            'type' => 'products',
            'page' => 1,
        ]))
            ->assertOk()
            ->assertSee(route('products.show', $product));

        $this->get(route('products.show', $product))
            ->assertOk()
            ->assertSee('name="description"', false)
            ->assertSee('rel="canonical"', false)
            ->assertSee('application/ld+json', false)
            ->assertSee($product->name);
    }
}
