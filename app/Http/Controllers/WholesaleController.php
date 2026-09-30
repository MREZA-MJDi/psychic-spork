<?php

namespace App\Http\Controllers;

use App\Http\Requests\WholesaleApplicationRequest;
use App\Models\ChequePermission;
use App\Models\WholesaleProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WholesaleController extends Controller
{
    public function show(Request $request): View
    {
        $profile = $request->user()
            ->wholesaleProfile()
            ->first();

        $chequePermission = $request->user()
            ->chequePermission()
            ->first();

        return view('pages.wholesale', [
            'profile' => $profile,
            'chequePermission' => $chequePermission,
        ]);
    }


    public function requestCheque(
        Request $request
    ): RedirectResponse {
        $user = $request->user();

        abort_unless(
            $user->isCustomer(),
            403,
            'فقط حساب مشتری می‌تواند برای پرداخت چکی درخواست بدهد.'
        );

        $permission = $user->chequePermission()->first();

        if ($permission?->isApproved()) {
            return back()->with(
                'success',
                'پرداخت چکی برای این حساب در حال حاضر فعال است.'
            );
        }

        if ($permission?->isPending()) {
            return back()->with(
                'success',
                'درخواست پرداخت چکی شما قبلاً ثبت شده و در انتظار بررسی مدیریت است.'
            );
        }

        ChequePermission::updateOrCreate(
            ['user_id' => $user->id],
            [
                'enabled' => false,
                'status' => ChequePermission::STATUS_PENDING,
                'requested_at' => now(),
            ]
        );

        return back()->with(
            'success',
            'درخواست پرداخت چکی ثبت شد و پس از بررسی مدیریت، نتیجه اعلام می‌شود.'
        );
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
