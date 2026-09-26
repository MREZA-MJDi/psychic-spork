<?php

namespace App\Http\Controllers;

use App\Contracts\PaymentGateway;
use App\Http\Requests\CheckoutRequest;
use App\Services\CartService;
use App\Services\OrderService;
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

        return view('pages.checkout', [
            'items' => $items,
            'total' => (float) $items->sum('line_total'),
        ]);
    }

    public function store(
        CheckoutRequest $request,
        OrderService $orders,
        CartService $cart,
        PaymentGateway $gateway
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
                    $gateway
                ): RedirectResponse {
                    $order = null;

                    try {
                        $order = $orders->createFromCart(
                            $cart->current($request),
                            $request->validated(),
                            $request->user()
                        );

                        $payment = $gateway->purchase($order);

                        $redirectUrl = data_get(
                            $payment->metadata,
                            'redirect_url'
                        );

                        abort_if(
                            blank($redirectUrl),
                            500,
                            'آدرس انتقال به درگاه ایجاد نشد.'
                        );

                        return redirect()->away($redirectUrl);
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
