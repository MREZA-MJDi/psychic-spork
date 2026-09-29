<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use InvalidArgumentException;

final class PaymentMethodManager
{
    /** @param array<string, PaymentMethod> $methods */
    public function __construct(
        private readonly array $methods,
    ) {
    }

    public function resolve(string $code): PaymentMethod
    {
        $method = $this->methods[$code] ?? null;

        if (! $method) {
            throw new InvalidArgumentException(
                'روش پرداخت معتبر نیست.'
            );
        }

        return $method;
    }

    public function validateCheckout(
        string $code,
        ?User $user,
        array $data
    ): void {
        $this->resolve($code)->validateCheckout($user, $data);
    }

    public function start(
        string $code,
        Order $order,
        ?User $user,
        array $data
    ): Payment {
        return $this->resolve($code)->start($order, $user, $data);
    }
}
