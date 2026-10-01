<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductFilterRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
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

        $sort = $filters['sort'] ?? 'newest';
        $perPage = (int) ($filters['per_page'] ?? 12);

        $priceSubquery = ProductVariant::query()
            ->selectRaw('COALESCE(sale_price, price)')
            ->whereColumn('product_id', 'products.id')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->limit(1);

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
            ->when($sort === 'oldest', fn ($query) =>
                $query->orderBy('updated_at')->orderBy('id')
            )
            ->when($sort === 'price_asc', fn ($query) =>
                $query->orderBy($priceSubquery, 'asc')->orderBy('id')
            )
            ->when($sort === 'price_desc', fn ($query) =>
                $query->orderBy($priceSubquery, 'desc')->orderBy('id')
            )
            ->when($sort === 'name_asc', fn ($query) =>
                $query->orderBy('name')->orderBy('id')
            )
            ->when($sort === 'name_desc', fn ($query) =>
                $query->orderByDesc('name')->orderBy('id')
            )
            ->when($sort === 'newest', fn ($query) =>
                $query->latest('updated_at')->latest('id')
            )
            ->paginate($perPage)
            ->withQueryString();

        return view('products.index', [
            'seo' => $seo->page(
                'محصولات — ' . config('app.store_name', 'Janan'),
                'مشاهده، جستجو و خرید محصولات فعال فروشگاه جانان.',
                route('products.index')
            ),
            'products' => $products,
            'sort' => $sort,
            'perPage' => $perPage,
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
            'activeVariants.galleryMedia',
            'galleryMedia' => fn ($query) => $query->orderBy('sort_order')->orderBy('id')->limit(8),
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
