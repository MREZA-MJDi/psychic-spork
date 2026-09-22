<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Product;
use Illuminate\View\View;

class StoreBrandController extends Controller
{
    public function index(): View
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
            'brands' => $brands,
        ]);
    }

    public function show(Brand $brand): View
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
            'brand' => $brand,
            'products' => $products,
        ]);
    }
}
