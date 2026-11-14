<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\CartWholesalePack;
use App\Models\ProductVariant;
use App\Models\WholesalePack;
use Illuminate\Support\Facades\DB;

final class WholesalePackCartService
{
    public function add(Cart $cart, WholesalePack $pack, int $quantity = 1): CartWholesalePack
    {
        return $this->setQuantity($cart, $pack, null, $quantity, true);
    }

    public function update(Cart $cart, CartWholesalePack $line, int $quantity): CartWholesalePack
    {
        abort_unless((int) $line->cart_id === (int) $cart->id, 404);

        return $this->setQuantity($cart, null, $line, $quantity, false);
    }

    public function remove(Cart $cart, CartWholesalePack $line): void
    {
        abort_unless((int) $line->cart_id === (int) $cart->id, 404);

        DB::transaction(function () use ($cart, $line): void {
            Cart::query()->lockForUpdate()->findOrFail($cart->id);
            $locked = CartWholesalePack::query()
                ->where('cart_id', $cart->id)
                ->lockForUpdate()
                ->find($line->id);

            $locked?->delete();
            $cart->update(['last_activity_at' => now()]);
        });
    }

    private function setQuantity(
        Cart $cart,
        ?WholesalePack $pack,
        ?CartWholesalePack $line,
        int $quantity,
        bool $increment
    ): CartWholesalePack {
        abort_if(
            $quantity < 1 || $quantity > CartService::MAX_QUANTITY_PER_ITEM,
            422,
            'تعداد پک باید بین ۱ تا ' . CartService::MAX_QUANTITY_PER_ITEM . ' باشد.'
        );

        return DB::transaction(function () use ($cart, $pack, $line, $quantity, $increment): CartWholesalePack {
            $lockedCart = Cart::query()->lockForUpdate()->findOrFail($cart->id);

            $packId = $pack?->id ?? $line?->wholesale_pack_id;
            $lockedPack = WholesalePack::query()
                ->with(['items.variant.product'])
                ->whereKey($packId)
                ->lockForUpdate()
                ->firstOrFail();

            abort_unless($lockedPack->is_active, 422, 'این پک دیگر برای فروش فعال نیست.');
            abort_if($lockedPack->items->isEmpty(), 422, 'این پک هنوز اقلامی برای فروش ندارد.');
            abort_if((float) $lockedPack->pack_price <= 0, 422, 'قیمت این پک معتبر نیست.');

            $lockedLine = $lockedCart->wholesalePacks()
                ->where('wholesale_pack_id', $lockedPack->id)
                ->lockForUpdate()
                ->first();

            if ($line) {
                abort_unless($lockedLine && (int) $lockedLine->id === (int) $line->id, 404);
            }

            $targetQuantity = $increment
                ? (int) ($lockedLine?->quantity ?? 0) + $quantity
                : $quantity;

            abort_if(
                $targetQuantity > CartService::MAX_QUANTITY_PER_ITEM,
                422,
                'تعداد این پک از حد مجاز بیشتر است.'
            );

            $packLines = $lockedCart->wholesalePacks()
                ->with(['pack.items.variant.product'])
                ->lockForUpdate()
                ->get();

            $requirements = [];
            foreach ($lockedCart->items()->get() as $cartItem) {
                $requirements[$cartItem->product_variant_id] =
                    ($requirements[$cartItem->product_variant_id] ?? 0) + (int) $cartItem->quantity;
            }

            foreach ($packLines as $existingLine) {
                $existingPack = $existingLine->pack;
                if (! $existingPack) {
                    continue;
                }

                $lineQuantity = (int) $existingLine->quantity;
                if ((int) $existingLine->id === (int) $lockedLine?->id) {
                    $lineQuantity = $targetQuantity;
                }

                foreach ($existingPack->items as $item) {
                    $variantId = (int) $item->product_variant_id;
                    $requirements[$variantId] =
                        ($requirements[$variantId] ?? 0) + ((int) $item->quantity * $lineQuantity);
                }
            }

            if (! $lockedLine) {
                foreach ($lockedPack->items as $item) {
                    $variantId = (int) $item->product_variant_id;
                    $requirements[$variantId] =
                        ($requirements[$variantId] ?? 0) + ((int) $item->quantity * $targetQuantity);
                }
            }

            $variants = ProductVariant::query()
                ->with('product')
                ->whereIn('id', array_keys($requirements))
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($requirements as $variantId => $required) {
                $variant = $variants->get($variantId);

                abort_unless(
                    $variant
                    && $variant->is_active
                    && $variant->product?->is_active
                    && $variant->wholesale_price !== null,
                    422,
                    'یکی از اقلام پک دیگر برای فروش عمده فعال نیست.'
                );

                abort_if(
                    (int) $variant->stock < (int) $required,
                    422,
                    "موجودی «{$variant->product->name}» برای تعداد پک انتخاب‌شده کافی نیست."
                );
            }

            $line = $lockedLine ?? new CartWholesalePack([
                'cart_id' => $lockedCart->id,
                'wholesale_pack_id' => $lockedPack->id,
            ]);
            $line->quantity = $targetQuantity;
            $line->save();

            $lockedCart->update(['last_activity_at' => now()]);

            return $line->fresh(['pack']);
        }, 3);
    }
}
