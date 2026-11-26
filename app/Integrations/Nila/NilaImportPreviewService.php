<?php

namespace App\Integrations\Nila;

use App\Models\IntegrationMapping;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Collection;

final class NilaImportPreviewService
{
    /**
     * Compare an uploaded Nila snapshot with Janan's current catalog.
     * External product/variant codes are the only matching keys; names are
     * never used to guess that two records are the same.
     *
     * @return array<string, mixed>
     */
    public function summarize(array $parsed): array
    {
        $groups = collect($parsed['groups'] ?? []);
        $rows = collect($parsed['rows'] ?? []);

        $blockedGroups = $groups->filter(
            fn (array $group): bool => (int) ($group['invalid_rows'] ?? 0) > 0
                || (bool) ($group['name_conflict'] ?? false)
        );

        $validGroups = $groups->diffKeys($blockedGroups)->values();

        $validCodes = $validGroups
            ->pluck('product_code')
            ->map(fn ($code): string => (string) $code)
            ->all();

        $validCodeSet = array_fill_keys($validCodes, true);
        $validRows = $rows->filter(
            fn (array $row): bool => (bool) ($row['is_valid'] ?? false)
                && isset($validCodeSet[(string) ($row['product_code'] ?? '')])
        )->values();

        $productMappings = $this->mappingsByExternalId(Product::class, $validCodes);
        $mappedProductIds = $productMappings->pluck('entity_id')->map(fn ($id) => (int) $id)->unique()->all();
        $products = $this->modelsById(Product::class, $mappedProductIds);

        $existingProducts = 0;
        $newProducts = 0;
        $renamedProducts = 0;

        foreach ($validGroups as $group) {
            $mapping = $productMappings->get((string) $group['product_code']);
            $product = $mapping ? $products->get((int) $mapping->entity_id) : null;

            if ($product) {
                $existingProducts++;

                if ((string) $product->name !== (string) $group['product_name_candidate']) {
                    $renamedProducts++;
                }
            } else {
                $newProducts++;
            }
        }

        $externalVariantIds = $validRows
            ->pluck('holoo_code')
            ->map(fn ($code): string => (string) $code)
            ->unique()
            ->values()
            ->all();

        $variantMappings = $this->mappingsByExternalId(ProductVariant::class, $externalVariantIds);
        $mappedVariantIds = $variantMappings->pluck('entity_id')->map(fn ($id) => (int) $id)->unique()->all();
        $variants = $this->modelsById(ProductVariant::class, $mappedVariantIds);

        $unmappedSkus = $validRows
            ->filter(fn (array $row): bool => ! $variantMappings->has((string) $row['holoo_code']))
            ->pluck('holoo_code')
            ->map(fn ($sku): string => (string) $sku)
            ->unique()
            ->values()
            ->all();

        $skuOwners = $this->skuOwners($unmappedSkus);

        $existingVariants = 0;
        $newVariants = 0;
        $stockChanges = [];
        $priceChanges = [];
        $skuConflicts = [];

        foreach ($validRows as $row) {
            $externalId = (string) $row['holoo_code'];
            $mapping = $variantMappings->get($externalId);
            $variant = $mapping ? $variants->get((int) $mapping->entity_id) : null;

            if ($variant) {
                $existingVariants++;

                if ((int) $variant->stock !== (int) $row['stock']) {
                    $stockChanges[] = [
                        'sku' => $externalId,
                        'name' => (string) ($row['holoo_name'] ?? ''),
                        'current' => (int) $variant->stock,
                        'incoming' => (int) $row['stock'],
                        'difference' => (int) $row['stock'] - (int) $variant->stock,
                    ];
                }

                if (round((float) $variant->price, 2) !== round((float) $row['price'], 2)) {
                    $priceChanges[] = [
                        'sku' => $externalId,
                        'name' => (string) ($row['holoo_name'] ?? ''),
                        'current' => (float) $variant->price,
                        'incoming' => (float) $row['price'],
                    ];
                }

                continue;
            }

            $owner = $skuOwners->get($externalId);
            if ($owner) {
                $skuConflicts[] = [
                    'sku' => $externalId,
                    'owner_variant_id' => (int) $owner->id,
                ];
            } else {
                $newVariants++;
            }
        }

        return [
            'valid_groups' => $validGroups->count(),
            'blocked_groups' => $blockedGroups->count(),
            'invalid_price_rows' => $rows->filter(fn (array $row): bool =>
                ($row['price'] ?? null) === null || $row['price'] < 0
            )->count(),
            'invalid_stock_rows' => $rows->filter(fn (array $row): bool =>
                ($row['stock'] ?? null) === null || $row['stock'] < 0
            )->count(),
            'name_conflict_groups' => $groups->where('name_conflict', true)->count(),
            'existing_products' => $existingProducts,
            'new_products' => $newProducts,
            'renamed_products' => $renamedProducts,
            'existing_variants' => $existingVariants,
            'new_variants' => $newVariants,
            'stock_change_count' => count($stockChanges),
            'price_change_count' => count($priceChanges),
            'sku_conflict_count' => count($skuConflicts),
            'stock_changes' => array_slice($stockChanges, 0, 30),
            'price_changes' => array_slice($priceChanges, 0, 20),
            'sku_conflicts' => array_slice($skuConflicts, 0, 20),
        ];
    }

    private function mappingsByExternalId(string $entityType, array $externalIds): Collection
    {
        $mappings = collect();

        foreach (array_chunk(array_values(array_unique($externalIds)), 500) as $chunk) {
            if ($chunk === []) {
                continue;
            }

            $mappings = $mappings->concat(
                IntegrationMapping::query()
                    ->where('integration', 'nila')
                    ->where('entity_type', $entityType)
                    ->whereIn('external_id', $chunk)
                    ->get(['id', 'entity_id', 'external_id'])
            );
        }

        return $mappings->keyBy('external_id');
    }

    private function modelsById(string $model, array $ids): Collection
    {
        $records = collect();

        foreach (array_chunk(array_values(array_unique($ids)), 500) as $chunk) {
            if ($chunk === []) {
                continue;
            }

            $records = $records->concat(
                $model::withTrashed()
                    ->whereIn('id', $chunk)
                    ->get()
            );
        }

        return $records->keyBy('id');
    }

    private function skuOwners(array $skus): Collection
    {
        $owners = collect();

        foreach (array_chunk(array_values(array_unique($skus)), 500) as $chunk) {
            if ($chunk === []) {
                continue;
            }

            $owners = $owners->concat(
                ProductVariant::withTrashed()
                    ->whereIn('sku', $chunk)
                    ->get(['id', 'sku'])
            );
        }

        return $owners->keyBy('sku');
    }
}
