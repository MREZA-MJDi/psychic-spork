<?php

namespace App\Services;

use App\Models\InventoryMovement;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;

final class InventoryService
{
    public function move(
        ProductVariant $variant,
        int $quantity,
        string $type,
        ?string $note = null,
        ?int $createdBy = null,
        ?string $referenceType = null,
        ?int $referenceId = null
    ): InventoryMovement {
        abort_unless(
            in_array($type, InventoryMovement::TYPES, true),
            422,
            'نوع گردش موجودی معتبر نیست.'
        );
        abort_if($quantity === 0, 422, 'مقدار گردش موجودی نمی‌تواند صفر باشد.');

        return DB::transaction(function () use (
            $variant,
            $quantity,
            $type,
            $note,
            $createdBy,
            $referenceType,
            $referenceId
        ): InventoryMovement {
            $locked = ProductVariant::query()
                ->lockForUpdate()
                ->findOrFail($variant->getKey());

            $newStock = (int) $locked->stock + $quantity;
            abort_if($newStock < 0, 422, 'موجودی نمی‌تواند منفی شود.');
            abort_if($newStock > 2147483647, 422, 'موجودی از محدوده مجاز بیشتر است.');

            $locked->update(['stock' => $newStock]);

            return $locked->inventoryMovements()->create([
                'type' => $type,
                'quantity' => $quantity,
                'stock_after' => $newStock,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'note' => $note,
                'created_by' => $createdBy,
            ]);
        }, 3);
    }

    /**
     * Record a movement on a variant row that the caller already locked.
     * The caller must run this inside the transaction holding that lock.
     */
    public function moveLocked(
        ProductVariant $locked,
        int $quantity,
        string $type,
        ?string $note = null,
        ?int $createdBy = null,
        ?string $referenceType = null,
        ?int $referenceId = null
    ): InventoryMovement {
        abort_unless(
            in_array($type, InventoryMovement::TYPES, true),
            422,
            'نوع گردش موجودی معتبر نیست.'
        );
        abort_if($quantity === 0, 422, 'مقدار گردش موجودی نمی‌تواند صفر باشد.');

        $newStock = (int) $locked->stock + $quantity;
        abort_if($newStock < 0, 422, 'موجودی نمی‌تواند منفی شود.');
        abort_if($newStock > 2147483647, 422, 'موجودی از محدوده مجاز بیشتر است.');

        $locked->update(['stock' => $newStock]);

        return $locked->inventoryMovements()->create([
            'type' => $type,
            'quantity' => $quantity,
            'stock_after' => $newStock,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'note' => $note,
            'created_by' => $createdBy,
        ]);
    }

    public function setStock(
        ProductVariant $variant,
        int $targetStock,
        string $note,
        ?int $createdBy = null,
        ?string $referenceType = null,
        ?int $referenceId = null
    ): ?InventoryMovement {
        abort_if($targetStock < 0, 422, 'موجودی نمی‌تواند منفی باشد.');
        abort_if($targetStock > 2147483647, 422, 'موجودی از محدوده مجاز بیشتر است.');

        return DB::transaction(function () use (
            $variant,
            $targetStock,
            $note,
            $createdBy,
            $referenceType,
            $referenceId
        ): ?InventoryMovement {
            $locked = ProductVariant::query()
                ->lockForUpdate()
                ->findOrFail($variant->getKey());

            $delta = $targetStock - (int) $locked->stock;
            if ($delta === 0) {
                return null;
            }

            $locked->update(['stock' => $targetStock]);

            return $locked->inventoryMovements()->create([
                'type' => 'adjustment',
                'quantity' => $delta,
                'stock_after' => $targetStock,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'note' => $note,
                'created_by' => $createdBy,
            ]);
        }, 3);
    }
}
