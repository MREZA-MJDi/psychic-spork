<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\ProductVariant;
use App\Models\User;

final class WholesalePricingService
{
    public function __construct(
        private readonly WholesaleEligibilityService $eligibility,
    ) {
    }

    public function quote(
        Cart $cart,
        ?User $user = null
    ): array {
        abort_if(
            $user && ! $user->isCustomer(),
            403,
            'حساب مدیریت برای ثبت سفارش مشتری استفاده نمی‌شود.'
        );

        $profile = $user?->wholesaleProfile()->first();

        $items = $cart->items()
            ->with(['productVariant.product'])
            ->orderBy('product_variant_id')
            ->get();

        $packs = $cart->wholesalePacks()
            ->with(['pack.items.variant.product'])
            ->orderBy('id')
            ->get();

        abort_if(
            $items->isEmpty() && $packs->isEmpty(),
            422,
            'سبد خرید خالی است.'
        );

        $subtotal = 0.0;
        $quantity = 0;
        $requiredStock = [];

        foreach ($items as $cartItem) {
            $variant = $cartItem->productVariant;

            abort_unless(
                $variant
                && $variant->is_active
                && $variant->product?->is_active,
                422,
                'یکی از محصولات سبد دیگر قابل سفارش نیست.'
            );

            $count = (int) $cartItem->quantity;

            abort_if(
                $count < 1,
                422,
                'تعداد یکی از محصولات سبد خرید نامعتبر است.'
            );

            $unitPrice = $this->unitPrice($variant);

            $quantity += $count;
            $subtotal += $unitPrice * $count;
            $requiredStock[$variant->id] = ($requiredStock[$variant->id] ?? 0) + $count;
        }

        $packDiscount = 0.0;

        foreach ($packs as $line) {
            $pack = $line->pack;
            $packCount = (int) $line->quantity;

            abort_unless(
                $pack && $pack->is_active,
                422,
                'یکی از پک‌های سبد دیگر برای فروش فعال نیست.'
            );

            abort_if(
                $packCount < 1 || $packCount > CartService::MAX_QUANTITY_PER_ITEM,
                422,
                'تعداد یکی از پک‌های سبد معتبر نیست.'
            );

            abort_if(
                $pack->items->isEmpty(),
                422,
                "پک «{$pack->name}» هنوز اقلام قابل فروش ندارد."
            );

            $packBasePrice = 0.0;
            $packUnitCount = 0;

            foreach ($pack->items as $packItem) {
                $variant = $packItem->variant;
                $itemQuantity = (int) $packItem->quantity;

                abort_unless(
                    $variant
                    && $variant->is_active
                    && $variant->product?->is_active,
                    422,
                    "یکی از اقلام پک «{$pack->name}» دیگر فعال نیست."
                );

                abort_if($itemQuantity < 1, 422, "تعداد یکی از اقلام پک «{$pack->name}» نامعتبر است.");

                $unitPrice = $this->unitPrice($variant);
                $packBasePrice += $unitPrice * $itemQuantity;
                $packUnitCount += $itemQuantity;

                $requiredStock[$variant->id] = ($requiredStock[$variant->id] ?? 0)
                    + ($itemQuantity * $packCount);
            }

            $packPrice = (float) $pack->pack_price;

            abort_if(
                $packPrice <= 0 || $packPrice > $packBasePrice,
                422,
                "قیمت پک «{$pack->name}» معتبر نیست؛ قیمت پک نباید از جمع قیمت عمده اقلام بیشتر باشد."
            );

            $subtotal += $packPrice * $packCount;
            $packDiscount += ($packBasePrice - $packPrice) * $packCount;
            $quantity += $packUnitCount * $packCount;
        }

        if ($requiredStock !== []) {
            $variants = ProductVariant::query()
                ->with('product')
                ->whereIn('id', array_keys($requiredStock))
                ->get()
                ->keyBy('id');

            foreach ($requiredStock as $variantId => $required) {
                $variant = $variants->get($variantId);

                abort_unless(
                    $variant
                    && $variant->is_active
                    && $variant->product?->is_active,
                    422,
                    'یکی از اقلام سبد دیگر قابل سفارش نیست.'
                );

                abort_if(
                    (int) $variant->stock < (int) $required,
                    422,
                    "موجودی «{$variant->product->name}» برای تعداد انتخاب‌شده کافی نیست."
                );
            }
        }

        if ($profile) {
            $this->assertMinimums($profile, $subtotal, $quantity);
        }

        return [
            'profile' => $profile,
            'items' => $items,
            'packs' => $packs,
            'subtotal' => $subtotal,
            'pack_discount' => $packDiscount,
            'quantity' => $quantity,
        ];
    }

    public function assertMinimums(
        \App\Models\WholesaleProfile $profile,
        float $subtotal,
        int $quantity
    ): void {
        if (
            $profile->minimum_order_amount !== null
            && $subtotal < (float) $profile->minimum_order_amount
        ) {
            abort(
                422,
                'حداقل مبلغ سفارش عمده '
                . number_format((float) $profile->minimum_order_amount)
                . ' تومان است.'
            );
        }

        if (
            $profile->minimum_order_quantity !== null
            && $quantity < (int) $profile->minimum_order_quantity
        ) {
            abort(
                422,
                'حداقل تعداد سفارش عمده '
                . number_format((int) $profile->minimum_order_quantity)
                . ' عدد است.'
            );
        }
    }

    public function unitPrice(ProductVariant $variant): float
    {
        abort_unless(
            $variant->wholesale_price !== null,
            422,
            'قیمت عمده این واریانت تعیین نشده است.'
        );

        return (float) $variant->wholesale_price;
    }
}
