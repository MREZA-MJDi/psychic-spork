<?php

namespace App\Contracts;

use App\Models\Order;
use App\Models\Payment;

interface PaymentGateway
{
    public function name(): string;
    public function purchase(Order $order): Payment;
    public function verify(Payment $payment): Payment;
    public function refund(Payment $payment): Payment;
}
