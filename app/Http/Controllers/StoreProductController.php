<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductFilterRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Services\SeoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StoreProductController extends Controller
{
    public function index(
        StoreProductFilterRequest $request,
        SeoService $seo
    ): View {
        $filters = $request->validated();

        $products = Product::query()
            ->active()
            ->with([
                'category:id,name,slug',
                'brand:id,name,slug',
                'primaryActiveVariant',
                'primaryGalleryMedia',
            ])
            ->when(
                ! empty($filters['q']),
                fn ($query) => $query->where(function ($query) use ($filters) {
                    $term = '%' . $filters['q'] . '%';

                    $query
                        ->where('name', 'like', $term)
                        ->orWhere('short_description', 'like', $term)
                        ->orWhere('description', 'like', $term)
                        ->orWhereHas(
                            'variants',
                            fn ($variant) => $variant->where(
                                'sku',
                                'like',
                                $term
                            )
                        )
                        ->orWhereHas(
                            'category',
                            fn ($category) => $category
                                ->where('name', 'like', $term)
                                ->where('is_active', true)
                        )
                        ->orWhereHas(
                            'brand',
                            fn ($brand) => $brand
                                ->where('name', 'like', $term)
                                ->where('is_active', true)
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
            ->when($filters['sort'] ?? 'latest', function ($query, $sort) {
                match ($sort) {
                    'price_asc' => $query
                        ->orderByRaw(
                            '(select min(pv.price) from product_variants pv where pv.product_id = products.id and pv.is_active = 1) asc'
                        )
                        ->orderByDesc('products.id'),
                    'price_desc' => $query
                        ->orderByRaw(
                            '(select max(pv.price) from product_variants pv where pv.product_id = products.id and pv.is_active = 1) desc'
                        )
                        ->orderByDesc('products.id'),
                    'name' => $query->orderBy('name')->orderByDesc('id'),
                    default => $query->latest('updated_at')->latest('id'),
                };
            })
            ->paginate(12)
            ->withQueryString();

        return view('products.index', [
            'seo' => $seo->page(
                'محصولات — ' . config('app.store_name', 'Janan'),
                'مشاهده، جستجو و خرید محصولات فعال فروشگاه جانان.',
                route('products.index')
            ),
            'products' => $products,
            'categories' => Category::query()
                ->active()
                ->select(['id', 'name', 'slug'])
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
            'brands' => Brand::query()
                ->active()
                ->select(['id', 'name', 'slug'])
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function suggestions(Request $request): JsonResponse
    {
        $term = trim((string) $request->query('q', ''));

        if (mb_strlen($term) < 2) {
            return response()->json(['items' => []]);
        }

        $like = '%' . $term . '%';

        $items = Product::query()
            ->active()
            ->with([
                'category:id,name',
                'brand:id,name',
                'primaryGalleryMedia',
                'primaryActiveVariant',
            ])
            ->where(function ($query) use ($like) {
                $query
                    ->where('name', 'like', $like)
                    ->orWhere('short_description', 'like', $like)
                    ->orWhereHas(
                        'variants',
                        fn ($variant) => $variant->where('sku', 'like', $like)
                    )
                    ->orWhereHas(
                        'category',
                        fn ($category) => $category
                            ->where('name', 'like', $like)
                            ->where('is_active', true)
                    )
                    ->orWhereHas(
                        'brand',
                        fn ($brand) => $brand
                            ->where('name', 'like', $like)
                            ->where('is_active', true)
                    );
            })
            ->latest('updated_at')
            ->latest('id')
            ->limit(6)
            ->get()
            ->map(function (Product $product): array {
                $variant = $product->primaryActiveVariant;

                return [
                    'name' => $product->name,
                    'brand' => $product->brand?->name,
                    'category' => $product->category?->name,
                    'image' => $product->primaryGalleryMedia?->url,
                    'price' => $variant?->effective_price,
                    'url' => route('products.show', $product),
                ];
            })
            ->values();

        return response()->json([
            'items' => $items,
        ]);
    }

    public function show(
        Product $product,
        SeoService $seo
    ): View {
        abort_unless($product->is_active, 404);

        $product->load([
            'category',
            'brand',
            'variants',
            'activeVariants',
            'galleryMedia',
        ]);

        $relatedProducts = Product::query()
            ->active()
            ->with([
                'category:id,name,slug',
                'brand:id,name,slug',
                'primaryActiveVariant',
                'primaryGalleryMedia',
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
                    'category:id,name,slug',
                    'brand:id,name,slug',
                    'primaryActiveVariant',
                    'primaryGalleryMedia',
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
            'seo' => $seo->product($product),
            'product' => $product,
            'relatedProducts' => $relatedProducts,
        ]);
    }
}
