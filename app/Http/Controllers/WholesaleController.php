<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreChequePermissionRequest;
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
                'activeVariants' => fn ($query) => $query
                    ->whereNotNull('wholesale_price')
                    ->select([
                        'id',
                        'product_id',
                        'sku',
                        'color',
                        'size',
                        'stock',
                        'wholesale_price',
                        'sort_order',
                    ]),
            ])
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->paginate(12, ['*'], 'products_page')
            ->withQueryString();

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
                'items.variant.primaryGalleryMedia' => fn ($query) => $query->select([
                    'media.id',
                    'media.mediable_id',
                    'media.mediable_type',
                    'media.collection',
                    'media.path',
                    'media.sort_order',
                ]),
                'items.variant.product:id,name,slug,brand_id',
                'items.variant.product.brand:id,name',
                'items.variant.product.primaryGalleryMedia' => fn ($query) => $query->select([
                    'id',
                    'mediable_id',
                    'mediable_type',
                    'collection',
                    'path',
                    'sort_order',
                ]),
            ])
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->paginate(6, ['*'], 'packs_page')
            ->withQueryString();

        return view('pages.wholesale', [
            'chequePermission' => $request->user()?->chequePermission()->first(),
            'isCustomer' => $request->user()?->isCustomer() ?? false,
            'products' => $products,
            'packs' => $packs,
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
