<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
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

        $products = (clone $productsQuery)
            ->featured()
            ->latest('updated_at')
            ->latest('id')
            ->take(8)
            ->get();

        if ($products->isEmpty()) {
            $products = $productsQuery
                ->latest('updated_at')
                ->latest('id')
                ->take(8)
                ->get();
        }

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
            'categories' => $categories,
            'brands' => $brands,
            'products' => $products,
            'latestProduct' => $products->first(),
            'heroSlides' => $heroSlides,
            'homeTagline' => 'کالکشن‌های منتخب جانان با محصولات واقعی فروشگاه، برای انتخابی دقیق‌تر و شخصی‌تر.',
        ]);
    }
}
