<?php

namespace App\Services;

use App\Models\ChequePermission;
use App\Models\User;
use App\Models\WholesaleProfile;

final class WholesaleEligibilityService
{
    public function assertWholesaleAllowed(User $user): WholesaleProfile
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

    public function assertChequeAllowed(
        User $user,
        float $amount
    ): ChequePermission {
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
