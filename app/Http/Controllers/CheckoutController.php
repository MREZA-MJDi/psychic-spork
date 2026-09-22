<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest;
use App\Services\CartService;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
        CartService $cart
    ): RedirectResponse {
        try {
            $order = $orders->createFromCart(
                $cart->current($request),
                $request->validated(),
                $request->user()
            );

            if ($request->user()) {
                return redirect()
                    ->route('account')
                    ->with(
                        'success',
                        "سفارش {$order->order_number} با موفقیت ثبت شد."
                    );
            }

            $request->session()->put(
                'completed_order',
                [
                    'orderNumber' => $order->order_number,
                    'total' => (float) $order->total,
                ]
            );

            return redirect()
                ->route('checkout.success')
                ->with(
                    'success',
                    'سفارش شما با موفقیت ثبت شد.'
                );
        } catch (Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->with(
                    'error',
                    $this->message(
                        $e,
                        'ثبت سفارش انجام نشد.'
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
