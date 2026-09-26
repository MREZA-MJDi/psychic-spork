<?php

namespace App\Services;

use App\Contracts\PaymentGateway;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use RuntimeException;

final class ZarinPalPaymentGateway implements PaymentGateway
{
    public function name(): string
    {
        return 'zarinpal';
    }

    public function purchase(Order $order): Payment
    {
        $merchantId = config('payment.zarinpal.merchant_id');

        if (! filled($merchantId)) {
            throw new RuntimeException(
                'درگاه پرداخت پیکربندی نشده است. MERCHANT ID را تنظیم کنید.'
            );
        }

        $payment = $order->payments()->create([
            'gateway' => $this->name(),
            'amount' => $order->total,
            'status' => 'pending',
        ]);

        $callbackUrl = URL::signedRoute(
            'payment.zarinpal.callback',
            ['order' => $order->id]
        );

        $amountRial = (int) round(
            ((float) $order->total) * 10
        );

        try {
            $response = Http::asJson()
                ->acceptJson()
                ->timeout(config('payment.zarinpal.timeout', 15))
                ->post(
                    config('payment.zarinpal.request_url'),
                    [
                        'merchant_id' => $merchantId,
                        'amount' => $amountRial,
                        'description' => 'سفارش ' . $order->order_number,
                        'callback_url' => $callbackUrl,
                        'metadata' => array_filter([
                            'mobile' => $order->customer_phone,
                            'email' => $order->customer_email,
                        ]),
                    ]
                );
        } catch (ConnectionException $e) {
            report($e);

            throw new RuntimeException(
                'اتصال به درگاه پرداخت برقرار نشد.',
                previous: $e
            );
        }

        $payload = $response->json();

        if (
            ! $response->successful()
            || (int) data_get($payload, 'data.code') !== 100
            || ! filled(data_get($payload, 'data.authority'))
        ) {
            $message = data_get(
                $payload,
                'errors.message',
                'درخواست پرداخت توسط درگاه پذیرفته نشد.'
            );

            throw new RuntimeException(
                'خطا در ایجاد تراکنش پرداخت: ' . $message
            );
        }

        $authority = (string) data_get(
            $payload,
            'data.authority'
        );

        $redirectUrl = rtrim(
            config('payment.zarinpal.startpay_url'),
            '/'
        ) . '/' . rawurlencode($authority);

        $payment->update([
            'authority' => $authority,
            'metadata' => [
                'redirect_url' => $redirectUrl,
                'request_code' => (int) data_get(
                    $payload,
                    'data.code'
                ),
                'amount_rial' => $amountRial,
            ],
        ]);

        $order->update([
            'payment_method' => $this->name(),
        ]);

        return $payment->fresh();
    }

    public function verify(Payment $payment): Payment
    {
        $merchantId = config('payment.zarinpal.merchant_id');

        abort_unless(
            filled($merchantId),
            500,
            'درگاه پرداخت پیکربندی نشده است.'
        );

        abort_unless(
            $payment->authority,
            422,
            'کد پیگیری اولیه پرداخت یافت نشد.'
        );

        $amountRial = (int) round(
            ((float) $payment->amount) * 10
        );

        try {
            $response = Http::asJson()
                ->acceptJson()
                ->timeout(config('payment.zarinpal.timeout', 15))
                ->post(
                    config('payment.zarinpal.verify_url'),
                    [
                        'merchant_id' => $merchantId,
                        'amount' => $amountRial,
                        'authority' => $payment->authority,
                    ]
                );
        } catch (ConnectionException $e) {
            report($e);

            throw new RuntimeException(
                'اتصال به سرویس تأیید پرداخت برقرار نشد.',
                previous: $e
            );
        }

        $payload = $response->json();
        $code = (int) data_get($payload, 'data.code');

        if (
            $response->successful()
            && in_array($code, [100, 101], true)
        ) {
            return tap($payment)->update([
                'status' => 'paid',
                'transaction_id' => $payment->authority,
                'reference_number' => data_get(
                    $payload,
                    'data.ref_id'
                )
                    ? (string) data_get($payload, 'data.ref_id')
                    : $payment->reference_number,
                'paid_at' => $payment->paid_at ?? now(),
                'metadata' => array_merge(
                    $payment->metadata ?? [],
                    [
                        'verify_code' => $code,
                    ]
                ),
            ]);
        }

        $payment->update([
            'status' => 'failed',
            'metadata' => array_merge(
                $payment->metadata ?? [],
                [
                    'verify_code' => $code,
                    'verify_error' => data_get(
                        $payload,
                        'errors.message'
                    ),
                ]
            ),
        ]);

        return $payment->fresh();
    }

    public function refund(Payment $payment): Payment
    {
        throw new RuntimeException(
            'Refund API این درگاه هنوز در این flow فعال نشده است.'
        );
    }
}
