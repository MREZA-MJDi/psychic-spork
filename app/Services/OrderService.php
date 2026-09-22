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
    ) {
    }

    public function createFromCart(
        Cart $cart,
        array $customer,
        ?User $user = null
    ): Order {
        return DB::transaction(function () use (
            $cart,
            $customer,
            $user
        ): Order {
            $cartItems = $cart->items()
                ->with([
                    'productVariant.product.galleryMedia',
                ])
                ->get();

            abort_if(
                $cartItems->isEmpty(),
                422,
                'سبد خرید خالی است.'
            );

            $addressId = $this->resolveAddressId(
                $customer['address_id'] ?? null,
                $user
            );

            $order = Order::create([
                'user_id' => $user?->id,
                'address_id' => $addressId,
                'order_number' => $this->orderNumber(),

                'customer_name' => trim(
                    (string) $customer['customer_name']
                ),

                'customer_phone' => trim(
                    (string) $customer['customer_phone']
                ),

                'customer_email' => filled($customer['customer_email'] ?? null)
                    ? trim((string) $customer['customer_email'])
                    : null,

                'shipping_address' => trim(
                    (string) $customer['shipping_address']
                ),

                'shipping_province' => $this->nullableString(
                    $customer['shipping_province'] ?? null
                ),

                'shipping_city' => $this->nullableString(
                    $customer['shipping_city'] ?? null
                ),

                'postal_code' => $this->nullableString(
                    $customer['postal_code'] ?? null
                ),

                'status' => 'pending',
                'payment_status' => 'pending',
                'payment_method' => 'cash_on_delivery',

                'subtotal' => 0,
                'discount' => 0,
                'shipping_cost' => 0,
                'total' => 0,

                'customer_note' => $this->nullableString(
                    $customer['customer_note'] ?? null
                ),

                'placed_at' => now(),
            ]);

            $subtotal = 0;

            foreach ($cartItems as $cartItem) {
                $variant = ProductVariant::query()
                    ->with([
                        'product.galleryMedia',
                    ])
                    ->lockForUpdate()
                    ->find($cartItem->product_variant_id);

                abort_unless(
                    $variant
                    && $variant->is_active
                    && $variant->product?->is_active,
                    422,
                    'یکی از محصولات سبد دیگر قابل سفارش نیست.'
                );

                $quantity = (int) $cartItem->quantity;

                abort_if(
                    $quantity < 1,
                    422,
                    'تعداد یکی از محصولات سبد خرید نامعتبر است.'
                );

                abort_if(
                    $quantity > $variant->stock,
                    422,
                    "موجودی «{$variant->product->name}» برای سفارش کافی نیست."
                );

                $unitPrice = (float) $variant->effective_price;
                $lineTotal = $unitPrice * $quantity;

                abort_if(
                    $unitPrice < 0 || $lineTotal < 0,
                    422,
                    'مبلغ یکی از محصولات نامعتبر است.'
                );

                $subtotal += $lineTotal;

                $order->items()->create([
                    'product_id' => $variant->product_id,
                    'product_variant_id' => $variant->id,
                    'product_name' => $variant->product->name,
                    'variant_name' => $this->variantName($variant),
                    'sku' => $variant->sku,
                    'product_image' => $variant->product
                        ->galleryMedia
                        ->first()?->path,
                    'unit_price' => $unitPrice,
                    'quantity' => $quantity,
                    'line_total' => $lineTotal,
                ]);

                $variant->decrement('stock', $quantity);
                $variant->refresh();

                $variant->inventoryMovements()->create([
                    'type' => 'sale',
                    'quantity' => -$quantity,
                    'stock_after' => $variant->stock,
                    'reference_type' => Order::class,
                    'reference_id' => $order->id,
                    'note' => "کسر موجودی سفارش {$order->order_number}",
                    'created_by' => $user?->id,
                ]);
            }

            abort_if(
                $subtotal <= 0,
                422,
                'مبلغ سفارش معتبر نیست.'
            );

            $order->update([
                'subtotal' => $subtotal,
                'total' => $subtotal,
            ]);

            $this->cart->clear($cart);

            return $order->fresh([
                'items',
                'payments',
            ]);
        });
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
                $this->payment->matchStatus(
                    $order,
                    $paymentStatus
                );
            }

            return $order->fresh([
                'items',
                'payments',
            ]);
        });
    }

    private function restoreInventory(Order $order): void
    {
        $order->load('items');

        foreach ($order->items as $item) {
            if (! $item->product_variant_id) {
                continue;
            }

            $variant = ProductVariant::query()
                ->lockForUpdate()
                ->find($item->product_variant_id);

            if (! $variant) {
                continue;
            }

            $variant->increment(
                'stock',
                (int) $item->quantity
            );

            $variant->refresh();

            $variant->inventoryMovements()->create([
                'type' => 'return',
                'quantity' => (int) $item->quantity,
                'stock_after' => $variant->stock,
                'reference_type' => Order::class,
                'reference_id' => $order->id,
                'note' => "بازگشت موجودی سفارش {$order->order_number}",
                'created_by' => auth()->id(),
            ]);
        }
    }

    private function deductInventoryAgain(Order $order): void
    {
        $order->load('items');

        foreach ($order->items as $item) {
            if (! $item->product_variant_id) {
                continue;
            }

            $variant = ProductVariant::query()
                ->lockForUpdate()
                ->find($item->product_variant_id);

            abort_unless(
                $variant
                && $variant->stock >= $item->quantity,
                422,
                "موجودی برای فعال‌سازی مجدد سفارش «{$order->order_number}» کافی نیست."
            );

            $variant->decrement(
                'stock',
                (int) $item->quantity
            );

            $variant->refresh();

            $variant->inventoryMovements()->create([
                'type' => 'sale',
                'quantity' => -((int) $item->quantity),
                'stock_after' => $variant->stock,
                'reference_type' => Order::class,
                'reference_id' => $order->id,
                'note' => "کسر مجدد موجودی سفارش {$order->order_number}",
                'created_by' => auth()->id(),
            ]);
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
