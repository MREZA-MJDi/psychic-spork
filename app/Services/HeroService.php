<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

final class HeroService
{
    public const MAX_SLIDES = 36;

    // Cache only request-independent slide data. URLs are kept relative so a request
    // arriving through an old proxy/port can never poison the shared hero cache.
    private const VERSION_KEY = 'store:home:hero:products:version:v4';
    private const CACHE_PREFIX = 'store:home:hero:products:v4:';
    private const CACHE_TTL_MINUTES = 30;

    public function slides(): array
    {
        $version = (string) Cache::remember(
            self::VERSION_KEY,
            now()->addYear(),
            static fn () => '1'
        );

        $slides = Cache::remember(
            self::CACHE_PREFIX . $version,
            now()->addMinutes(self::CACHE_TTL_MINUTES),
            fn () => $this->loadSlides()
        );

        if ($slides === []) {
            return [];
        }

        // Price and stock must not be cached for the full editorial cache TTL.
        // One bounded aggregate query for the selected products keeps them fresh.
        $metrics = ProductVariant::query()
            ->whereIn('product_id', array_column($slides, 'id'))
            ->where('is_active', true)
            ->select('product_id')
            ->selectRaw('MIN(COALESCE(sale_price, price)) as min_price')
            ->selectRaw('MAX(COALESCE(sale_price, price)) as max_price')
            ->selectRaw('SUM(stock) as stock_total')
            ->selectRaw("COUNT(DISTINCT NULLIF(TRIM(color), '')) as colors_count")
            ->selectRaw("COUNT(DISTINCT NULLIF(TRIM(size), '')) as sizes_count")
            ->groupBy('product_id')->get()->keyBy('product_id');

        return array_map(static function (array $slide) use ($metrics): array {
            $metric = $metrics->get($slide['id']);
            $min = (float) ($metric?->min_price ?? 0);
            $max = (float) ($metric?->max_price ?? 0);
            $slide['price'] = $min > 0
                ? ($max > $min ? 'از ' : '') . \App\Support\PersianNumber::format($min) . ' تومان'
                : null;
            $slide['availability'] = (int) ($metric?->stock_total ?? 0) > 0 ? 'موجود' : 'ناموجود';
            $slide['facts'] = array_values(array_filter([
                (int) ($metric?->colors_count ?? 0) > 0 ? \App\Support\PersianNumber::format($metric->colors_count) . ' رنگ' : null,
                (int) ($metric?->sizes_count ?? 0) > 0 ? \App\Support\PersianNumber::format($metric->sizes_count) . ' سایز' : null,
            ]));
            return $slide;
        }, $slides);
    }

    public function toggle(Product $product): bool
    {
        $isHero = Cache::lock('store:home:hero:toggle', 10)->block(
            5,
            function () use ($product): bool {
                return DB::transaction(function () use ($product): bool {
                    $current = Product::query()
                        ->whereKey($product->getKey())
                        ->lockForUpdate()
                        ->firstOrFail();

                    if (! $current->is_hero) {
                        if (! $current->is_active) {
                            throw new RuntimeException('hero_requires_active');
                        }

                        $heroCount = Product::query()
                            ->where('is_active', true)
                            ->where('is_hero', true)
                            ->lockForUpdate()
                            ->count();

                        if ($heroCount >= self::MAX_SLIDES) {
                            throw new RuntimeException('hero_limit');
                        }
                    }

                    $current->update([
                        'is_hero' => ! $current->is_hero,
                    ]);

                    return (bool) $current->is_hero;
                });
            }
        );

        $this->invalidate();

        return $isHero;
    }

    public function invalidate(): void
    {
        Cache::put(
            self::VERSION_KEY,
            (string) Str::uuid(),
            now()->addYear()
        );
    }

    private function loadSlides(): array
    {
        return Product::query()
            ->select([
                'id',
                'brand_id',
                'category_id',
                'name',
                'slug',
                'short_description',
                'description',
                'sort_order',
            ])
            ->where('is_active', true)
            ->where('is_hero', true)
            ->with([
                'brand:id,name',
                'brand.logoMedia',
                'category:id,name',
                'primaryGalleryMedia',
            ])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->limit(self::MAX_SLIDES)
            ->get()
            ->values()
            ->map(function (Product $product, int $index): array {
                $description = trim((string) (
                    $product->short_description ?: $product->description
                ));

                if ($description === '') {
                    $description = $product->category?->name
                        ? 'منتخبی از دسته ' . $product->category->name . ' در جانان.'
                        : 'منتخبی از کالکشن جانان.';
                }

                return [
                    'id' => $product->getKey(),
                    'number' => str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT),
                    'image' => $product->primaryGalleryMedia?->url
                        ?: $product->brand?->logoMedia?->url,
                    'title' => $product->name,
                    'description' => Str::limit(trim(strip_tags($description)), 120),
                    'category' => $product->category?->name,
                    'brand' => $product->brand?->name ?? 'جانان',
                    'url' => route('products.show', $product, false),
                ];
            })
            ->all();
    }
}
