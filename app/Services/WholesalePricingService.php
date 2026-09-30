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
        User $user
    ): array {
        $profile = $this->eligibility->assertWholesaleAllowed($user);

        $items = $cart->items()
            ->with([
                'productVariant.product.galleryMedia',
            ])
            ->get();

        abort_if(
            $items->isEmpty(),
            422,
            'سبد خرید خالی است.'
        );

        $subtotal = 0.0;
        $quantity = 0;

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
                $count < 1 || $count > $variant->stock,
                422,
                "تعداد «{$variant->product->name}» برای سفارش عمده معتبر نیست."
            );

            abort_unless(
                $variant->wholesale_price !== null,
                422,
                "قیمت عمده «{$variant->product->name}» هنوز برای این واریانت تعیین نشده است."
            );

            $unitPrice = (float) $variant->wholesale_price;

            abort_if(
                $unitPrice < 0,
                422,
                'قیمت عمده یکی از محصولات نامعتبر است.'
            );

            $quantity += $count;
            $subtotal += $unitPrice * $count;
        }

        $this->assertMinimums(
            $profile,
            $subtotal,
            $quantity
        );

        return [
            'profile' => $profile,
            'items' => $items,
            'subtotal' => $subtotal,
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
