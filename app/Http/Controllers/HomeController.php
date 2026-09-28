<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Services\SeoService;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(SeoService $seo): View
    {
        $categories = Category::query()
            ->active()
            ->with('coverMedia')
            ->withCount([
                'products as active_products_count' => fn ($query) =>
                $query->where('is_active', true),
            ])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->take(8)
            ->get();

        $brands = Brand::query()
            ->active()
            ->with('logoMedia')
            ->withCount([
                'products as active_products_count' => fn ($query) =>
                $query->where('is_active', true),
            ])
            ->orderBy('name')
            ->take(8)
            ->get();

        $productsQuery = Product::query()
            ->active()
            ->with([
                'category',
                'brand',
                'variants',
                'galleryMedia',
            ]);

        $featuredProducts = (clone $productsQuery)
            ->featured()
            ->latest('updated_at')
            ->latest('id')
            ->take(12)
            ->get();

        $products = $featuredProducts;

        if ($products->count() < 12) {
            $remaining = 12 - $products->count();

            $latestProducts = (clone $productsQuery)
                ->when(
                    $products->isNotEmpty(),
                    fn ($query) => $query->whereNotIn(
                        'id',
                        $products->pluck('id')
                    )
                )
                ->latest('updated_at')
                ->latest('id')
                ->take($remaining)
                ->get();

            $products = $products
                ->concat($latestProducts)
                ->values();
        }

        /*
        |--------------------------------------------------------------------------
        | Home signals
        |--------------------------------------------------------------------------
        |
        | These values mirror the catalog signals used in Admin Dashboard:
        | recent catalog additions + paid, non-cancelled top sellers.
        | They are filtered to active products before reaching the storefront.
        |--------------------------------------------------------------------------
        */

        $recentProducts = Product::query()
            ->active()
            ->with([
                'category:id,name',
                'brand:id,name',
                'galleryMedia',
                'variants',
            ])
            ->latest('created_at')
            ->latest('id')
            ->limit(4)
            ->get();

        $popularProducts = Product::query()
            ->active()
            ->with([
                'category:id,name',
                'brand:id,name',
                'galleryMedia',
                'variants',
            ])
            ->withSum(
                [
                    'orderItems as sales_quantity' => function ($query) {
                        $query->whereHas('order', function ($orderQuery) {
                            $orderQuery
                                ->whereNotIn(
                                    'status',
                                    Order::CANCEL_LIKE_STATUSES
                                )
                                ->where('payment_status', 'paid');
                        });
                    },
                ],
                'quantity'
            )
            ->having('sales_quantity', '>', 0)
            ->orderByDesc('sales_quantity')
            ->orderByDesc('id')
            ->limit(4)
            ->get();

        $heroProducts = Product::query()
            ->active()
            ->with(['brand.logoMedia', 'galleryMedia', 'variants'])
            ->inRandomOrder()
            ->take(4)
            ->get();

        $heroSlides = $heroProducts
            ->values()
            ->map(function (Product $product, int $index): array {
                $productImage = $product->galleryMedia->first()?->url;
                $brandImage = $product->brand?->logoMedia?->url;

                return [
                    'image' => $productImage ?: $brandImage,
                    'title' => $product->name,
                    'brand' => $product->brand?->name ?? 'JANAN',
                    'url' => route('products.show', $product),
                    'index' => $index,
                ];
            })
            ->all();

        return view('home.index', [
            'seo' => $seo->storeHome(),
            'categories' => $categories,
            'brands' => $brands,
            'products' => $products,
            'latestProduct' => $products->first(),
            'recentProducts' => $recentProducts,
            'popularProducts' => $popularProducts,
            'heroSlides' => $heroSlides,
            'homeTagline' => 'کالکشن‌های منتخب جانان با محصولات واقعی فروشگاه، برای انتخابی دقیق‌تر و شخصی‌تر.',
        ]);
    }
}
