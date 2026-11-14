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
    public const MAX_ITEMS = 100;

    public const MAX_QUANTITY_PER_ITEM = 99;

    public function current(Request $request): Cart
    {
        if ($request->user()) {
            return Cart::query()->createOrFirst(
                ['user_id' => $request->user()->id],
                ['last_activity_at' => now()]
            );
        }

        $sessionId = $request->session()->getId();

        return Cart::query()->createOrFirst(
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
                $cart = Cart::query()->createOrFirst(
                    ['user_id' => $user->id],
                    ['last_activity_at' => now()],
                );

                $cart = Cart::query()
                    ->lockForUpdate()
                    ->findOrFail($cart->id);
            }

            if (! $guest || $guest->id === $cart->id) {
                $cart->update([
                    'last_activity_at' => now(),
                ]);

                return $cart->fresh();
            }

            $cartItemCount = (int) $cart->items()->count();

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
                    self::MAX_QUANTITY_PER_ITEM,
                    (int) ($existing?->quantity ?? 0) + (int) $guestItem->quantity
                );

                if ($quantity < 1) {
                    continue;
                }

                if ($existing) {
                    $existing->update([
                        'quantity' => $quantity,
                    ]);
                } else {
                    if ($cartItemCount >= self::MAX_ITEMS) {
                        continue;
                    }

                    $cart->items()->create([
                        'product_variant_id' => $variant->id,
                        'quantity' => $quantity,
                    ]);
                    $cartItemCount++;
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
                'productVariant.product.primaryGalleryMedia',
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
                    (int) $variant->stock,
                    self::MAX_QUANTITY_PER_ITEM
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
                    'image' => $product->primaryGalleryMedia?->url,
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

        abort_if(
            $quantity > self::MAX_QUANTITY_PER_ITEM,
            422,
            'تعداد هر تنوع نمی‌تواند بیشتر از ' . self::MAX_QUANTITY_PER_ITEM . ' عدد باشد.'
        );

        DB::transaction(function () use (
            $cart,
            $variant,
            $quantity
        ): void {
            // Serialize writes per cart so parallel requests cannot exceed
            // the maximum number of distinct cart lines.
            $lockedCart = Cart::query()
                ->lockForUpdate()
                ->findOrFail($cart->id);

            $variant = ProductVariant::query()
                ->with('product')
                ->lockForUpdate()
                ->find($variant->id);

            $this->assertPurchasable($variant);

            $existing = $lockedCart->items()
                ->where('product_variant_id', $variant->id)
                ->lockForUpdate()
                ->first();

            $newQuantity = (int) ($existing?->quantity ?? 0) + $quantity;

            abort_if(
                $newQuantity > self::MAX_QUANTITY_PER_ITEM,
                422,
                'تعداد هر تنوع نمی‌تواند بیشتر از ' . self::MAX_QUANTITY_PER_ITEM . ' عدد باشد.'
            );

            abort_if(
                $newQuantity > $variant->stock,
                422,
                "حداکثر تعداد قابل سفارش از «{$variant->product->name}» {$variant->stock} عدد است."
            );

            if ($existing) {
                $existing->update(['quantity' => $newQuantity]);
            } else {
                abort_if(
                    $lockedCart->items()->count() >= self::MAX_ITEMS,
                    422,
                    'سبد خرید نمی‌تواند بیشتر از ' . self::MAX_ITEMS . ' تنوع متفاوت داشته باشد.'
                );

                $lockedCart->items()->create([
                    'product_variant_id' => $variant->id,
                    'quantity' => $quantity,
                ]);
            }

            $lockedCart->update(['last_activity_at' => now()]);
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

        abort_if(
            $quantity < 1 || $quantity > self::MAX_QUANTITY_PER_ITEM,
            422,
            'تعداد هر تنوع باید بین ۱ تا ' . self::MAX_QUANTITY_PER_ITEM . ' عدد باشد.'
        );

        DB::transaction(function () use (
            $cart,
            $item,
            $quantity
        ): void {
            $lockedCart = Cart::query()
                ->lockForUpdate()
                ->findOrFail($cart->id);

            $lockedItem = $lockedCart->items()
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

            $lockedItem->update(['quantity' => $quantity]);
            $lockedCart->update(['last_activity_at' => now()]);
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
            $lockedCart = Cart::query()
                ->lockForUpdate()
                ->findOrFail($cart->id);

            $lockedCart->items()->delete();
            $lockedCart->update(['last_activity_at' => now()]);
        });
    }

    public function restoreFromOrder(
        Cart $cart,
        Order $order
    ): void {
        DB::transaction(function () use ($cart, $order): void {
            $order->load('items');

            $cartItemCount = (int) $cart->items()->count();

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
                    self::MAX_QUANTITY_PER_ITEM,
                    (int) ($existing?->quantity ?? 0) + (int) $orderItem->quantity
                );

                if ($quantity < 1) {
                    continue;
                }

                if ($existing) {
                    $existing->update([
                        'quantity' => $quantity,
                    ]);
                } else {
                    if ($cartItemCount >= self::MAX_ITEMS) {
                        continue;
                    }

                    $cart->items()->create([
                        'product_variant_id' => $variant->id,
                        'quantity' => $quantity,
                    ]);
                    $cartItemCount++;
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
