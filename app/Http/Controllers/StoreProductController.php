<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductFilterRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\View\View;

class StoreProductController extends Controller
{
    public function index(
        StoreProductFilterRequest $request
    ): View {
        $filters = $request->validated();

        $products = Product::query()
            ->active()
            ->with([
                'category',
                'brand',
                'variants',
                'galleryMedia',
            ])
            ->when(
                ! empty($filters['q']),
                fn ($query) => $query->where(function ($query) use ($filters) {
                    $query
                        ->where(
                            'name',
                            'like',
                            '%' . $filters['q'] . '%'
                        )
                        ->orWhereHas(
                            'variants',
                            fn ($variant) => $variant->where(
                                'sku',
                                'like',
                                '%' . $filters['q'] . '%'
                            )
                        );
                })
            )
            ->when(
                ! empty($filters['category']),
                fn ($query) => $query->whereHas(
                    'category',
                    fn ($category) => $category
                        ->where('slug', $filters['category'])
                        ->where('is_active', true)
                )
            )
            ->when(
                ! empty($filters['brand']),
                fn ($query) => $query->whereHas(
                    'brand',
                    fn ($brand) => $brand
                        ->where('slug', $filters['brand'])
                        ->where('is_active', true)
                )
            )
            ->latest('updated_at')
            ->latest('id')
            ->paginate(12)
            ->withQueryString();

        return view('products.index', [
            'products' => $products,

            'categories' => Category::query()
                ->active()
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),

            'brands' => Brand::query()
                ->active()
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function show(Product $product): View
    {
        abort_unless($product->is_active, 404);

        $product->load([
            'category',
            'brand',
            'variants',
            'galleryMedia',
        ]);

        $relatedProducts = Product::query()
            ->active()
            ->with([
                'category',
                'brand',
                'variants',
                'galleryMedia',
            ])
            ->where('id', '!=', $product->id)
            ->when(
                $product->category_id,
                fn ($query) => $query->where(
                    'category_id',
                    $product->category_id
                )
            )
            ->latest('updated_at')
            ->latest('id')
            ->take(4)
            ->get();

        if ($relatedProducts->count() < 4) {
            $remaining = 4 - $relatedProducts->count();

            $fallbackProducts = Product::query()
                ->active()
                ->with([
                    'category',
                    'brand',
                    'variants',
                    'galleryMedia',
                ])
                ->where('id', '!=', $product->id)
                ->whereNotIn(
                    'id',
                    $relatedProducts->pluck('id')
                )
                ->latest('updated_at')
                ->latest('id')
                ->take($remaining)
                ->get();

            $relatedProducts = $relatedProducts
                ->concat($fallbackProducts)
                ->values();
        }

        return view('products.show', [
            'product' => $product,
            'relatedProducts' => $relatedProducts,
        ]);
    }
}
