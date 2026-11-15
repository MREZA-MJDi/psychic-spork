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
            $cartPackCount = (int) $cart->wholesalePacks()->count();

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
                    if ($cartItemCount + $cartPackCount >= self::MAX_ITEMS) {
                        continue;
                    }

                    $cart->items()->create([
                        'product_variant_id' => $variant->id,
                        'quantity' => $quantity,
                    ]);
                    $cartItemCount++;
                }
            }

            foreach ($guest->wholesalePacks()->get() as $guestPack) {
                $existingPack = $cart->wholesalePacks()
                    ->where('wholesale_pack_id', $guestPack->wholesale_pack_id)
                    ->lockForUpdate()
                    ->first();

                if ($existingPack) {
                    $existingPack->update([
                        'quantity' => min(
                            CartService::MAX_QUANTITY_PER_ITEM,
                            (int) $existingPack->quantity + (int) $guestPack->quantity
                        ),
                    ]);
                } else {
                    $cart->wholesalePacks()->create([
                        'wholesale_pack_id' => $guestPack->wholesale_pack_id,
                        'quantity' => min(CartService::MAX_QUANTITY_PER_ITEM, (int) $guestPack->quantity),
                    ]);
                }
            }

            $guest->items()->delete();
            $guest->wholesalePacks()->delete();
            $guest->delete();

            $cart->update([
                'last_activity_at' => now(),
            ]);

            return $cart->fresh();
        });
    }

    public function items(Cart $cart): Collection
    {
        $packRequirements = $this->wholesalePackRequirements($cart);
        $items = $cart->items()
            ->with([
                'productVariant.product.category',
                'productVariant.product.brand',
                'productVariant.product.primaryGalleryMedia',
                'productVariant.primaryGalleryMedia',
            ])
            ->get();

        return $items
            ->filter(function (CartItem $item): bool {
                return $item->quantity > 0
                    && $item->productVariant?->is_active
                    && $item->productVariant?->product?->is_active;
            })
            ->map(function (CartItem $item) use ($packRequirements): ?array {
                $variant = $item->productVariant;
                $product = $variant->product;

                $availableStock = max(
                    0,
                    (int) $variant->stock - (int) ($packRequirements[$variant->id] ?? 0)
                );
                $quantity = min(
                    (int) $item->quantity,
                    $availableStock,
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
                    'image' => $product->primaryGalleryMedia?->url ?: $variant->primaryGalleryMedia?->url,
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

            $reservedByPacks = $this->wholesalePackRequirements($lockedCart)[$variant->id] ?? 0;
            $availableStock = max(0, (int) $variant->stock - (int) $reservedByPacks);

            abort_if(
                $newQuantity > $availableStock,
                422,
                "با احتساب پک‌های سبد، حداکثر تعداد قابل سفارش از «{$variant->product->name}» {$availableStock} عدد است."
            );

            if ($existing) {
                $existing->update(['quantity' => $newQuantity]);
            } else {
                abort_if(
                    ($lockedCart->items()->count() + $lockedCart->wholesalePacks()->count()) >= self::MAX_ITEMS,
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

            $reservedByPacks = $this->wholesalePackRequirements($lockedCart)[$variant->id] ?? 0;
            $availableStock = max(0, (int) $variant->stock - (int) $reservedByPacks);

            abort_if(
                $quantity > $availableStock,
                422,
                "با احتساب پک‌های سبد، حداکثر تعداد قابل سفارش از «{$variant->product->name}» {$availableStock} عدد است."
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
            $lockedCart->wholesalePacks()->delete();
            $lockedCart->update(['last_activity_at' => now()]);
        });
    }

    public function restoreFromOrder(
        Cart $cart,
        Order $order
    ): void {
        DB::transaction(function () use ($cart, $order): void {
            $order->load(['items', 'wholesalePacks']);

            $cartItemCount = (int) $cart->items()->count();
            $cartPackCount = (int) $cart->wholesalePacks()->count();

            foreach ($order->items->whereNull('wholesale_pack_order_id') as $orderItem) {
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
                    ->where('product_variant_id', $variant->id)
                    ->lockForUpdate()
                    ->first();

                $reservedByPacks = $this->wholesalePackRequirements($cart)[$variant->id] ?? 0;
                $availableStock = max(0, (int) $variant->stock - (int) $reservedByPacks);

                $quantity = min(
                    (int) $variant->stock,
                    $availableStock,
                    self::MAX_QUANTITY_PER_ITEM,
                    (int) ($existing?->quantity ?? 0) + (int) $orderItem->quantity
                );

                if ($quantity < 1) {
                    continue;
                }

                if ($existing) {
                    $existing->update(['quantity' => $quantity]);
                } else {
                    if ($cartItemCount + $cartPackCount >= self::MAX_ITEMS) {
                        continue;
                    }

                    $cart->items()->create([
                        'product_variant_id' => $variant->id,
                        'quantity' => $quantity,
                    ]);
                    $cartItemCount++;
                }
            }

            foreach ($order->wholesalePacks as $orderPack) {
                if (! $orderPack->wholesale_pack_id) {
                    continue;
                }

                $existingPack = $cart->wholesalePacks()
                    ->where('wholesale_pack_id', $orderPack->wholesale_pack_id)
                    ->lockForUpdate()
                    ->first();

                $quantity = min(
                    self::MAX_QUANTITY_PER_ITEM,
                    (int) ($existingPack?->quantity ?? 0) + (int) $orderPack->quantity
                );

                if ($existingPack) {
                    $existingPack->update(['quantity' => $quantity]);
                } else {
                    $cart->wholesalePacks()->create([
                        'wholesale_pack_id' => $orderPack->wholesale_pack_id,
                        'quantity' => $quantity,
                    ]);
                }
            }

            $cart->update(['last_activity_at' => now()]);
        });
    }

    public function total(Cart $cart): float
    {
        $itemTotal = (float) $this->items($cart)->sum('line_total');
        $packTotal = (float) $cart->wholesalePacks()
            ->with('pack')
            ->get()
            ->sum(fn ($line) => (float) ($line->pack?->pack_price ?? 0) * (int) $line->quantity);

        return $itemTotal + $packTotal;
    }

    /**
     * Quantity of each variant currently required by the selected wholesale
     * packs in this cart. Normal product lines must not consume the same stock
     * a second time.
     *
     * @return array<int, int>
     */
    private function wholesalePackRequirements(Cart $cart): array
    {
        $requirements = [];

        $lines = $cart->wholesalePacks()
            ->with('pack.items')
            ->get();

        foreach ($lines as $line) {
            foreach ($line->pack?->items ?? [] as $item) {
                $variantId = (int) $item->product_variant_id;
                $requirements[$variantId] = ($requirements[$variantId] ?? 0)
                    + ((int) $item->quantity * (int) $line->quantity);
            }
        }

        return $requirements;
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
