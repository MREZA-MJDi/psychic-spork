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
        // Online wholesale checkout is public. Cheque authorization is enforced
        // separately by ChequePaymentMethod + ChequePermission.
    }

    public function start(
        Order $order,
        ?User $user,
        array $data
    ): Payment {
        return $this->gateway->purchase($order);
    }
}
