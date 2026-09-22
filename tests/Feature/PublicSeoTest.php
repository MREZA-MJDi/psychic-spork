<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicSeoTest extends TestCase
{
    use RefreshDatabase;

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
            ->assertSee(route('products.show', $product));

        $this->get(route('products.show', $product))
            ->assertOk()
            ->assertSee('<meta name="description"', false)
            ->assertSee('<link rel="canonical"', false)
            ->assertSee('application/ld+json', false)
            ->assertSee($product->name);
    }
}
