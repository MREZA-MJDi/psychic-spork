<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;

final class ChequePaymentMethod implements PaymentMethod
{
    public function __construct(
        private readonly WholesaleEligibilityService $eligibility,
        private readonly ChequePaymentService $cheques,
    ) {
    }

    public function code(): string
    {
        return 'cheque';
    }

    public function validateCheckout(?User $user, array $data): void
    {
        abort_unless(
            $user && $user->isCustomer(),
            403,
            'پرداخت چکی فقط برای حساب مشتری مجاز است.'
        );

        abort_unless(
            ($data['order_type'] ?? null) === 'wholesale',
            422,
            'پرداخت چکی فقط برای سفارش عمده مجاز است.'
        );

        $amount = (float) ($data['checkout_total'] ?? 0);

        abort_if(
            $amount <= 0,
            422,
            'مبلغ سفارش برای پرداخت چکی معتبر نیست.'
        );

        $this->eligibility->assertChequeAllowed($user, $amount);
    }

    public function start(
        Order $order,
        ?User $user,
        array $data
    ): Payment {
        abort_unless($user, 403);

        return $this->cheques->submit($order, $user, $data);
    }
}
