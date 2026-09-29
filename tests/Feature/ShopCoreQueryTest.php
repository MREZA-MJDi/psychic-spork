<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShopCoreQueryTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_catalog_does_not_lazy_load_variant_or_gallery_collections(): void
    {
        $category = Category::create([
            'name' => 'کالکشن تست',
            'slug' => 'catalog-query-test-' . uniqid(),
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $brand = Brand::create([
            'name' => 'برند تست',
            'slug' => 'catalog-brand-test-' . uniqid(),
            'is_active' => true,
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'name' => 'محصول کوئری تست',
            'slug' => 'catalog-product-test-' . uniqid(),
            'is_active' => true,
            'is_featured' => false,
            'sort_order' => 1,
        ]);

        ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'CATALOG-A-' . strtoupper(uniqid()),
            'size' => 'M',
            'price' => 100000,
            'stock' => 5,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'CATALOG-B-' . strtoupper(uniqid()),
            'size' => 'L',
            'price' => 110000,
            'stock' => 4,
            'is_active' => true,
            'sort_order' => 2,
        ]);

        Model::preventLazyLoading(true);

        try {
            $this->get(route('products.index'))
                ->assertOk()
                ->assertSee('محصول کوئری تست');
        } finally {
            Model::preventLazyLoading(false);
        }
    }

    public function test_product_can_store_a_generic_external_mapping(): void
    {
        $product = Product::create([
            'name' => 'محصول مپینگ تست',
            'slug' => 'integration-mapping-test-' . uniqid(),
            'is_active' => true,
            'is_featured' => false,
            'sort_order' => 1,
        ]);

        $mapping = $product->integrationMappings()->create([
            'integration' => 'nila',
            'external_id' => 'NILA-10001',
            'external_sku' => 'NILA-SKU-10001',
            'metadata' => [
                'source' => 'catalog-test',
            ],
        ]);

        $this->assertSame('nila', $mapping->integration);
        $this->assertSame(Product::class, $mapping->entity_type);
        $this->assertSame($product->id, $mapping->entity_id);
        $this->assertSame('NILA-10001', $mapping->external_id);
        $this->assertSame('NILA-SKU-10001', $mapping->external_sku);
    }
}
