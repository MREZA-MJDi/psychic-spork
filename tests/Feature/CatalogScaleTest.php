<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('catalog-scale')]
class CatalogScaleTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_metrics_and_price_sort_scale_to_five_thousand_products_and_fifty_thousand_variants(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            $this->markTestSkipped('The 5,000 / 50,000 scale benchmark runs on the MySQL CI service.');
        }

        $this->seedCatalog();

        $this->assertSame(5000, Product::query()->count());
        $this->assertSame(50000, ProductVariant::query()->count());

        $measurePage = function (int $perPage): array {
            DB::flushQueryLog();
            DB::enableQueryLog();

            $memoryBefore = memory_get_usage();
            $startedAt = hrtime(true);

            $page = Product::query()
                ->select([
                    'id',
                    'category_id',
                    'brand_id',
                    'name',
                    'slug',
                    'short_description',
                    'updated_at',
                ])
                ->active()
                ->withCatalogMetrics()
                ->with([
                    'category:id,name,slug',
                    'brand:id,name,slug',
                    'primaryActiveVariant' => fn ($query) => $query->select([
                        'product_variants.id',
                        'product_variants.product_id',
                        'product_variants.sku',
                        'product_variants.price',
                        'product_variants.sale_price',
                        'product_variants.stock',
                        'product_variants.low_stock_threshold',
                        'product_variants.is_active',
                        'product_variants.sort_order',
                    ]),
                    'primaryGalleryMedia' => fn ($query) => $query->select([
                        'media.id',
                        'media.mediable_id',
                        'media.mediable_type',
                        'media.collection',
                        'media.path',
                        'media.sort_order',
                    ]),
                ])
                ->orderByEffectivePrice('asc')
                ->paginate($perPage);

            foreach ($page->items() as $product) {
                // Exercise exactly the relation/aggregate access used by cards.
                $product->category?->name;
                $product->brand?->name;
                $product->primaryActiveVariant?->effective_price;
                $product->primaryGalleryMedia?->url;
                $product->getAttribute('active_variants_count');
                $product->getAttribute('active_variant_colors_count');
                $product->getAttribute('active_variant_sizes_count');
                $product->getAttribute('active_stock_total');
                $product->getAttribute('minimum_effective_price');
                $product->getAttribute('maximum_effective_price');
            }

            $elapsedMs = (hrtime(true) - $startedAt) / 1_000_000;
            $queryCount = count(DB::getQueryLog());
            $memoryDelta = max(0, memory_get_usage() - $memoryBefore);

            DB::disableQueryLog();

            return [
                'page' => $page,
                'elapsed_ms' => $elapsedMs,
                'query_count' => $queryCount,
                'memory_delta' => $memoryDelta,
            ];
        };

        $firstPage = $measurePage(24);
        $largerPage = $measurePage(48);

        $this->assertCount(24, $firstPage['page']->items());
        $this->assertCount(48, $largerPage['page']->items());
        $this->assertLessThanOrEqual(8, $firstPage['query_count']);
        $this->assertSame(
            $firstPage['query_count'],
            $largerPage['query_count'],
            'The catalog query count must not grow with the number of rendered cards.'
        );
        $this->assertLessThan(
            15000,
            $firstPage['elapsed_ms'],
            'The 24-card catalog query exceeded the 15-second CI budget.'
        );
        $this->assertLessThan(
            64 * 1024 * 1024,
            $largerPage['memory_delta'],
            'Hydrating a 48-card catalog page used more than 64 MiB of additional memory.'
        );

        $first = $firstPage['page']->items()[0];
        $this->assertSame('Scale Product 0000', $first->name);
        $this->assertSame(10, (int) $first->active_variants_count);
        $this->assertSame(20, (int) $first->active_stock_total);
        $this->assertEquals(100000, (float) $first->minimum_effective_price);
        $this->assertEquals(109000, (float) $first->maximum_effective_price);

        fwrite(STDOUT, sprintf(
            "CATALOG_SCALE products=5000 variants=50000 page24_queries=%d page24_ms=%.1f page48_queries=%d page48_ms=%.1f page48_memory_delta=%d bytes\n",
            $firstPage['query_count'],
            $firstPage['elapsed_ms'],
            $largerPage['query_count'],
            $largerPage['elapsed_ms'],
            $largerPage['memory_delta']
        ));
    }

    private function seedCatalog(): void
    {
        $category = Category::factory()->create([
            'name' => 'Scale category',
            'slug' => 'scale-category',
            'is_active' => true,
        ]);
        $brand = Brand::factory()->create([
            'name' => 'Scale brand',
            'slug' => 'scale-brand',
            'is_active' => true,
        ]);

        $now = now();
        $firstProduct = Product::factory()->create([
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'name' => 'Scale Product 0000',
            'slug' => 'scale-product-0000',
            'is_active' => true,
            'sort_order' => 0,
        ]);

        $productTemplate = $firstProduct->getAttributes();
        unset($productTemplate['id']);
        $productRows = [];

        for ($i = 1; $i < 5000; $i++) {
            $number = str_pad((string) $i, 4, '0', STR_PAD_LEFT);
            $productRows[] = array_merge($productTemplate, [
                'name' => 'Scale Product ' . $number,
                'slug' => 'scale-product-' . $number,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            if (count($productRows) === 500) {
                DB::table('products')->insert($productRows);
                $productRows = [];
            }
        }

        if ($productRows !== []) {
            DB::table('products')->insert($productRows);
        }

        $productIds = Product::query()->orderBy('id')->pluck('id')->all();
        $this->assertCount(5000, $productIds);

        $variantTemplate = ProductVariant::factory()->make([
            'product_id' => $productIds[0],
            'sku' => 'SCALE-000000',
            'price' => 100000,
            'sale_price' => null,
            'wholesale_price' => null,
            'stock' => 2,
            'color' => 'Color 0',
            'size' => 'M',
            'is_active' => true,
            'sort_order' => 0,
        ])->getAttributes();
        unset($variantTemplate['id']);

        $variantRows = [];

        for ($i = 0; $i < 50000; $i++) {
            $productIndex = intdiv($i, 10);
            $variantNumber = $i % 10;
            $variantRows[] = array_merge($variantTemplate, [
                'product_id' => $productIds[$productIndex],
                'sku' => 'SCALE-' . str_pad((string) $i, 6, '0', STR_PAD_LEFT),
                'price' => 100000 + ($productIndex * 10000) + ($variantNumber * 1000),
                'sale_price' => null,
                'wholesale_price' => null,
                'stock' => 2,
                'color' => 'Color ' . ($variantNumber % 3),
                'size' => 'Size ' . ($variantNumber % 4),
                'is_active' => true,
                'sort_order' => $variantNumber,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            if (count($variantRows) === 1000) {
                DB::table('product_variants')->insert($variantRows);
                $variantRows = [];
            }
        }

        if ($variantRows !== []) {
            DB::table('product_variants')->insert($variantRows);
        }
    }
}
