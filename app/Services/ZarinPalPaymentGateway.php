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
        abort_unless(
            $order->payment_status === 'pending',
            422,
            'این سفارش در وضعیت قابل پرداخت نیست.'
        );

        $existing = $order->payments()
            ->where('gateway', $this->name())
            ->whereIn('status', ['pending'])
            ->latest('id')
            ->first();

        if ($existing?->authority) {
            return $existing->fresh();
        }

        $payment = $existing ?? $order->payments()->create([
            'gateway' => $this->name(),
            'idempotency_key' => 'zarinpal:order:' . $order->id,
            'amount' => $order->total,
            'status' => 'pending',
        ]);

        $response = $this->request(
            config('payment.zarinpal.request_url'),
            [
                'merchant_id' => config('payment.zarinpal.merchant_id'),
                'amount' => $this->gatewayAmount($order->total),
                'description' => 'پرداخت سفارش ' . $order->order_number,
                'callback_url' => URL::temporarySignedRoute(
                    'payment.zarinpal.callback',
                    now()->addMinutes(30),
                    ['order' => $order->id]
                ),
                'metadata' => array_filter([
                    'email' => $order->customer_email,
                    'mobile' => $order->customer_phone,
                ]),
            ]
        );

        $code = (int) data_get($response, 'data.code', -1);
        $authority = trim((string) data_get(
            $response,
            'data.authority',
            ''
        ));

        abort_unless(
            $code === 100 && $authority !== '',
            502,
            'درخواست پرداخت از درگاه پذیرفته نشد.'
        );

        $payment->update([
            'authority' => $authority,
            'metadata' => [
                'redirect_url' => rtrim(
                    config('payment.zarinpal.startpay_url'),
                    '/'
                ) . '/' . $authority,
            ],
        ]);

        return $payment->fresh();
    }

    public function verify(Payment $payment): Payment
    {
        $payment = $payment->fresh();

        if ($payment->status === 'paid') {
            return $payment;
        }

        abort_unless(
            $payment->gateway === $this->name()
                && filled($payment->authority),
            422,
            'پرداخت درگاه قابل تأیید نیست.'
        );

        $response = $this->request(
            config('payment.zarinpal.verify_url'),
            [
                'merchant_id' => config('payment.zarinpal.merchant_id'),
                'amount' => $this->gatewayAmount($payment->amount),
                'authority' => $payment->authority,
            ]
        );

        $code = (int) data_get($response, 'data.code', -1);

        if ($code === 100 || $code === 101) {
            $payment->update([
                'status' => 'paid',
                'transaction_id' => (string) data_get(
                    $response,
                    'data.ref_id',
                    $payment->transaction_id
                ),
                'reference_number' => (string) data_get(
                    $response,
                    'data.ref_id',
                    $payment->reference_number
                ),
                'paid_at' => $payment->paid_at ?? now(),
            ]);

            return $payment->fresh();
        }

        $payment->update(['status' => 'failed']);

        return $payment->fresh();
    }

    public function refund(Payment $payment): Payment
    {
        $payment = $payment->fresh();

        abort_unless(
            $payment->gateway === $this->name()
                && $payment->status === 'paid'
                && filled($payment->authority),
            422,
            'این پرداخت قابل بازگشت نیست.'
        );

        $response = $this->request(
            config('payment.zarinpal.reverse_url'),
            [
                'merchant_id' => config('payment.zarinpal.merchant_id'),
                'authority' => $payment->authority,
            ]
        );

        $code = (int) data_get($response, 'data.code', -1);

        abort_unless(
            in_array($code, [100, 101], true),
            502,
            'بازگشت وجه توسط درگاه تأیید نشد.'
        );

        $payment->update([
            'status' => 'refunded',
        ]);

        return $payment->fresh();
    }

    private function request(string $url, array $payload): array
    {
        if (blank(config('payment.zarinpal.merchant_id'))) {
            throw new RuntimeException(
                'شناسه پذیرنده زرین‌پال تنظیم نشده است.'
            );
        }

        try {
            $response = Http::acceptJson()
                ->asJson()
                ->timeout((int) config('payment.zarinpal.timeout', 15))
                ->retry(2, 250, throw: false)
                ->post($url, $payload);
        } catch (ConnectionException $e) {
            throw new RuntimeException(
                'ارتباط با درگاه پرداخت برقرار نشد.',
                previous: $e
            );
        }

        if ($response->failed()) {
            throw new RuntimeException(
                'درگاه پرداخت پاسخ معتبر نداد.'
            );
        }

        $json = $response->json();

        if (! is_array($json)) {
            throw new RuntimeException(
                'پاسخ درگاه پرداخت نامعتبر است.'
            );
        }

        return $json;
    }

    private function gatewayAmount(float|string $amount): int
    {
        $amount = (int) round((float) $amount);

        return config('payment.currency_unit') === 'toman'
            ? $amount * 10
            : $amount;
    }
}
