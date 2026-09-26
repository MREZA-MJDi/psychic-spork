<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class CartService
{
    public function current(Request $request): Cart
    {
        if ($request->user()) {
            return Cart::query()->firstOrCreate(
                ['user_id' => $request->user()->id],
                ['last_activity_at' => now()]
            );
        }

        $sessionId = $request->session()->getId();

        return Cart::query()->firstOrCreate(
            ['session_id' => $sessionId],
            ['last_activity_at' => now()]
        );
    }

    public function mergeGuestIntoUser(
        Request $request,
        User $user
    ): Cart {
        return DB::transaction(function () use ($request, $user): Cart {
            $sessionId = $request->session()->getId();

            $guest = Cart::query()
                ->whereNull('user_id')
                ->where('session_id', $sessionId)
                ->with('items')
                ->lockForUpdate()
                ->first();

            $cart = Cart::query()
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->first();

            if (! $cart) {
                $cart = Cart::query()->create([
                    'user_id' => $user->id,
                    'last_activity_at' => now(),
                ]);
            }

            if (! $guest || $guest->id === $cart->id) {
                $cart->update([
                    'last_activity_at' => now(),
                ]);

                return $cart->fresh();
            }

            foreach ($guest->items as $guestItem) {
                $variant = ProductVariant::query()
                    ->with('product')
                    ->lockForUpdate()
                    ->whereKey($guestItem->product_variant_id)
                    ->where('is_active', true)
                    ->first();

                if (
                    ! $variant
                    || ! $variant->product?->is_active
                    || $variant->stock < 1
                ) {
                    continue;
                }

                $existing = $cart->items()
                    ->where(
                        'product_variant_id',
                        $variant->id
                    )
                    ->lockForUpdate()
                    ->first();

                $quantity = min(
                    (int) $variant->stock,
                    (int) ($existing?->quantity ?? 0)
                    + (int) $guestItem->quantity
                );

                if ($quantity < 1) {
                    continue;
                }

                if ($existing) {
                    $existing->update([
                        'quantity' => $quantity,
                    ]);
                } else {
                    $cart->items()->create([
                        'product_variant_id' => $variant->id,
                        'quantity' => $quantity,
                    ]);
                }
            }

            $guest->items()->delete();
            $guest->delete();

            $cart->update([
                'last_activity_at' => now(),
            ]);

            return $cart->fresh();
        });
    }

    public function items(Cart $cart): Collection
    {
        $items = $cart->items()
            ->with([
                'productVariant.product.category',
                'productVariant.product.brand',
                'productVariant.product.galleryMedia',
            ])
            ->get();

        return $items
            ->filter(function (CartItem $item): bool {
                return $item->quantity > 0
                    && $item->productVariant?->is_active
                    && $item->productVariant?->product?->is_active;
            })
            ->map(function (CartItem $item): ?array {
                $variant = $item->productVariant;
                $product = $variant->product;

                $quantity = min(
                    (int) $item->quantity,
                    (int) $variant->stock
                );

                if ($quantity < 1) {
                    return null;
                }

                if ($quantity !== (int) $item->quantity) {
                    $item->update([
                        'quantity' => $quantity,
                    ]);
                }

                $unitPrice = (float) $variant->effective_price;

                return [
                    'item' => $item,
                    'id' => $item->id,
                    'product' => $product,
                    'variant' => $variant,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'line_total' => $quantity * $unitPrice,
                    'image' => $product->galleryMedia->first()?->url,
                ];
            })
            ->filter()
            ->values();
    }

    public function add(
        Cart $cart,
        ProductVariant $variant,
        int $quantity = 1
    ): void {
        abort_if(
            $quantity < 1,
            422,
            'تعداد محصول باید حداقل یک عدد باشد.'
        );

        DB::transaction(function () use (
            $cart,
            $variant,
            $quantity
        ): void {
            $variant = ProductVariant::query()
                ->with('product')
                ->lockForUpdate()
                ->find($variant->id);

            $this->assertPurchasable($variant);

            $existing = $cart->items()
                ->where(
                    'product_variant_id',
                    $variant->id
                )
                ->lockForUpdate()
                ->first();

            $newQuantity = (int) ($existing?->quantity ?? 0)
                + $quantity;

            abort_if(
                $newQuantity > $variant->stock,
                422,
                "حداکثر تعداد قابل سفارش از «{$variant->product->name}» {$variant->stock} عدد است."
            );

            if ($existing) {
                $existing->update([
                    'quantity' => $newQuantity,
                ]);
            } else {
                $cart->items()->create([
                    'product_variant_id' => $variant->id,
                    'quantity' => $quantity,
                ]);
            }

            $cart->update([
                'last_activity_at' => now(),
            ]);
        });
    }

    public function update(
        Cart $cart,
        CartItem $item,
        int $quantity
    ): void {
        $this->assertItemBelongsToCart($cart, $item);

        if ($quantity === 0) {
            $this->remove($cart, $item);

            return;
        }

        DB::transaction(function () use (
            $cart,
            $item,
            $quantity
        ): void {
            $lockedItem = $cart->items()
                ->whereKey($item->id)
                ->lockForUpdate()
                ->firstOrFail();

            $variant = ProductVariant::query()
                ->with('product')
                ->lockForUpdate()
                ->find($lockedItem->product_variant_id);

            $this->assertPurchasable($variant);

            abort_if(
                $quantity > $variant->stock,
                422,
                "حداکثر تعداد قابل سفارش از «{$variant->product->name}» {$variant->stock} عدد است."
            );

            $lockedItem->update([
                'quantity' => $quantity,
            ]);

            $cart->update([
                'last_activity_at' => now(),
            ]);
        });
    }

    public function remove(
        Cart $cart,
        CartItem $item
    ): void {
        $this->assertItemBelongsToCart($cart, $item);

        DB::transaction(function () use (
            $cart,
            $item
        ): void {
            $lockedItem = $cart->items()
                ->whereKey($item->id)
                ->lockForUpdate()
                ->first();

            if (! $lockedItem) {
                return;
            }

            $lockedItem->delete();

            $cart->update([
                'last_activity_at' => now(),
            ]);
        });
    }

    public function clear(Cart $cart): void
    {
        DB::transaction(function () use ($cart): void {
            $cart->items()->delete();

            $cart->update([
                'last_activity_at' => now(),
            ]);
        });
    }

    public function restoreFromOrder(
        Cart $cart,
        Order $order
    ): void {
        DB::transaction(function () use ($cart, $order): void {
            $order->load('items');

            foreach ($order->items as $orderItem) {
                $variant = ProductVariant::query()
                    ->lockForUpdate()
                    ->find($orderItem->product_variant_id);

                if (
                    ! $variant
                    || ! $variant->is_active
                    || $variant->stock < 1
                ) {
                    continue;
                }

                $existing = $cart->items()
                    ->where(
                        'product_variant_id',
                        $variant->id
                    )
                    ->lockForUpdate()
                    ->first();

                $quantity = min(
                    (int) $variant->stock,
                    (int) ($existing?->quantity ?? 0)
                    + (int) $orderItem->quantity
                );

                if ($quantity < 1) {
                    continue;
                }

                if ($existing) {
                    $existing->update([
                        'quantity' => $quantity,
                    ]);
                } else {
                    $cart->items()->create([
                        'product_variant_id' => $variant->id,
                        'quantity' => $quantity,
                    ]);
                }
            }

            $cart->update([
                'last_activity_at' => now(),
            ]);
        });
    }

    public function total(Cart $cart): float
    {
        return (float) $this->items($cart)
            ->sum('line_total');
    }

    private function assertPurchasable(
        ?ProductVariant $variant
    ): void {
        abort_unless(
            $variant
            && $variant->is_active
            && $variant->product?->is_active,
            404,
            'این گزینه محصول دیگر در دسترس نیست.'
        );

        abort_if(
            $variant->stock < 1,
            422,
            'این گزینه محصول در حال حاضر موجود نیست.'
        );
    }

    private function assertItemBelongsToCart(
        Cart $cart,
        CartItem $item
    ): void {
        abort_unless(
            (int) $item->cart_id === (int) $cart->id,
            404
        );
    }
}
