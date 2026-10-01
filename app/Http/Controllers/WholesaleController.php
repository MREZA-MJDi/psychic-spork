<?php

namespace App\Http\Controllers;

use App\Http\Requests\WholesaleApplicationRequest;
use App\Models\WholesaleProfile;
use App\Models\Product;
use App\Models\WholesalePack;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WholesaleController extends Controller
{
    public function show(Request $request): View
    {
        $profile = $request->user()?->wholesaleProfile()->first();

        $products = Product::query()
            ->active()
            ->whereHas('activeVariants', fn ($query) => $query->whereNotNull('wholesale_price'))
            ->with([
                'brand',
                'category',
                'primaryGalleryMedia',
                'activeVariants' => fn ($query) => $query->whereNotNull('wholesale_price'),
            ])
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->paginate(12, ['*'], 'products_page');

        $packs = WholesalePack::query()
            ->where('is_active', true)
            ->with([
                'items.variant.product.brand',
                'items.variant.product.primaryGalleryMedia',
            ])
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->get();

        return view('pages.wholesale', [
            'profile' => $profile,
            'products' => $products,
            'packs' => $packs,
        ]);
    }

    public function apply(
        WholesaleApplicationRequest $request
    ): RedirectResponse {
        $user = $request->user();

        abort_unless(
            $user->isCustomer(),
            403,
            'فقط حساب مشتری می‌تواند برای خرید عمده درخواست بدهد.'
        );

        $profile = $user->wholesaleProfile()->first();

        abort_if(
            $profile?->isApproved(),
            422,
            'حساب شما در حال حاضر دسترسی خرید عمده دارد.'
        );

        abort_if(
            $profile?->status === 'pending',
            422,
            'درخواست عمده شما در حال بررسی مدیریت است.'
        );

        abort_if(
            $profile?->status === 'suspended',
            403,
            'دسترسی عمده این حساب توسط مدیریت تعلیق شده است.'
        );

        WholesaleProfile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'status' => 'pending',
                'business_name' => $request->validated('business_name'),
                'business_type' => $request->validated('business_type'),
                'business_phone' => $request->validated('business_phone'),
                'business_address' => $request->validated('business_address'),
                'approved_by' => null,
                'approved_at' => null,
                'suspended_by' => null,
                'suspended_at' => null,
            ]
        );

        return redirect()
            ->route('wholesale.show')
            ->with(
                'success',
                'درخواست خرید عمده ثبت شد و پس از بررسی مدیریت فعال می‌شود.'
            );
    }
}
