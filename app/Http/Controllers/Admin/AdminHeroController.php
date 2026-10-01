<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HeroSlide;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminHeroController extends Controller
{
    public function index(): View
    {
        $slides = HeroSlide::query()
            ->with([
                'product:id,name,slug,brand_id',
                'product.primaryGalleryMedia',
                'product.brand:id,name',
                'product.brand.logoMedia',
            ])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $selectedIds = $slides
            ->pluck('product_id')
            ->filter()
            ->values();

        $products = Product::query()
            ->active()
            ->with([
                'primaryGalleryMedia',
                'brand:id,name',
            ])
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return view('admin.hero.index', compact('slides', 'selectedIds', 'products'));
    }

    public function update(): RedirectResponse
    {
        $productIds = collect(request()->input('product_ids', []))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->take(6);

        $products = Product::query()
            ->active()
            ->whereIn('id', $productIds)
            ->get()
            ->keyBy('id');

        DB::transaction(function () use ($productIds, $products): void {
            HeroSlide::query()->delete();

            foreach ($productIds as $index => $productId) {
                $product = $products->get($productId);

                if (!$product) {
                    continue;
                }

                HeroSlide::query()->create([
                    'product_id' => $product->id,
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ]);
            }
        });

        Cache::forget('store:home:hero');

        return back()->with(
            'success',
            'تصاویر Hero با موفقیت ذخیره شد. حداکثر ۶ محصول انتخاب می‌شود.'
        );
    }
}
