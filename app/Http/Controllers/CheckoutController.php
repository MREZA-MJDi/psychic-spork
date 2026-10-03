<?php

namespace App\Http\Controllers;

use App\Services\PaymentMethodManager;
use App\Http\Requests\CheckoutRequest;
use App\Services\CartService;
use App\Services\OrderService;
use App\Services\WholesalePricingService;
use Illuminate\Cache\LockTimeoutException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;
use Throwable;

class CheckoutController extends Controller
{
    public function create(
        Request $request,
        CartService $cart
    ): View|RedirectResponse {
        $current = $cart->current($request);
        $items = $cart->items($current);

        if ($items->isEmpty()) {
            return redirect()
                ->route('cart')
                ->with(
                    'error',
                    'سبد خرید شما خالی است.'
                );
        }

        $user = $request->user();
        $chequePermission = $user?->chequePermission()->first();
        $canSelectWholesale = ! $user || $user->isCustomer();

        $chequeEnabled = (bool) (
            $user?->isCustomer()
            && $chequePermission?->isApproved()
        );

        return view('pages.checkout', [
            'items' => $items,
            'total' => (float) $items->sum('line_total'),
            'chequeEnabled' => $chequeEnabled,
            'canSelectWholesale' => $canSelectWholesale,
            'chequeMaxOrderAmount' => $chequePermission?->max_order_amount,
        ]);
    }

    public function store(
        CheckoutRequest $request,
        OrderService $orders,
        CartService $cart,
        PaymentMethodManager $paymentMethods,
        WholesalePricingService $wholesalePricing
    ): RedirectResponse {
        $lockKey = $request->user()
            ? 'janan:checkout:user:' . $request->user()->id
            : 'janan:checkout:session:' . $request->session()->getId();

        try {
            return Cache::lock($lockKey, 30)->block(
                10,
                function () use (
                    $request,
                    $orders,
                    $cart,
                    $paymentMethods,
                    $wholesalePricing
                ): RedirectResponse {
                    $order = null;

                    try {
                        $checkoutData = $request->validated();
                        $currentCart = $cart->current($request);

                        if ($checkoutData['order_type'] === 'wholesale') {
                            $quote = $wholesalePricing->quote(
                                $currentCart,
                                $request->user()
                            );

                            $checkoutData['checkout_total'] = (float) $quote['subtotal'];
                        } else {
                            $checkoutData['checkout_total'] = (float) $cart
                                ->items($currentCart)
                                ->sum('line_total');
                        }

                        $paymentMethods->validateCheckout(
                            $checkoutData['payment_method'],
                            $request->user(),
                            $checkoutData
                        );
                        $order = $orders->createFromCart(
                            $currentCart,
                            $checkoutData,
                            $request->user()
                        );

                        $payment = $paymentMethods->start(
                            $checkoutData['payment_method'],
                            $order,
                            $request->user(),
                            $checkoutData
                        );

                        $redirectUrl = data_get(
                            $payment->metadata,
                            'redirect_url'
                        );

                        if (filled($redirectUrl)) {
                            return redirect()->away($redirectUrl);
                        }

                        $request->session()->put('completed_order', [
                            'orderNumber' => $order->order_number,
                            'total' => (float) $order->total,
                            'orderStatus' => $order->status,
                            'paymentStatus' => $order->payment_status,
                        ]);

                        return redirect()
                            ->route('checkout.success')
                            ->with(
                                'success',
                                'درخواست پرداخت چکی ثبت شد و در انتظار بررسی مدیریت است.'
                            );
                    } catch (Throwable $e) {
                        if ($order) {
                            try {
                                if (
                                    $order->status !== 'cancelled'
                                    && $order->payment_status !== 'paid'
                                ) {
                                    $orders->updateStatus(
                                        $order,
                                        'cancelled',
                                        'failed',
                                        'ایجاد تراکنش پرداخت ناموفق بود.'
                                    );

                                    $cart->restoreFromOrder(
                                        $cart->current($request),
                                        $order
                                    );
                                }
                            } catch (Throwable $rollbackError) {
                                report($rollbackError);
                            }
                        }

                        throw $e;
                    }
                }
            );
        } catch (LockTimeoutException $e) {
            report($e);

            return back()
                ->withInput()
                ->with(
                    'error',
                    'درخواست پرداخت دیگری برای این سبد در حال پردازش است.'
                );
        } catch (Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->with(
                    'error',
                    $this->message(
                        $e,
                        'شروع فرآیند پرداخت انجام نشد.'
                    )
                );
        }
    }

    public function success(
        Request $request
    ): View|RedirectResponse {
        $completed = $request->session()->pull(
            'completed_order'
        );

        if (! $completed) {
            return redirect()->route('home');
        }

        return view(
            'pages.checkout-success',
            $completed
        );
    }

    private function message(
        Throwable $e,
        string $fallback
    ): string {
        return $e instanceof \Symfony\Component\HttpKernel\Exception\HttpException
        && $e->getStatusCode() >= 400
        && $e->getStatusCode() < 500
        && filled($e->getMessage())
            ? $e->getMessage()
            : $fallback;
    }
}
