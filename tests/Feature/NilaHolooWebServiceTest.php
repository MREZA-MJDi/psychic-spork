<?php

namespace Tests\Feature;

use App\Integrations\Nila\Data\ExternalProductData;
use App\Integrations\Nila\Data\ExternalVariantData;
use App\Integrations\Nila\HolooNilaCatalogSource;
use App\Integrations\Nila\NilaCatalogImporter;
use App\Models\Category;
use App\Models\IntegrationMapping;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class NilaHolooWebServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'nila.enabled' => true,
            'nila.base_url' => 'http://holoo.test/TncHoloo/api',
            'nila.username' => 'test-user',
            'nila.password' => 'secret-password',
            'nila.database' => 'HolooTest',
            'nila.timeout' => 5,
            'nila.page_size' => 2,
            'nila.max_pages' => 10,
            'nila.price_field' => 'SellPrice',
            'nila.price_multiplier' => 1,
            'nila.active_by_default' => false,
        ]);
    }

    public function test_holoo_login_uses_documented_payload_and_bearer_token_for_catalog_requests(): void
    {
        $this->fakeLoginAndCatalog([
            $this->holooProduct('holoo-product-1', '1001', 'Sample product', '1250.50', '6'),
        ]);

        $source = app(HolooNilaCatalogSource::class);
        $products = iterator_to_array($source->fetchProducts(), false);

        $this->assertCount(1, $products);

        Http::assertSent(function (ClientRequest $request): bool {
            if (str_ends_with($request->url(), '/Login')) {
                $body = $request->data();

                return $request->method() === 'POST'
                    && ($body['userinfo']['username'] ?? null) === 'test-user'
                    && ($body['userinfo']['dbname'] ?? null) === 'HolooTest'
                    && ($body['userinfo']['userpass'] ?? null) === base64_encode('secret-password');
            }

            return false;
        });

        Http::assertSent(function (ClientRequest $request): bool {
            return str_contains($request->url(), '/Product/1/2')
                && $request->hasHeader('Authorization', 'Bearer test-token')
                && str_contains($request->url(), 'getimage=false')
                && str_contains($request->url(), 'fa=false');
        });

        $this->assertSame('holoo-product-1', $products[0]->externalId);
        $this->assertSame('1001', $products[0]->variants[0]->sku);
        $this->assertSame(1250.5, $products[0]->variants[0]->price);
        $this->assertSame(6, $products[0]->variants[0]->stock);
    }

    public function test_catalog_sync_maps_groups_and_imports_provider_owned_product_values(): void
    {
        $this->fakeLoginAndCatalog([
            $this->holooProduct('holoo-product-1', '1001', 'بلوز نمونه', '1250.50', '6.8'),
            $this->holooProduct('holoo-product-2', '1002', 'شلوار نمونه', '2200', '3'),
            $this->holooProduct('holoo-product-3', '1003', 'مانتو نمونه', '3500', '1'),
        ]);

        $source = app(HolooNilaCatalogSource::class);
        $groupCounts = $source->syncCategories();

        $this->assertSame(['main_groups' => 1, 'side_groups' => 1], $groupCounts);
        $mainCategory = Category::query()->where('name', 'پوشاک')->firstOrFail();
        $sideCategory = Category::query()->where('name', 'بلوز')->firstOrFail();
        $this->assertSame($mainCategory->id, $sideCategory->parent_id);

        $products = iterator_to_array($source->fetchProducts(), false);
        $this->assertCount(3, $products);
        $this->assertSame($sideCategory->id, $products[0]->categoryId);

        $product = app(NilaCatalogImporter::class)->import($products[0]);
        $variant = $product->variants->firstOrFail();

        $this->assertSame('بلوز نمونه', $product->name);
        $this->assertFalse($product->is_active, 'New products should be hidden until the admin reviews them.');
        $this->assertSame($sideCategory->id, $product->category_id);
        $this->assertSame('1001', $variant->sku);
        $this->assertSame(1250.5, (float) $variant->price);
        $this->assertSame(6, $variant->stock, 'Fractional stock should be floored for the integer local schema.');
        $this->assertDatabaseCount('integration_mappings', 4);
        $this->assertDatabaseHas('integration_mappings', [
            'integration' => 'nila',
            'entity_type' => Product::class,
            'entity_id' => $product->id,
            'external_id' => 'holoo-product-1',
        ]);
    }

    public function test_reimport_preserves_store_managed_fields_holoo_does_not_supply(): void
    {
        $this->fakeLoginAndCatalog([
            $this->holooProduct('holoo-product-1', '1001', 'بلوز نمونه', '1250.50', '6'),
        ]);

        $source = app(HolooNilaCatalogSource::class);
        $product = app(NilaCatalogImporter::class)->import(
            iterator_to_array($source->fetchProducts(), false)[0]
        );

        $product->update([
            'short_description' => 'شرح کوتاه دستی',
            'description' => 'شرح کامل دستی',
            'is_active' => true,
            'is_featured' => true,
            'sort_order' => 7,
        ]);
        $variant = $product->variants()->firstOrFail();
        $variant->update([
            'sale_price' => 990,
            'size' => 'XL',
            'color' => 'زرشکی',
            'is_active' => false,
            'sort_order' => 9,
        ]);

        app(NilaCatalogImporter::class)->import(new ExternalProductData(
            externalId: 'holoo-product-1',
            name: 'بلوز نمونه - به‌روزرسانی',
            variants: [
                new ExternalVariantData(
                    externalId: 'holoo-product-1',
                    sku: '1001',
                    price: 1300,
                    stock: 2,
                ),
            ],
            // No description/category/visibility/variant fields: Holoo doesn't
            // provide these in its documented Product response.
        ));

        $product->refresh();
        $variant->refresh();

        $this->assertSame('بلوز نمونه - به‌روزرسانی', $product->name);
        $this->assertSame('شرح کوتاه دستی', $product->short_description);
        $this->assertSame('شرح کامل دستی', $product->description);
        $this->assertTrue($product->is_active);
        $this->assertTrue($product->is_featured);
        $this->assertSame(7, $product->sort_order);

        $this->assertSame(1300.0, (float) $variant->price);
        $this->assertSame(2, $variant->stock);
        $this->assertSame(990.0, (float) $variant->sale_price);
        $this->assertSame('XL', $variant->size);
        $this->assertSame('زرشکی', $variant->color);
        $this->assertFalse($variant->is_active);
        $this->assertSame(9, $variant->sort_order);
    }

    public function test_dry_run_does_not_write_categories_products_or_mappings(): void
    {
        $this->fakeLoginAndCatalog([
            $this->holooProduct('holoo-product-1', '1001', 'Sample product', '1250.50', '6'),
        ]);

        $exitCode = Artisan::call('nila:sync-products', [
            '--dry-run' => true,
            '--limit' => 1,
        ]);

        $this->assertSame(0, $exitCode, Artisan::output());
        $this->assertDatabaseCount('categories', 0);
        $this->assertDatabaseCount('products', 0);
        $this->assertDatabaseCount('product_variants', 0);
        $this->assertDatabaseCount('integration_mappings', 0);
        $this->assertStringContainsString('no catalog rows were written', Artisan::output());
    }

    private function fakeLoginAndCatalog(array $products): void
    {
        Http::fake([
            '*holoo.test/TncHoloo/api/Login' => Http::response([
                'Login' => [
                    'State' => 'True',
                    'Token' => 'Bearer test-token',
                    'ErrorCode' => 0,
                    'Error' => '',
                ],
            ], 200),
            '*holoo.test/TncHoloo/api/MainGroup' => Http::response([
                'mainGroup' => [[
                    'Name' => 'پوشاک',
                    'ErpCode' => 'main-erp-code',
                ]],
            ], 200),
            '*holoo.test/TncHoloo/api/SideGroup' => Http::response([
                'sideGroup' => [[
                    'Name' => 'بلوز',
                    'ErpCode' => 'side-erp-code',
                    'MainGroupName' => 'پوشاک',
                    'MainErpCode' => 'main-erp-code',
                ]],
            ], 200),
            '*holoo.test/TncHoloo/api/Product/1/2*' => Http::response([
                'product' => array_slice($products, 0, 2),
            ], 200),
            '*holoo.test/TncHoloo/api/Product/2/2*' => Http::response([
                'product' => array_slice($products, 2, 2),
            ], 200),
            '*holoo.test/TncHoloo/api/Product/3/2*' => Http::response([
                'product' => [],
            ], 200),
        ]);
    }

    private function holooProduct(
        string $erpCode,
        string $code,
        string $name,
        string $sellPrice,
        string $stock
    ): array {
        return [
            'ErpCode' => $erpCode,
            'Code' => $code,
            'Name' => $name,
            'Few' => $stock,
            'SellPrice' => $sellPrice,
            'SellPrice2' => '0',
            'MainGroupName' => 'پوشاک',
            'MainGroupErpCode' => 'main-erp-code',
            'SideGroupName' => 'بلوز',
            'SideGroupErpCode' => 'side-erp-code',
        ];
    }
}
