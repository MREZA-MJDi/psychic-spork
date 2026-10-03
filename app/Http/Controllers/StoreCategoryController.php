<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Services\SeoService;
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

    public function show(Category $category, SeoService $seo, Request $request): View
    {
        abort_unless($category->is_active, 404);

        $category->load('coverMedia');

        $sort = in_array($request->query('sort'), [
            'newest',
            'oldest',
            'price_asc',
            'price_desc',
            'name_asc',
            'name_desc',
        ], true) ? $request->query('sort') : 'newest';

        $perPage = in_array((int) $request->query('per_page', 6), [6, 12, 24, 36], true)
            ? (int) $request->query('per_page', 6)
            : 6;

        $products = Product::query()
            ->active()
            ->with([
                'category:id,name,slug',
                'brand:id,name,slug',
                'primaryActiveVariant',
                'primaryGalleryMedia',
                'activeVariants',
            ])
            ->where('category_id', $category->id)
            ->when($sort === 'oldest', fn ($query) => $query->orderBy('updated_at')->orderBy('id'))
            ->when($sort === 'price_asc', fn ($query) => $query->orderByEffectivePrice('asc'))
            ->when($sort === 'price_desc', fn ($query) => $query->orderByEffectivePrice('desc'))
            ->when($sort === 'name_asc', fn ($query) => $query->orderBy('name')->orderBy('id'))
            ->when($sort === 'name_desc', fn ($query) => $query->orderByDesc('name')->orderBy('id'))
            ->when($sort === 'newest', fn ($query) => $query->latest('updated_at')->latest('id'))
            ->paginate($perPage)
            ->withQueryString();

        return view('categories.show', [
            'seo' => $seo->category($category),
            'category' => $category,
            'products' => $products,
            'sort' => $sort,
            'perPage' => $perPage,
        ]);
    }
}
