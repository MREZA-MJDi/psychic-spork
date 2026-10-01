<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\HeroSlide;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Services\SeoService;
use Illuminate\Support\Facades\Cache;
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
                'primaryActiveVariant',
                'primaryGalleryMedia',
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

        $recentProducts = Product::query()
            ->active()
            ->with([
                'category:id,name',
                'brand:id,name',
                'primaryGalleryMedia',
                'primaryActiveVariant',
            ])
            ->latest('created_at')
            ->latest('id')
            ->limit(4)
            ->get();

        $signalFrom = now()
            ->startOfDay()
            ->subDays(29);

        $signalTo = now()->endOfDay();

        $popularSalesFilter = function ($query) use ($signalFrom, $signalTo) {
            $query->whereHas('order', function ($orderQuery) use ($signalFrom, $signalTo) {
                $orderQuery
                    ->whereBetween('placed_at', [$signalFrom, $signalTo])
                    ->whereNotIn(
                        'status',
                        Order::CANCEL_LIKE_STATUSES
                    )
                    ->where('payment_status', 'paid');
            });
        };

        $popularProducts = Product::query()
            ->active()
            ->whereHas('orderItems', $popularSalesFilter)
            ->with([
                'category:id,name',
                'brand:id,name',
                'galleryMedia',
                'variants',
            ])
            ->withSum(
                [
                    'orderItems as sales_quantity' => $popularSalesFilter,
                ],
                'quantity'
            )
            ->orderByDesc('sales_quantity')
            ->orderByDesc('id')
            ->limit(4)
            ->get();

        $heroVersion = Cache::remember(
            'store:home:hero:version',
            now()->addYear(),
            fn () => '1'
        );

        $heroSlides = Cache::remember(
            'store:home:hero:' . $heroVersion,
            now()->addMinutes(30),
            fn () => HeroSlide::query()
                ->active()
                ->with([
                    'product:id,name,slug,brand_id,category_id,short_description,description',
                    'product.brand:id,name',
                    'product.brand.logoMedia',
                    'product.category:id,name',
                    'product.primaryGalleryMedia',
                ])
                ->orderBy('sort_order')
                ->orderBy('id')
                ->limit(90)
                ->get()
                ->values()
                ->map(function (HeroSlide $slide, int $index): array {
                    $product = $slide->product;

                    $description = trim((string) (
                        $slide->description
                        ?: $product?->short_description
                        ?: $product?->description
                    ));

                    if ($description === '') {
                        $description = $product?->category?->name
                            ? 'منتخبی از دسته ' . $product->category->name . ' در جانان.'
                            : 'منتخبی از کالکشن جانان.';
                    }

                    return [
                        'number' => str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT),
                        'image' => $slide->resolved_image_url,
                        'title' => $slide->title ?: ($product?->name ?? 'کالکشن منتخب جانان'),
                        'description' => $description,
                        'brand' => $product?->brand?->name ?? 'JANAN',
                        'url' => $slide->link_url
                            ?: ($product ? route('products.show', $product) : route('products.index')),
                    ];
                })
                ->all()
        );

        return view('home.index', [
            'seo' => $seo->storeHome(),
            'categories' => $categories,
            'brands' => $brands,
            'products' => $products,
            'recentProducts' => $recentProducts,
            'popularProducts' => $popularProducts,
            'heroSlides' => $heroSlides,
        ]);
    }
}
