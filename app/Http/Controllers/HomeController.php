<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Services\HeroService;
use App\Services\SeoService;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(SeoService $seo, HeroService $hero): View
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
                'activeVariants',
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
                'activeVariants',
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
                'primaryGalleryMedia',
                'primaryActiveVariant',
                'activeVariants',
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

        $heroSlides = $hero->slides();

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
