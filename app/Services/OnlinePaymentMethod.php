<?php

namespace App\Services;

use App\Contracts\PaymentGateway;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;

final class OnlinePaymentMethod implements PaymentMethod
{
    public function __construct(
        private readonly PaymentGateway $gateway,
    ) {
    }

    public function code(): string
    {
        return 'online';
    }

    public function validateCheckout(?User $user, array $data): void
    {
        if (($data['order_type'] ?? 'retail') === 'wholesale') {
            abort_unless(
                $user && $user->isCustomer(),
                403,
                'برای خرید عمده باید وارد حساب مشتری شوید.'
            );

        }
    }

    public function start(
        Order $order,
        ?User $user,
        array $data
    ): Payment {
        return $this->gateway->purchase($order);
    }
}
