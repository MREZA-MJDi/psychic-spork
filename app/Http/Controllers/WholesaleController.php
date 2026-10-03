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
            ->active()
            ->whereHas('activeVariants', fn ($query) => $query->whereNotNull('wholesale_price'))
            ->with([
                'brand',
                'category',
                'primaryGalleryMedia',
                'activeVariants' => fn ($query) => $query
                    ->whereNotNull('wholesale_price')
                    ->select(['id', 'product_id', 'color', 'size', 'stock', 'wholesale_price']),
            ])
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->paginate(12, ['*'], 'products_page');

        $packs = WholesalePack::query()
            ->where('is_active', true)
            ->with([
                'items.variant.primaryGalleryMedia',
                'items.variant.product.brand',
                'items.variant.product.primaryGalleryMedia',
            ])
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->get();

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

        return back()->with('success', match ($result) {
            'already-approved' => 'مجوز پرداخت چکی این حساب از قبل فعال است.',
            'already-pending' => 'درخواست پرداخت چکی شما در حال بررسی است.',
            default => 'درخواست مجوز چک برای مدیریت ارسال شد.',
        });
    }

}
