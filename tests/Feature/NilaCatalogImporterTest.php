<?php

namespace Tests\Feature;

use App\Integrations\Nila\Data\ExternalProductData;
use App\Integrations\Nila\Data\ExternalVariantData;
use App\Integrations\Nila\NilaCatalogImporter;
use App\Models\IntegrationMapping;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Product;
use App\Models\Media;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NilaCatalogImporterTest extends TestCase
{
    use RefreshDatabase;

    public function test_nila_import_is_idempotent_and_updates_existing_records(): void
    {
        $importer = app(NilaCatalogImporter::class);

        $first = $this->productData(stock: 8, price: 850_000);

        $product = $importer->import($first);

        $this->assertDatabaseCount('products', 1);
        $this->assertDatabaseCount('product_variants', 1);
        $this->assertDatabaseCount('integration_mappings', 2);

        $second = $this->productData(stock: 3, price: 920_000);

        $sameProduct = $importer->import($second);

        $this->assertSame($product->id, $sameProduct->id);
        $this->assertDatabaseCount('products', 1);
        $this->assertDatabaseCount('product_variants', 1);

        $this->assertDatabaseHas('product_variants', [
            'product_id' => $product->id,
            'sku' => 'JANAN-NILA-001',
            'price' => 920000,
            'stock' => 3,
        ]);
    }

    public function test_same_unmapped_sku_is_rejected_instead_of_hijacking_a_variant(): void
    {
        $existingProduct = Product::query()->create([
            'name' => 'Existing Product',
            'slug' => 'existing-product',
            'is_active' => true,
            'is_featured' => false,
            'sort_order' => 0,
        ]);

        $existingProduct->variants()->create([
            'sku' => 'JANAN-NILA-001',
            'price' => 500_000,
            'stock' => 2,
        ]);

        $this->expectException(\RuntimeException::class);

        app(NilaCatalogImporter::class)->import(
            $this->productData()
        );

        $this->assertDatabaseCount('integration_mappings', 0);
    }

    public function test_nila_import_never_touches_janan_managed_media(): void
    {
        $product = app(NilaCatalogImporter::class)->import($this->productData());

        $media = $product->media()->create([
            'collection' => 'gallery',
            'disk' => 'public',
            'path' => 'products/manual-image.webp',
            'original_name' => 'manual-image.webp',
            'mime_type' => 'image/webp',
            'alt_text' => 'تصویر دستی جانان',
            'sort_order' => 0,
            'metadata' => ['source' => 'admin'],
        ]);

        app(NilaCatalogImporter::class)->import(
            $this->productData(stock: 2, price: 990_000)
        );

        $this->assertDatabaseHas('media', [
            'id' => $media->id,
            'path' => 'products/manual-image.webp',
            'alt_text' => 'تصویر دستی جانان',
            'sort_order' => 0,
        ]);
        $this->assertSame(1, Media::query()->where('mediable_id', $product->id)->count());
    }
    public function test_nila_mappings_point_to_product_and_variant(): void
    {
        $product = app(NilaCatalogImporter::class)->import($this->productData());

        $this->assertTrue(
            IntegrationMapping::query()
                ->where('integration', 'nila')
                ->where('entity_type', Product::class)
                ->where('entity_id', $product->id)
                ->where('external_id', 'nila-product-001')
                ->exists()
        );

        $this->assertTrue(
            IntegrationMapping::query()
                ->where('integration', 'nila')
                ->where('entity_type', 'App\\Models\\ProductVariant')
                ->where('external_id', 'nila-variant-001')
                ->exists()
        );
    }

    public function test_nila_updates_do_not_overwrite_janan_managed_product_and_variant_fields(): void
    {
        $category = Category::query()->create([
            'name' => 'دسته دستی',
            'slug' => 'manual-category',
            'is_active' => true,
            'sort_order' => 0,
        ]);

        $brand = Brand::query()->create([
            'name' => 'برند دستی',
            'slug' => 'manual-brand',
            'is_active' => true,
        ]);

        $importer = app(NilaCatalogImporter::class);
        $product = $importer->import($this->productData());

        $product->update([
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'short_description' => 'توضیح دستی جانان',
            'description' => 'متن دستی جانان',
            'attributes' => ['fabric' => 'cotton'],
            'is_active' => true,
            'is_featured' => true,
            'sort_order' => 42,
        ]);

        $variant = $product->variants()->firstOrFail();
        $variant->update([
            'sale_price' => 700000,
            'is_active' => true,
            'sort_order' => 9,
        ]);

        $importer->import($this->productData(stock: 3, price: 920000));

        $product->refresh();
        $variant->refresh();

        $this->assertSame($category->id, $product->category_id);
        $this->assertSame($brand->id, $product->brand_id);
        $this->assertSame('توضیح دستی جانان', $product->short_description);
        $this->assertSame('متن دستی جانان', $product->description);
        $this->assertSame(['fabric' => 'cotton'], $product->attributes);
        $this->assertTrue($product->is_active);
        $this->assertTrue($product->is_featured);
        $this->assertSame(42, $product->sort_order);

        $this->assertSame('700000.00', $variant->sale_price);
        $this->assertTrue($variant->is_active);
        $this->assertSame(9, $variant->sort_order);
        $this->assertSame(920000.0, (float) $variant->price);
        $this->assertSame(3, $variant->stock);
    }

    private function productData(
        int $stock = 5,
        float $price = 800_000
    ): ExternalProductData {
        return new ExternalProductData(
            externalId: 'nila-product-001',
            name: 'Janan Nila Product',
            variants: [
                new ExternalVariantData(
                    externalId: 'nila-variant-001',
                    sku: 'JANAN-NILA-001',
                    price: $price,
                    stock: $stock,
                    size: 'M',
                    color: 'Black',
                ),
            ],
            metadata: ['source_version' => 'test'],
        );
    }
}
