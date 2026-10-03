<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreChequePermissionRequest;
use App\Models\ChequePermission;
use App\Models\Product;
use App\Models\WholesalePack;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class WholesaleController extends Controller
{
    public function show(Request $request): View
    {
        $products = Product::query()
            ->select([
                'id', 'category_id', 'brand_id', 'name', 'slug',
                'is_featured', 'sort_order',
            ])
            ->active()
            ->whereHas('activeVariants', fn ($query) => $query->whereNotNull('wholesale_price'))
            ->with([
                'brand:id,name,slug',
                'category:id,name,slug',
                'primaryGalleryMedia',
                'activeVariants' => fn ($query) => $query
                    ->whereNotNull('wholesale_price')
                    ->select([
                        'id', 'product_id', 'sku', 'color', 'size',
                        'stock', 'wholesale_price', 'sort_order',
                    ]),
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

        $user = $request->user();

        return view('pages.wholesale', [
            'chequePermission' => $user?->chequePermission()->first(),
            'isCustomer' => $user?->isCustomer() ?? false,
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

        $message = match ($result) {
            'already-approved' => 'مجوز پرداخت چکی این حساب از قبل فعال است.',
            'already-pending' => 'درخواست پرداخت چکی شما ثبت شده و در انتظار تأیید مدیر است.',
            default => 'درخواست پرداخت چکی شما با موفقیت ثبت شد و در انتظار تأیید مدیر است.',
        };

        // Redirect to the status block instead of a generic back(). This makes the
        // persisted pending/approved state and the flash confirmation immediately
        // visible after submitting the cheque request.
        return redirect()
            ->to(route('wholesale.show') . '#cheque-application')
            ->with('success', $message);
    }
}
