<?php

namespace AppHttpControllers;

use AppModelsBrand;
use AppModelsProduct;
use AppServicesSeoService;
use IlluminateViewView;

class StoreBrandController extends Controller
{
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

    public function show(Brand $brand, SeoService $seo): View
    {
        abort_unless($brand->is_active, 404);

        $brand->load('logoMedia');

        $products = Product::query()
            ->active()
            ->with([
                'category',
                'brand',
                'variants',
                'galleryMedia',
            ])
            ->where('brand_id', $brand->id)
            ->latest('updated_at')
            ->latest('id')
            ->paginate(12)
            ->withQueryString();

        return view('brands.show', [
            'seo' => $seo->brand($brand),
            'brand' => $brand,
            'products' => $products,
        ]);
    }
}
