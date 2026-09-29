<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;

interface PaymentMethod
{
    public function code(): string;

    public function validateCheckout(
        ?User $user,
        array $data
    ): void;

    public function start(
        Order $order,
        ?User $user,
        array $data
    ): Payment;
}
