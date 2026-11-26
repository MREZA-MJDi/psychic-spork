<?php

namespace App\Http\Controllers;

use App\Http\Requests\CartStoreRequest;
use App\Http\Requests\CartUpdateRequest;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\User;
use App\Models\ProductVariant;
use App\Services\CartService;
use App\Services\WholesalePricingService;
use Illuminate\Support\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class CartController extends Controller
{
    public function __construct(
        private readonly WholesalePricingService $wholesalePricing
    ) {
    }
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

        $pricing = $this->calculatePricing(
            $current,
            $items,
            $packLines,
            $request->user()
        );

        return view('pages.cart', [
            'items' => $items,
            'packLines' => $packLines,
            'packTotal' => $pricing['packTotal'],
            'total' => $pricing['total'],
            'retailTotal' => $pricing['retailTotal'],
            'wholesaleTotal' => $pricing['wholesaleTotal'],
            'wholesaleUnavailableReason' => $pricing['wholesaleUnavailableReason'],
            'hasWholesalePacks' => $pricing['hasWholesalePacks'],
        ]);
    }

    public function summary(
        Request $request,
        CartService $cart
    ): JsonResponse {
        return response()->json(
            $this->payload($cart->current($request), $cart, $request->user())
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
                    $this->payload($current, $cart, $request->user())
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
                    $this->payload($current, $cart, $request->user())
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
                    $this->payload($current, $cart, $request->user())
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

    private function payload(Cart $cartModel, CartService $cart, ?User $user): array
    {
        $items = $cart->items($cartModel);
        $packLines = $cartModel->wholesalePacks()
            ->with('pack')
            ->get();

        $pricing = $this->calculatePricing($cartModel, $items, $packLines, $user);
        $packCount = (int) $packLines->sum(
            fn ($line) => (int) ($line->pack?->pack_quantity ?? 0) * (int) $line->quantity
        );

        return [
            'count' => (int) $items->sum('quantity') + $packCount,
            'total' => $pricing['total'],
            'retail_total' => $pricing['retailTotal'],
            'wholesale_total' => $pricing['wholesaleTotal'],
            'wholesale_available' => $pricing['wholesaleTotal'] !== null,
            'wholesale_unavailable_reason' => $pricing['wholesaleUnavailableReason'],
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
                    'variant_name' => $item['variant']->customer_label,
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

    private function calculatePricing(
        Cart $cart,
        Collection $items,
        Collection $packLines,
        ?User $user
    ): array {
        $hasWholesalePacks = $packLines->isNotEmpty();
        $packTotal = (float) $packLines->sum(
            fn ($line) => (float) ($line->pack?->pack_price ?? 0) * (int) $line->quantity
        );
        $retailTotal = (float) $items->sum('line_total') + $packTotal;

        $wholesaleTotal = null;
        $wholesaleUnavailableReason = null;
        $total = $retailTotal;

        if ($hasWholesalePacks) {
            try {
                $quote = $this->wholesalePricing->quote($cart, $user);
                $wholesaleTotal = (float) $quote['subtotal'];
                $total = $wholesaleTotal;
            } catch (\Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $exception) {
                $wholesaleUnavailableReason = $exception->getMessage();
            }
        }

        return [
            'hasWholesalePacks' => $hasWholesalePacks,
            'packTotal' => $packTotal,
            'retailTotal' => $retailTotal,
            'wholesaleTotal' => $wholesaleTotal,
            'wholesaleUnavailableReason' => $wholesaleUnavailableReason,
            'total' => $total,
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
