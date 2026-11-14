<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreChequePermissionRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\ChequePermission;
use App\Models\Product;
use App\Models\WholesalePack;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class WholesaleController extends Controller
{
    public function show(Request $request): View
    {
        $packs = WholesalePack::query()
            ->where('is_active', true)
            ->with([
                'items' => fn ($query) => $query->select([
                    'id',
                    'wholesale_pack_id',
                    'product_variant_id',
                    'quantity',
                ]),
                'items.variant' => fn ($query) => $query->select([
                    'id',
                    'product_id',
                    'sku',
                    'size',
                    'color',
                    'stock',
                    'sort_order',
                ]),
                'items.variant.primaryGalleryMedia',
                'items.variant.product:id,name,slug,brand_id',
                'items.variant.product.brand:id,name',
                'items.variant.product.primaryGalleryMedia',
            ])
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->paginate(6, ['*'], 'packs_page')
            ->withQueryString();

        return view('pages.wholesale', [
            'chequePermission' => $request->user()?->chequePermission()->first(),
            'isCustomer' => $request->user()?->isCustomer() ?? false,
            'packs' => $packs,
        ]);
    }

    /**
     * Keep the public wholesale landing page focused on ready-made packs.
     * Product discovery lives on its own paginated page and only aggregates
     * wholesale variant counts/prices instead of hydrating every variant.
     */
    public function catalog(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'brand_id' => ['nullable', 'integer', 'exists:brands,id'],
        ]);

        $products = Product::query()
            ->select([
                'id',
                'category_id',
                'brand_id',
                'name',
                'slug',
                'updated_at',
            ])
            ->active()
            ->whereHas('activeVariants', fn ($query) => $query->whereNotNull('wholesale_price'))
            ->withCount([
                'activeVariants as wholesale_variants_count' => fn ($query) => $query
                    ->whereNotNull('wholesale_price'),
            ])
            ->withMin([
                'activeVariants as minimum_wholesale_price' => fn ($query) => $query
                    ->whereNotNull('wholesale_price'),
            ], 'wholesale_price')
            ->withMax([
                'activeVariants as maximum_wholesale_price' => fn ($query) => $query
                    ->whereNotNull('wholesale_price'),
            ], 'wholesale_price')
            ->with([
                'brand:id,name,slug',
                'category:id,name,slug',
                'primaryGalleryMedia' => fn ($query) => $query->select([
                    'media.id',
                    'media.mediable_id',
                    'media.mediable_type',
                    'media.collection',
                    'media.path',
                    'media.sort_order',
                ]),
            ])
            ->when($filters['q'] ?? null, function ($query, string $term): void {
                $like = '%' . trim($term) . '%';

                $query->where(function ($search) use ($like): void {
                    $search
                        ->where('name', 'like', $like)
                        ->orWhere('slug', 'like', $like)
                        ->orWhereHas('activeVariants', fn ($variants) => $variants
                            ->whereNotNull('wholesale_price')
                            ->where('sku', 'like', $like))
                        ->orWhereHas('brand', fn ($brand) => $brand
                            ->where('is_active', true)
                            ->where('name', 'like', $like));
                });
            })
            ->when(
                $filters['category_id'] ?? null,
                fn ($query, int $categoryId) => $query->where('category_id', $categoryId)
            )
            ->when(
                $filters['brand_id'] ?? null,
                fn ($query, int $brandId) => $query->where('brand_id', $brandId)
            )
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate(12, ['*'], 'products_page')
            ->withQueryString();

        return view('pages.wholesale-catalog', [
            'products' => $products,
            'categories' => Category::query()
                ->active()
                ->select(['id', 'name'])
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
            'brands' => Brand::query()
                ->active()
                ->select(['id', 'name'])
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function requestCheque(StoreChequePermissionRequest $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validated();

        $result = DB::transaction(function () use ($user, $data): string {
            \App\Models\User::query()
                ->lockForUpdate()
                ->findOrFail($user->id);

            $permission = ChequePermission::query()
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->first();

            if ($permission?->isApproved()) {
                return 'already-approved';
            }

            if ($permission?->isPending()) {
                return 'already-pending';
            }

            ChequePermission::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'enabled' => false,
                    'max_order_amount' => null,
                    'requested_amount' => $data['requested_amount'],
                    'requested_at' => now(),
                    'approved_by' => null,
                    'approved_at' => null,
                    'disabled_by' => null,
                    'disabled_at' => null,
                ]
            );

            return 'requested';
        }, 3);

        return back()
            ->with('success', match ($result) {
                'already-approved' => 'مجوز پرداخت چکی این حساب از قبل فعال است.',
                'already-pending' => 'درخواست پرداخت چکی شما در حال بررسی است.',
                default => 'درخواست مجوز چک برای مدیریت ارسال شد.',
            })
            ->withFragment('cheque-application');
    }
}
