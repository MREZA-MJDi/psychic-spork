<?php

namespace App\Services;

use App\Models\FinancialTransaction;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

final class PaymentService
{
    public function matchStatus(Order $order, string $status): void
    {
        match ($status) {
            'paid' => $this->markPaid($order),
            'failed' => $this->markFailed($order),
            'refunded' => $this->refund($order),
            'pending' => $this->setStatus($order, 'pending'),
            default => abort(422, 'وضعیت پرداخت معتبر نیست.'),
        };
    }

    public function setStatus(Order $order, string $status, string $gateway = 'manual'): Payment
    {
        abort_unless(in_array($status, Payment::STATUSES, true), 422, 'وضعیت پرداخت معتبر نیست.');

        return DB::transaction(function () use ($order, $status, $gateway) {
            $payment = $order->payments()->latest('id')->first() ?? $order->payments()->create([
                'gateway' => $gateway,
                'amount' => $order->total,
                'status' => 'pending',
            ]);

            $payment->update([
                'gateway' => $gateway,
                'amount' => $order->total,
                'status' => $status,
                'paid_at' => $status === 'paid' ? ($payment->paid_at ?? now()) : $payment->paid_at,
            ]);

            $order->update([
                'payment_status' => $status,
                'paid_at' => $status === 'paid' ? ($order->paid_at ?? now()) : $order->paid_at,
                'payment_method' => $gateway,
            ]);

            return $payment->fresh();
        });
    }

    public function markPaid(Order $order, string $gateway = 'manual', ?string $reference = null): Payment
    {
        return DB::transaction(function () use ($order, $gateway, $reference) {
            $payment = $this->setStatus($order, 'paid', $gateway);

            $payment->update([
                'reference_number' => $reference ?? $payment->reference_number,
            ]);

            FinancialTransaction::firstOrCreate([
                'type' => 'income',
                'category' => 'order',
                'reference_type' => Order::class,
                'reference_id' => $order->id,
            ], [
                'amount' => $order->total,
                'description' => "دریافت سفارش {$order->order_number}",
                'transaction_date' => optional($order->placed_at)->toDateString() ?: now()->toDateString(),
                'created_by' => auth()->id(),
            ]);

            return $payment->fresh();
        });
    }

    public function markFailed(Order $order, string $gateway = 'manual'): Payment
    {
        return $this->setStatus($order, 'failed', $gateway);
    }

    public function refund(Order $order): Payment
    {
        return DB::transaction(function () use ($order) {
            $payment = $order->payments()->latest('id')->firstOrFail();

            abort_unless(
                $payment->status === 'paid' || $order->payment_status === 'paid',
                422,
                'فقط یک پرداخت موفق قابل بازپرداخت است.'
            );

            $payment->update([
                'status' => 'refunded',
            ]);

            $order->update([
                'payment_status' => 'refunded',
            ]);

            FinancialTransaction::firstOrCreate([
                'type' => 'expense',
                'category' => 'refund',
                'reference_type' => Order::class,
                'reference_id' => $order->id,
            ], [
                'amount' => $order->total,
                'description' => "بازپرداخت سفارش {$order->order_number}",
                'transaction_date' => now()->toDateString(),
                'created_by' => auth()->id(),
            ]);

            return $payment->fresh();
        });
    }
}
