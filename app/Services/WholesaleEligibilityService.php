<?php

namespace App\Services;

use App\Models\ChequePermission;
use App\Models\User;
use App\Models\WholesaleProfile;

final class WholesaleEligibilityService
{
    public function assertWholesaleAllowed(User $user): ?WholesaleProfile
    {
        abort_if(
            ! $user->isCustomer(),
            403,
            'فقط حساب مشتری می‌تواند خرید عمده انجام دهد.'
        );

        $profile = $user->wholesaleProfile;

        abort_unless(
            $profile?->isApproved(),
            403,
            'دسترسی خرید عمده این حساب هنوز توسط مدیریت تأیید نشده است.'
        );

        return $profile;
    }

    public function assertWholesaleOrder(User $user, float $amount, int $quantity): ?WholesaleProfile
    {
        $profile = $this->assertWholesaleAllowed($user);

        if (
            $profile
            && $profile->minimum_order_amount !== null
            && $amount < (float) $profile->minimum_order_amount
        ) {
            abort(
                422,
                'حداقل مبلغ سفارش عمده '
                . number_format((float) $profile->minimum_order_amount)
                . ' تومان است.'
            );
        }

        if (
            $profile
            && $profile->minimum_order_quantity !== null
            && $quantity < (int) $profile->minimum_order_quantity
        ) {
            abort(
                422,
                'حداقل تعداد سفارش عمده '
                . number_format((int) $profile->minimum_order_quantity)
                . ' عدد است.'
            );
        }

        return $profile;
    }

    public function assertChequeAllowed(
        User $user,
        float $amount
    ): ChequePermission {
        abort_if(
            ! $user->isCustomer(),
            403,
            'فقط حساب مشتری می‌تواند پرداخت چکی داشته باشد.'
        );

        $this->assertWholesaleAllowed($user);

        $permission = $user->chequePermission;

        abort_unless(
            $permission?->allows($amount),
            403,
            'پرداخت چکی برای این حساب یا این مبلغ مجاز نیست.'
        );

        return $permission;
    }
}
