<?php

namespace App\Http\Controllers;

use App\Http\Requests\CartStoreRequest;
use App\Http\Requests\CartUpdateRequest;
use App\Models\CartItem;
use App\Models\ProductVariant;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class CartController extends Controller
{
    public function index(
        Request $request,
        CartService $cart
    ): View {
        $current = $cart->current($request);
        $items = $cart->items($current);

        return view('pages.cart', [
            'items' => $items,
            'total' => (float) $items->sum('line_total'),
        ]);
    }

    public function store(
        CartStoreRequest $request,
        ProductVariant $variant,
        CartService $cart
    ): RedirectResponse {
        try {
            $data = $request->validated();

            $cart->add(
                $cart->current($request),
                $variant,
                (int) ($data['quantity'] ?? 1)
            );

            return back()->with(
                'success',
                'محصول به سبد خرید اضافه شد.'
            );
        } catch (Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->with(
                    'error',
                    $this->message(
                        $e,
                        'افزودن محصول به سبد انجام نشد.'
                    )
                );
        }
    }

    public function update(
        CartUpdateRequest $request,
        CartItem $item,
        CartService $cart
    ): RedirectResponse {
        try {
            $data = $request->validated();

            $cart->update(
                $cart->current($request),
                $item,
                (int) $data['quantity']
            );

            return back()->with(
                'success',
                'سبد خرید با موفقیت به‌روزرسانی شد.'
            );
        } catch (Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->with(
                    'error',
                    $this->message(
                        $e,
                        'به‌روزرسانی سبد خرید انجام نشد.'
                    )
                );
        }
    }

    public function remove(
        Request $request,
        CartItem $item,
        CartService $cart
    ): RedirectResponse {
        try {
            $cart->remove(
                $cart->current($request),
                $item
            );

            return back()->with(
                'success',
                'محصول از سبد خرید حذف شد.'
            );
        } catch (Throwable $e) {
            report($e);

            return back()->with(
                'error',
                $this->message(
                    $e,
                    'حذف محصول از سبد انجام نشد.'
                )
            );
        }
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
