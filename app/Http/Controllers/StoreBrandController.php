<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Services\SeoService;
use Illuminate\View\View;

class StoreBrandController extends Controller
{
    private function catalogRelations(): array
    {
        return [
            'category:id,name,slug',
            'brand:id,name,slug',
            'primaryActiveVariant' => fn ($query) => $query->select([
                'product_variants.id','product_variants.product_id','product_variants.sku','product_variants.price','product_variants.sale_price','product_variants.stock',
                'product_variants.low_stock_threshold','product_variants.is_active','product_variants.sort_order',
            ]),
            'primaryGalleryMedia' => fn ($query) => $query->select([
                'id','mediable_id','mediable_type','collection','path','sort_order',
            ]),
            'activeVariants' => fn ($query) => $query->select([
                'id','product_id','sku','size','color','price','sale_price',
                'stock','low_stock_threshold','is_active','sort_order',
            ]),
        ];
    }

    public function index(SeoService $seo): View
    {
        $brands = Brand::query()
            ->active()
            ->with('logoMedia')
            ->withCount([
                'products as active_products_count' => fn ($query) =>
                $query->where('is_active', true),
            ])
            ->orderBy('name')
            ->orderBy('id')
            ->get();

        return view('brands.index', [
            'seo' => $seo->page(
                'برندها — ' . config('app.store_name', 'Janan'),
                'معرفی برندهای فعال و محصولات مرتبط در فروشگاه جانان.',
                route('brands.index')
            ),
            'brands' => $brands,
        ]);
    }

    public function show(Brand $brand, SeoService $seo, Request $request): View
    {
        abort_unless($brand->is_active, 404);
        $brand->load('logoMedia');

        $sort = in_array($request->query('sort'), [
            'newest','oldest','price_asc','price_desc','name_asc','name_desc',
        ], true) ? $request->query('sort') : 'newest';

        $perPage = in_array((int) $request->query('per_page', 6), [6,12,24,36], true)
            ? (int) $request->query('per_page', 6)
            : 6;

        $products = Product::query()
            ->select(['id','category_id','brand_id','name','slug','short_description','updated_at'])
            ->active()
            ->with($this->catalogRelations())
            ->where('brand_id', $brand->id)
            ->when($sort === 'oldest', fn ($query) => $query->orderBy('updated_at')->orderBy('id'))
            ->when($sort === 'price_asc', fn ($query) => $query->orderByEffectivePrice('asc'))
            ->when($sort === 'price_desc', fn ($query) => $query->orderByEffectivePrice('desc'))
            ->when($sort === 'name_asc', fn ($query) => $query->orderBy('name')->orderBy('id'))
            ->when($sort === 'name_desc', fn ($query) => $query->orderByDesc('name')->orderBy('id'))
            ->when($sort === 'newest', fn ($query) => $query->latest('updated_at')->latest('id'))
            ->paginate($perPage)
            ->withQueryString();

        return view('brands.show', [
            'seo' => $seo->brand($brand),
            'brand' => $brand,
            'products' => $products,
            'sort' => $sort,
            'perPage' => $perPage,
        ]);
    }
}
