<?php

namespace Tests\Feature;

use App\Integrations\Nila\Data\ExternalProductData;
use App\Integrations\Nila\Data\ExternalVariantData;
use App\Integrations\Nila\NilaCatalogImporter;
use App\Models\IntegrationMapping;
use App\Models\Product;
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
