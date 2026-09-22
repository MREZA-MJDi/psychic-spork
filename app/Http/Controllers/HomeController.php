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

        return view('welcome', [
            'categories' => $categories,
            'brands' => $brands,
            'products' => $products,
            'latestProduct' => $products->first(),
        ]);
    }
}
