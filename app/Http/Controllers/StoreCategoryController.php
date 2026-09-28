<?php

namespace App\Http\Controllers;

use App\ModelsCategory;
use App\ModelsProduct;
use App\ServicesSeoService;
use Illuminate\View\View;

class StoreCategoryController extends Controller
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
            ->get();

        return view('categories.index', [
            'seo' => $seo->page(
                'دسته‌بندی‌ها — ' . config('app.store_name', 'Janan'),
                'مرور دسته‌بندی‌ها و کالکشن‌های فروشگاه جانان.',
                route('categories.index')
            ),
            'categories' => $categories,
        ]);
    }

    public function show(Category $category, SeoService $seo): View
    {
        abort_unless($category->is_active, 404);

        $category->load('coverMedia');

        $products = Product::query()
            ->active()
            ->with([
                'category',
                'brand',
                'variants',
                'galleryMedia',
            ])
            ->where('category_id', $category->id)
            ->latest('updated_at')
            ->latest('id')
            ->paginate(12)
            ->withQueryString();

        return view('categories.show', [
            'seo' => $seo->category($category),
            'category' => $category,
            'products' => $products,
        ]);
    }
}
