<?php

namespace App\Http\Controllers;

use App\Models\CartWholesalePack;
use App\Models\WholesalePack;
use App\Services\CartService;
use App\Services\WholesalePackCartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Throwable;

class WholesalePackCartController extends Controller
{
    public function store(
        Request $request,
        WholesalePack $wholesalePack,
        CartService $cart,
        WholesalePackCartService $packs
    ): RedirectResponse {
        try {
            $data = $request->validate([
                'quantity' => ['nullable', 'integer', 'min:1', 'max:99'],
            ]);

            $packs->add(
                $cart->current($request),
                $wholesalePack,
                (int) ($data['quantity'] ?? 1)
            );

            return redirect()->route('cart')->with('success', 'پک عمده به سبد خرید اضافه شد.');
        } catch (Throwable $exception) {
            report($exception);

            return back()->withInput()->with(
                'error',
                $exception instanceof \Symfony\Component\HttpKernel\Exception\HttpException
                    && $exception->getStatusCode() < 500
                    ? $exception->getMessage()
                    : 'افزودن پک به سبد خرید انجام نشد.'
            );
        }
    }

    public function update(
        Request $request,
        CartWholesalePack $cartWholesalePack,
        CartService $cart,
        WholesalePackCartService $packs
    ): RedirectResponse {
        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:99'],
        ]);

        $packs->update(
            $cart->current($request),
            $cartWholesalePack,
            (int) $data['quantity']
        );

        return back()->with('success', 'تعداد پک در سبد خرید به‌روزرسانی شد.');
    }

    public function destroy(
        Request $request,
        CartWholesalePack $cartWholesalePack,
        CartService $cart,
        WholesalePackCartService $packs
    ): RedirectResponse {
        $packs->remove($cart->current($request), $cartWholesalePack);

        return back()->with('success', 'پک از سبد خرید حذف شد.');
    }
}
