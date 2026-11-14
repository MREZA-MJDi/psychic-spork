<?php

namespace App\Http\Controllers;

use App\Contracts\PaymentGateway;
use App\Services\CartService;
use App\Services\OrderService;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class PaymentController extends Controller
{
    public function zarinpalCallback(
        Request $request,
        int $order,
        PaymentGateway $gateway,
        PaymentService $payments,
        OrderService $orders,
        CartService $cart
    ): View|RedirectResponse {
        abort_unless(
            $request->hasValidSignatureWhileIgnoring([
                'Authority',
                'Status',
            ]),
            403,
            'آدرس بازگشت پرداخت معتبر نیست.'
        );

        $orderModel = \App\Models\Order::query()
            ->with(['payments', 'items'])
            ->findOrFail($order);

        $payment = $orderModel->payments()
            ->where('gateway', $gateway->name())
            ->latest('id')
            ->firstOrFail();

        if (
            $orderModel->payment_status === 'paid'
            && $payment->status === 'paid'
        ) {
            return $this->finish(
                $request,
                $orderModel,
                'پرداخت قبلاً تأیید شده است.'
            );
        }

        $authority = trim((string) $request->query('Authority', ''));
        $status = strtoupper(
            trim((string) $request->query('Status', ''))
        );

        if (
            blank($authority)
            || ! hash_equals(
                (string) $payment->authority,
                $authority
            )
        ) {
            return $this->pending(
                $request,
                $orderModel,
                'اطلاعات بازگشت از درگاه معتبر نیست؛ وضعیت سفارش برای بررسی بعدی حفظ شد.'
            );
        }

        if ($status !== 'OK') {
            return $this->pending(
                $request,
                $orderModel,
                'درگاه تکمیل پرداخت را تأیید نکرد؛ سفارش تا مشخص‌شدن نتیجه در انتظار باقی ماند.'
            );
        }

        try {
            $verified = $gateway->verify($payment);
        } catch (Throwable $e) {
            report($e);

            return $this->pending(
                $request,
                $orderModel,
                'ارتباط یا تأیید درگاه موقتاً ناموفق بود؛ موجودی و سفارش حفظ شده‌اند. وضعیت پرداخت را دوباره بررسی کنید.'
            );
        }

        if ($verified->status !== 'paid') {
            return $this->pending(
                $request,
                $orderModel,
                'درگاه هنوز پرداخت را تأیید نکرده است؛ سفارش برای بررسی بعدی در انتظار باقی ماند.'
            );
        }

        try {
            $payments->markPaid(
                $orderModel,
                $gateway->name(),
                $verified->reference_number
            );

            $orders->updateStatus(
                $orderModel->fresh(),
                'confirmed',
                'paid'
            );
        } catch (Throwable $e) {
            report($e);

            return $this->finish(
                $request,
                $orderModel->fresh(),
                'پرداخت توسط درگاه تأیید شد؛ ثبت نهایی سفارش نیاز به تکمیل دارد.'
            );
        }

        return $this->finish(
            $request,
            $orderModel->fresh(),
            'پرداخت با موفقیت تأیید شد.'
        );
    }

    private function pending(
        Request $request,
        \App\Models\Order $order,
        string $message
    ): RedirectResponse {
        $request->session()->put('completed_order', [
            'orderNumber' => $order->order_number,
            'total' => (float) $order->total,
            'orderStatus' => $order->status,
            'paymentStatus' => $order->payment_status,
            'paymentMethod' => $order->payment_method,
        ]);

        return redirect()
            ->route('checkout.success')
            ->with('warning', $message);
    }

    private function finish(
        Request $request,
        \App\Models\Order $order,
        string $message
    ): RedirectResponse {
        $request->session()->put(
            'completed_order',
            [
                'orderNumber' => $order->order_number,
                'total' => (float) $order->total,
                'orderStatus' => $order->status,
                'paymentStatus' => $order->payment_status,
                'paymentMethod' => $order->payment_method,
            ]
        );

        return redirect()
            ->route('checkout.success')
            ->with('success', $message);
    }
}
