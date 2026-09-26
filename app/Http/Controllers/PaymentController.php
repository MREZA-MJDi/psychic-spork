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
            return $this->cancel(
                $request,
                $orderModel,
                $orders,
                $cart,
                'اطلاعات بازگشت از درگاه معتبر نیست.'
            );
        }

        if ($status !== 'OK') {
            return $this->cancel(
                $request,
                $orderModel,
                $orders,
                $cart,
                'پرداخت توسط شما تکمیل نشد.'
            );
        }

        try {
            $verified = $gateway->verify($payment);
        } catch (Throwable $e) {
            report($e);

            return $this->cancel(
                $request,
                $orderModel,
                $orders,
                $cart,
                'تأیید پرداخت انجام نشد؛ سفارش به وضعیت ناموفق برگشت.'
            );
        }

        if ($verified->status !== 'paid') {
            return $this->cancel(
                $request,
                $orderModel,
                $orders,
                $cart,
                'پرداخت توسط درگاه تأیید نشد.'
            );
        }

        try {
            $orders->updateStatus(
                $orderModel,
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

    private function cancel(
        Request $request,
        \App\Models\Order $order,
        OrderService $orders,
        CartService $cart,
        string $message
    ): RedirectResponse {
        try {
            if (
                $order->status !== 'cancelled'
                && $order->payment_status !== 'paid'
            ) {
                $orders->updateStatus(
                    $order,
                    'cancelled',
                    'failed',
                    $message
                );

                $cart->restoreFromOrder(
                    $cart->current($request),
                    $order
                );
            }
        } catch (Throwable $e) {
            report($e);
        }

        return redirect()
            ->route('cart')
            ->with('error', $message);
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
            ]
        );

        return redirect()
            ->route('checkout.success')
            ->with('success', $message);
    }
}
