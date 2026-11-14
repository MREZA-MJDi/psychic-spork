<?php

namespace App\Services;

use App\Models\Address;
use App\Models\Cart;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class OrderService
{
    public function __construct(
        private readonly PaymentService $payment,
        private readonly CartService $cart,
        private readonly WholesalePricingService $wholesalePricing,
        private readonly InventoryService $inventory,
    ) {
    }

    public function createFromCart(
        Cart $cart,
        array $customer,
        ?User $user = null
    ): Order {
        return DB::transaction(function () use ($cart, $customer, $user): Order {
            $lockedCart = Cart::query()
                ->lockForUpdate()
                ->findOrFail($cart->id);

            $cartItems = $lockedCart->items()
                ->orderBy('product_variant_id')
                ->lockForUpdate()
                ->get();

            $packLines = $lockedCart->wholesalePacks()
                ->with('pack')
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            $hasPacks = $packLines->isNotEmpty();
            $orderType = $hasPacks ? 'wholesale' : ($customer['order_type'] ?? 'retail');

            abort_unless(
                in_array($orderType, ['retail', 'wholesale'], true),
                422,
                'نوع سفارش نامعتبر است.'
            );

            abort_if(
                $cartItems->isEmpty() && ! $hasPacks,
                422,
                'سبد خرید خالی است.'
            );

            abort_if(
                $cartItems->count() + $packLines->count() > CartService::MAX_ITEMS,
                422,
                'سبد خرید از حداکثر تعداد اقلام مجاز بیشتر است.'
            );

            $packIds = $packLines->pluck('wholesale_pack_id')->unique()->values();
            $packs = $packIds->isEmpty()
                ? collect()
                : \App\Models\WholesalePack::query()
                    ->with(['items.variant.product.galleryMedia'])
                    ->whereIn('id', $packIds)
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

            $variantIds = $cartItems->pluck('product_variant_id');

            foreach ($packs as $pack) {
                $variantIds = $variantIds->merge($pack->items->pluck('product_variant_id'));
            }

            $variantIds = $variantIds->filter()->unique()->sort()->values();

            $lockedVariants = $variantIds->isEmpty()
                ? collect()
                : ProductVariant::query()
                    ->with('product.galleryMedia')
                    ->whereIn('id', $variantIds)
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

            $normalLines = [];
            $packPlans = [];
            $requiredStock = [];
            $subtotal = 0.0;
            $discount = 0.0;
            $orderQuantity = 0;

            foreach ($cartItems as $cartItem) {
                $variant = $lockedVariants->get($cartItem->product_variant_id);

                abort_unless(
                    $variant
                    && $variant->is_active
                    && $variant->product?->is_active,
                    422,
                    'یکی از محصولات سبد دیگر قابل سفارش نیست.'
                );

                $quantity = (int) $cartItem->quantity;

                abort_if(
                    $quantity < 1 || $quantity > CartService::MAX_QUANTITY_PER_ITEM,
                    422,
                    'تعداد یکی از محصولات سبد خرید نامعتبر است.'
                );

                $unitPrice = $orderType === 'wholesale'
                    ? $this->wholesalePricing->unitPrice($variant)
                    : (float) $variant->effective_price;

                abort_if($unitPrice < 0, 422, 'مبلغ یکی از محصولات نامعتبر است.');

                $lineTotal = round($unitPrice * $quantity, 2);
                $subtotal += $lineTotal;
                $orderQuantity += $quantity;
                $requiredStock[$variant->id] = ($requiredStock[$variant->id] ?? 0) + $quantity;

                $normalLines[] = [
                    'variant' => $variant,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'line_total' => $lineTotal,
                ];
            }

            foreach ($packLines as $packLine) {
                $pack = $packs->get($packLine->wholesale_pack_id);
                $packCount = (int) $packLine->quantity;

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
                $itemSnapshots = [];

                foreach ($pack->items as $packItem) {
                    $variant = $lockedVariants->get($packItem->product_variant_id);
                    $quantityPerPack = (int) $packItem->quantity;

                    abort_unless(
                        $variant
                        && $variant->is_active
                        && $variant->product?->is_active,
                        422,
                        "یکی از اقلام پک «{$pack->name}» دیگر فعال نیست."
                    );

                    abort_if($quantityPerPack < 1, 422, "تعداد یکی از اقلام پک «{$pack->name}» نامعتبر است.");

                    $unitPrice = $this->wholesalePricing->unitPrice($variant);
                    $totalQuantity = $quantityPerPack * $packCount;
                    $lineTotal = round($unitPrice * $totalQuantity, 2);

                    $packBasePrice += $unitPrice * $quantityPerPack;
                    $packUnitCount += $quantityPerPack;
                    $requiredStock[$variant->id] = ($requiredStock[$variant->id] ?? 0) + $totalQuantity;

                    $itemSnapshots[] = [
                        'variant_id' => $variant->id,
                        'product_id' => $variant->product_id,
                        'product_name' => $variant->product->name,
                        'variant_name' => $this->variantName($variant),
                        'sku' => $variant->sku,
                        'product_image' => $variant->product->galleryMedia->first()?->path,
                        'quantity_per_pack' => $quantityPerPack,
                        'quantity' => $totalQuantity,
                        'unit_price' => $unitPrice,
                        'line_total' => $lineTotal,
                    ];
                }

                $packPrice = (float) $pack->pack_price;

                abort_if(
                    $packPrice <= 0 || $packPrice > $packBasePrice,
                    422,
                    "قیمت پک «{$pack->name}» معتبر نیست؛ قیمت پک نباید از جمع قیمت عمده اقلام بیشتر باشد."
                );

                $packLineTotal = round($packPrice * $packCount, 2);
                $packGrossTotal = round($packBasePrice * $packCount, 2);

                $subtotal += $packGrossTotal;
                $discount += round($packGrossTotal - $packLineTotal, 2);
                $orderQuantity += $packUnitCount * $packCount;

                $packPlans[] = [
                    'pack' => $pack,
                    'quantity' => $packCount,
                    'unit_price' => $packPrice,
                    'line_total' => $packLineTotal,
                    'items_snapshot' => $itemSnapshots,
                ];
            }

            foreach ($requiredStock as $variantId => $required) {
                $variant = $lockedVariants->get($variantId);

                abort_unless(
                    $variant && $variant->is_active && $variant->product?->is_active,
                    422,
                    'یکی از اقلام سفارش دیگر قابل فروش نیست.'
                );

                abort_if(
                    (int) $variant->stock < (int) $required,
                    422,
                    "موجودی «{$variant->product->name}» برای سفارش کافی نیست."
                );
            }

            $total = round($subtotal - $discount, 2);

            abort_if(
                $total <= 0,
                422,
                'مبلغ سفارش معتبر نیست.'
            );

            $profile = $user?->wholesaleProfile()->first();
            if ($orderType === 'wholesale' && $profile) {
                $this->wholesalePricing->assertMinimums(
                    $profile,
                    $total,
                    $orderQuantity
                );
            }

            $addressId = $this->resolveAddressId(
                $customer['address_id'] ?? null,
                $user
            );

            $order = Order::create([
                'user_id' => $user?->id,
                'address_id' => $addressId,
                'order_number' => $this->orderNumber(),
                'customer_name' => trim((string) $customer['customer_name']),
                'customer_phone' => trim((string) $customer['customer_phone']),
                'customer_email' => filled($customer['customer_email'] ?? null)
                    ? trim((string) $customer['customer_email'])
                    : null,
                'shipping_address' => trim((string) $customer['shipping_address']),
                'shipping_province' => $this->nullableString($customer['shipping_province'] ?? null),
                'shipping_city' => $this->nullableString($customer['shipping_city'] ?? null),
                'postal_code' => $this->nullableString($customer['postal_code'] ?? null),
                'status' => 'pending',
                'payment_status' => 'pending',
                'payment_method' => $customer['payment_method'] ?? 'online',
                'order_type' => $orderType,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'shipping_cost' => 0,
                'total' => $total,
                'customer_note' => $this->nullableString($customer['customer_note'] ?? null),
                'placed_at' => now(),
            ]);

            foreach ($normalLines as $line) {
                /** @var ProductVariant $variant */
                $variant = $line['variant'];
                $quantity = $line['quantity'];

                $order->items()->create([
                    'product_id' => $variant->product_id,
                    'product_variant_id' => $variant->id,
                    'product_name' => $variant->product->name,
                    'variant_name' => $this->variantName($variant),
                    'sku' => $variant->sku,
                    'product_image' => $variant->product->galleryMedia->first()?->path,
                    'unit_price' => $line['unit_price'],
                    'quantity' => $quantity,
                    'line_total' => $line['line_total'],
                ]);

                $this->inventory->moveLocked(
                    $variant,
                    -$quantity,
                    'sale',
                    "کسر موجودی سفارش {$order->order_number}",
                    $user?->id,
                    Order::class,
                    $order->id
                );
            }

            foreach ($packPlans as $plan) {
                $pack = $plan['pack'];
                $packOrderLine = $order->wholesalePacks()->create([
                    'wholesale_pack_id' => $pack->id,
                    'pack_name' => $pack->name,
                    'pack_slug' => $pack->slug,
                    'image_path' => $pack->image_path,
                    'quantity' => $plan['quantity'],
                    'unit_pack_price' => $plan['unit_price'],
                    'line_total' => $plan['line_total'],
                    'items_snapshot' => $plan['items_snapshot'],
                ]);

                foreach ($plan['items_snapshot'] as $snapshot) {
                    /** @var ProductVariant $variant */
                    $variant = $lockedVariants->get($snapshot['variant_id']);

                    $order->items()->create([
                        'wholesale_pack_order_id' => $packOrderLine->id,
                        'product_id' => $variant->product_id,
                        'product_variant_id' => $variant->id,
                        'product_name' => $variant->product->name,
                        'variant_name' => $this->variantName($variant),
                        'sku' => $variant->sku,
                        'product_image' => $variant->product->galleryMedia->first()?->path,
                        'unit_price' => $snapshot['unit_price'],
                        'quantity' => $snapshot['quantity'],
                        'line_total' => $snapshot['line_total'],
                    ]);

                    $this->inventory->moveLocked(
                        $variant,
                        -(int) $snapshot['quantity'],
                        'sale',
                        "کسر موجودی پک {$pack->name} در سفارش {$order->order_number}",
                        $user?->id,
                        Order::class,
                        $order->id
                    );
                }
            }

            $lockedCart->items()->delete();
            $lockedCart->wholesalePacks()->delete();
            $lockedCart->update(['last_activity_at' => now()]);

            return $order->fresh([
                'items',
                'payments',
                'wholesalePacks.items',
            ]);
        }, 3);
    }

    public function updateStatus(
        Order $order,
        string $newStatus,
        string $paymentStatus,
        ?string $customerNote = null,
        ?string $trackingCode = null
    ): Order {
        abort_unless(
            in_array($newStatus, Order::STATUSES, true),
            422,
            'وضعیت سفارش نامعتبر است.'
        );

        abort_unless(
            in_array($paymentStatus, Order::PAYMENT_STATUSES, true),
            422,
            'وضعیت پرداخت نامعتبر است.'
        );

        return DB::transaction(function () use (
            $order,
            $newStatus,
            $paymentStatus,
            $customerNote,
            $trackingCode
        ): Order {
            $order = Order::query()
                ->lockForUpdate()
                ->findOrFail($order->id);

            $oldStatus = $order->status;
            $oldPaymentStatus = $order->payment_status;

            $this->assertStatusTransition(
                $oldStatus,
                $newStatus
            );

            abort_if(
                $newStatus === 'returned'
                    && $oldPaymentStatus === 'paid'
                    && $paymentStatus !== 'refunded',
                422,
                'برای ثبت مرجوعی سفارش پرداخت‌شده، بازپرداخت را هم‌زمان ثبت کنید.'
            );

            abort_if(
                $paymentStatus === 'refunded'
                    && ($newStatus !== 'returned' || $oldPaymentStatus !== 'paid'),
                422,
                'بازپرداخت فقط همراه با مرجوعی سفارش پرداخت‌شده مجاز است.'
            );

            $wasCancelledLike = in_array(
                $oldStatus,
                Order::CANCEL_LIKE_STATUSES,
                true
            );

            $willBeCancelledLike = in_array(
                $newStatus,
                Order::CANCEL_LIKE_STATUSES,
                true
            );

            if (
                ! $wasCancelledLike
                && $willBeCancelledLike
            ) {
                $cheque = $order->chequePayment()
                    ->lockForUpdate()
                    ->first();

                abort_if(
                    $cheque && in_array($cheque->status, ['deposited'], true),
                    409,
                    'سفارش دارای چک واریزشده را تا تعیین تکلیف چک نمی‌توان لغو کرد.'
                );

                if ($cheque && in_array($cheque->status, ['submitted', 'under_review', 'awaiting_original', 'original_received', 'accepted'], true)) {
                    $cheque->update(['status' => 'cancelled']);
                }

                $this->restoreInventory($order);
            }

            if (
                $wasCancelledLike
                && ! $willBeCancelledLike
            ) {
                $this->deductInventoryAgain($order);
            }

            $order->update([
                'status' => $newStatus,
                'customer_note' => $customerNote,
                'tracking_code' => $trackingCode,
                'shipped_at' => $this->statusTimestamp(
                    $newStatus,
                    'shipped_at',
                    $order->shipped_at
                ),
                'delivered_at' => $this->statusTimestamp(
                    $newStatus,
                    'delivered_at',
                    $order->delivered_at
                ),
                'cancelled_at' => $willBeCancelledLike
                    ? ($order->cancelled_at ?? now())
                    : $order->cancelled_at,
            ]);

            if ($paymentStatus !== $oldPaymentStatus) {
                abort_if(
                    $paymentStatus === 'paid',
                    422,
                    'ثبت پرداخت موفق فقط از مسیر تأیید درگاه یا تسویه ابزار پرداخت انجام می‌شود.'
                );

                abort_if(
                    $paymentStatus === 'refunded',
                    422,
                    'بازپرداخت باید از مسیر امن پرداخت انجام شود.'
                );

                $this->payment->matchStatus(
                    $order,
                    $paymentStatus
                );
            }

            return $order->fresh([
                'items',
                'payments',
            ]);
        }, 3);
    }

    private function restoreInventory(Order $order): void
    {
        $order->loadMissing('items');

        $variants = ProductVariant::query()
            ->whereIn(
                'id',
                $order->items
                    ->pluck('product_variant_id')
                    ->filter()
                    ->unique()
            )
            ->orderBy('id')
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        foreach ($order->items as $item) {
            $variant = $variants->get($item->product_variant_id);

            if (! $variant) {
                continue;
            }

            $this->inventory->moveLocked(
                $variant,
                (int) $item->quantity,
                'return',
                "بازگشت موجودی سفارش {$order->order_number}",
                auth()->id(),
                Order::class,
                $order->id
            );
        }
    }

    private function deductInventoryAgain(Order $order): void
    {
        $order->loadMissing('items');

        $variants = ProductVariant::query()
            ->whereIn(
                'id',
                $order->items
                    ->pluck('product_variant_id')
                    ->filter()
                    ->unique()
            )
            ->orderBy('id')
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        foreach ($order->items as $item) {
            $variant = $variants->get($item->product_variant_id);

            abort_unless(
                $variant
                && $variant->stock >= $item->quantity,
                422,
                "موجودی برای فعال‌سازی مجدد سفارش «{$order->order_number}» کافی نیست."
            );

            $this->inventory->moveLocked(
                $variant,
                -((int) $item->quantity),
                'sale',
                "کسر مجدد موجودی سفارش {$order->order_number}",
                auth()->id(),
                Order::class,
                $order->id
            );
        }
    }

    private function resolveAddressId(
        ?int $addressId,
        ?User $user
    ): ?int {
        if (! $addressId) {
            return null;
        }

        abort_unless(
            $user
            && Address::query()
                ->whereKey($addressId)
                ->where('user_id', $user->id)
                ->exists(),
            422,
            'آدرس انتخاب‌شده معتبر نیست.'
        );

        return $addressId;
    }

    private function variantName(
        ProductVariant $variant
    ): ?string {
        $parts = array_values(array_filter([
            $variant->size
                ? "سایز {$variant->size}"
                : null,

            $variant->color
                ? "رنگ {$variant->color}"
                : null,
        ]));

        return $parts
            ? implode(' / ', $parts)
            : null;
    }

    private function assertStatusTransition(
        string $from,
        string $to
    ): void {
        if ($from === $to) {
            return;
        }

        $allowed = match ($from) {
            'pending' => [
                'confirmed',
                'cancelled',
            ],

            'confirmed' => [
                'preparing',
                'cancelled',
            ],

            'preparing' => [
                'shipped',
                'cancelled',
            ],

            'shipped' => [
                'delivered',
                'returned',
            ],

            'delivered' => [
                'returned',
            ],

            'cancelled',
            'returned' => [],

            default => [],
        };

        abort_unless(
            in_array($to, $allowed, true),
            422,
            'تغییر وضعیت سفارش از وضعیت فعلی مجاز نیست.'
        );
    }

    private function statusTimestamp(
        string $status,
        string $field,
        mixed $current
    ): mixed {
        return match ($field) {
            'shipped_at' => $status === 'shipped'
                ? ($current ?? now())
                : $current,

            'delivered_at' => $status === 'delivered'
                ? ($current ?? now())
                : $current,

            default => $current,
        };
    }

    private function nullableString(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value !== ''
            ? $value
            : null;
    }

    private function orderNumber(): string
    {
        do {
            $number = 'JN-'
                . now()->format('Ymd')
                . '-'
                . Str::upper(Str::random(6));
        } while (
            Order::query()
                ->where('order_number', $number)
                ->exists()
        );

        return $number;
    }
}
