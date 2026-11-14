<?php

namespace App\Http\Controllers;

use App\Http\Requests\CartStoreRequest;
use App\Http\Requests\CartUpdateRequest;
use App\Models\CartItem;
use App\Models\ProductVariant;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
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
        $packLines = $current->wholesalePacks()
            ->with(['pack.items.variant.product.primaryGalleryMedia', 'pack.items.variant.primaryGalleryMedia'])
            ->latest('id')
            ->get();

        $packTotal = (float) $packLines->sum(
            fn ($line) => (float) $line->pack?->pack_price * (int) $line->quantity
        );

        return view('pages.cart', [
            'items' => $items,
            'packLines' => $packLines,
            'packTotal' => $packTotal,
            'total' => (float) $items->sum('line_total') + $packTotal,
        ]);
    }

    public function summary(
        Request $request,
        CartService $cart
    ): JsonResponse {
        return response()->json(
            $this->payload($cart->current($request), $cart)
        );
    }

    public function store(
        CartStoreRequest $request,
        ProductVariant $variant,
        CartService $cart
    ): JsonResponse|RedirectResponse {
        try {
            $data = $request->validated();
            $current = $cart->current($request);

            $cart->add(
                $current,
                $variant,
                (int) ($data['quantity'] ?? 1)
            );

            if ($request->expectsJson()) {
                return response()->json(
                    $this->payload($current, $cart)
                );
            }

            return back()->with('success', 'محصول به سبد خرید اضافه شد.');
        } catch (Throwable $e) {
            report($e);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $this->message(
                        $e,
                        'افزودن محصول به سبد انجام نشد.'
                    ),
                ], $this->status($e));
            }

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
    ): JsonResponse|RedirectResponse {
        try {
            $data = $request->validated();
            $current = $cart->current($request);

            $cart->update(
                $current,
                $item,
                (int) $data['quantity']
            );

            if ($request->expectsJson()) {
                return response()->json(
                    $this->payload($current, $cart)
                );
            }

            return back()->with(
                'success',
                'سبد خرید با موفقیت به‌روزرسانی شد.'
            );
        } catch (Throwable $e) {
            report($e);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $this->message(
                        $e,
                        'به‌روزرسانی سبد خرید انجام نشد.'
                    ),
                ], $this->status($e));
            }

            return back()->with(
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
    ): JsonResponse|RedirectResponse {
        try {
            $current = $cart->current($request);

            $cart->remove($current, $item);

            if ($request->expectsJson()) {
                return response()->json(
                    $this->payload($current, $cart)
                );
            }

            return back()->with(
                'success',
                'محصول از سبد خرید حذف شد.'
            );
        } catch (Throwable $e) {
            report($e);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $this->message(
                        $e,
                        'حذف محصول از سبد انجام نشد.'
                    ),
                ], $this->status($e));
            }

            return back()->with(
                'error',
                $this->message(
                    $e,
                    'حذف محصول از سبد انجام نشد.'
                )
            );
        }
    }

    private function payload($cartModel, CartService $cart): array
    {
        $items = $cart->items($cartModel);
        $packLines = $cartModel->wholesalePacks()
            ->with('pack')
            ->get();

        $packCount = (int) $packLines->sum(
            fn ($line) => (int) ($line->pack?->pack_quantity ?? 0) * (int) $line->quantity
        );
        $packTotal = (float) $packLines->sum(
            fn ($line) => (float) ($line->pack?->pack_price ?? 0) * (int) $line->quantity
        );

        return [
            'count' => (int) $items->sum('quantity') + $packCount,
            'total' => (float) $items->sum('line_total') + $packTotal,
            'packs' => $packLines->map(fn ($line): array => [
                'id' => $line->id,
                'name' => $line->pack?->name ?? 'پک عمده',
                'quantity' => (int) $line->quantity,
                'pack_quantity' => (int) ($line->pack?->pack_quantity ?? 0),
                'unit_price' => (float) ($line->pack?->pack_price ?? 0),
                'line_total' => (float) ($line->pack?->pack_price ?? 0) * (int) $line->quantity,
            ])->values()->all(),
            'items' => $items->map(function (array $item): array {
                return [
                    'id' => $item['id'],
                    'name' => $item['product']->name,
                    'variant_name' => $item['variant']->display_name,
                    'quantity' => (int) $item['quantity'],
                    'stock' => (int) $item['variant']->stock,
                    'unit_price' => (float) $item['unit_price'],
                    'line_total' => (float) $item['line_total'],
                    'image' => $item['image'],
                    'product_url' => route(
                        'products.show',
                        $item['product']
                    ),
                ];
            })->values()->all(),
        ];
    }

    private function status(Throwable $e): int
    {
        return $e instanceof \Symfony\Component\HttpKernel\Exception\HttpException
            ? $e->getStatusCode()
            : 422;
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
