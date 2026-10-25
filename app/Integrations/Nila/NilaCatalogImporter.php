<?php

namespace App\Integrations\Nila;

use App\Integrations\Nila\Data\ExternalProductData;
use App\Integrations\Nila\Data\ExternalVariantData;
use App\Models\IntegrationMapping;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

final class NilaCatalogImporter
{
    public function import(ExternalProductData $data): Product
    {
        return DB::transaction(
            fn (): Product => $this->importProduct($data),
            attempts: 3
        );
    }

    /**
     * Import one product at a time so a bad record cannot roll back a whole
     * catalog batch. The caller can queue/chunk these calls as needed.
     */
    public function importFrom(NilaCatalogSource $source): array
    {
        return $this->importMany($source->fetchProducts());
    }

    public function importMany(iterable $products): array
    {
        $results = [];

        foreach ($products as $product) {
            if (! $product instanceof ExternalProductData) {
                throw new \InvalidArgumentException(
                    'Nila catalog imports must contain ExternalProductData instances.'
                );
            }

            $results[] = $this->import($product);
        }

        return $results;
    }

    private function importProduct(ExternalProductData $data): Product
    {
        $product = $this->resolveProduct($data);
        $isNew = ! $product->exists;

        // Only update fields that this provider actually supplies. Holoo's
        // documented product list has no descriptions, storefront flags or
        // media fields, so a sync must not erase Janan-administered content.
        $fields = [
            'name' => $data->name,
            'slug' => $this->resolveSlug($data, $product),
        ];

        if ($data->shortDescription !== null) {
            $fields['short_description'] = $data->shortDescription;
        }

        if ($data->description !== null) {
            $fields['description'] = $data->description;
        }

        if ($data->attributes !== null) {
            $fields['attributes'] = $data->attributes;
        }

        if ($data->categoryId !== null) {
            $fields['category_id'] = $data->categoryId;
        }

        if ($data->brandId !== null) {
            $fields['brand_id'] = $data->brandId;
        }

        if ($data->isActive !== null || $isNew) {
            $fields['is_active'] = $data->isActive
                ?? (bool) config('nila.active_by_default', false);
        }

        if ($data->isFeatured !== null || $isNew) {
            $fields['is_featured'] = $data->isFeatured ?? false;
        }

        if ($data->sortOrder !== null || $isNew) {
            $fields['sort_order'] = $data->sortOrder ?? 0;
        }

        $product->fill($fields);
        $product->save();

        $this->syncMapping(
            Product::class,
            $product->id,
            $data->externalId,
            null,
            $data->metadata
        );

        foreach ($data->variants as $variantData) {
            $this->importVariant($product, $variantData);
        }

        return $product->fresh(['variants', 'integrationMappings']);
    }

    private function resolveProduct(ExternalProductData $data): Product
    {
        $mapping = $this->mapping(Product::class, $data->externalId);

        if (! $mapping) {
            return new Product;
        }

        $product = Product::withTrashed()->find($mapping->entity_id);

        if (! $product) {
            $mapping->delete();

            return new Product;
        }

        if ($product->trashed()) {
            $product->restore();
        }

        return $product;
    }

    private function importVariant(
        Product $product,
        ExternalVariantData $data
    ): ProductVariant {
        $mapping = $this->mapping(ProductVariant::class, $data->externalId);

        if ($mapping) {
            $variant = ProductVariant::withTrashed()->find($mapping->entity_id);

            if (! $variant) {
                $mapping->delete();
                $variant = new ProductVariant;
            } elseif ($variant->trashed()) {
                $variant->restore();
            }
        } else {
            $variant = new ProductVariant;

            $skuOwner = ProductVariant::withTrashed()
                ->where('sku', $data->sku)
                ->first();

            if ($skuOwner) {
                throw new RuntimeException(
                    "Nila SKU [{$data->sku}] already belongs to a variant without a matching Nila mapping."
                );
            }
        }

        $isNew = ! $variant->exists;
        $previousStock = (int) ($variant->stock ?? 0);

        // SKU, price and stock are supplied by Holoo. Optional storefront fields
        // are preserved unless the source explicitly provides a non-null value.
        $fields = [
            'product_id' => $product->id,
            'sku' => $data->sku,
            'price' => $data->price,
        ];

        if ($data->salePrice !== null) {
            $fields['sale_price'] = $data->salePrice;
        }

        if ($data->size !== null) {
            $fields['size'] = $data->size;
        }

        if ($data->color !== null) {
            $fields['color'] = $data->color;
        }

        if ($data->colorCode !== null) {
            $fields['color_code'] = $data->colorCode;
        }

        if ($data->isActive !== null || $isNew) {
            $fields['is_active'] = $data->isActive ?? true;
        }

        if ($data->sortOrder !== null || $isNew) {
            $fields['sort_order'] = $data->sortOrder ?? 0;
        }

        $variant->fill($fields);
        $variant->save();

        $mapping = $this->syncMapping(
            ProductVariant::class,
            $variant->id,
            $data->externalId,
            $data->sku,
            $data->metadata
        );

        $targetStock = $data->stock;
        $delta = $targetStock - $previousStock;

        if ($delta !== 0) {
            $variant->update(['stock' => $targetStock]);

            InventoryMovement::create([
                'product_variant_id' => $variant->id,
                'type' => 'adjustment',
                'quantity' => $delta,
                'stock_after' => $targetStock,
                'reference_type' => IntegrationMapping::class,
                'reference_id' => $mapping->id,
                'note' => 'به‌روزرسانی موجودی از Nila',
                'created_by' => null,
            ]);
        }

        return $variant;
    }

    private function syncMapping(
        string $entityType,
        int $entityId,
        string $externalId,
        ?string $externalSku,
        array $metadata
    ): IntegrationMapping {
        $mapping = $this->mapping($entityType, $externalId);

        $payload = $metadata;
        $payload['integration'] = 'nila';

        if ($mapping) {
            $mapping->update([
                'entity_id' => $entityId,
                'external_sku' => $externalSku,
                'metadata' => $payload,
            ]);

            return $mapping->refresh();
        }

        return IntegrationMapping::query()->create([
            'integration' => 'nila',
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'external_id' => $externalId,
            'external_sku' => $externalSku,
            'metadata' => $payload,
        ]);
    }

    private function mapping(string $entityType, string $externalId): ?IntegrationMapping
    {
        return IntegrationMapping::query()
            ->where('integration', 'nila')
            ->where('entity_type', $entityType)
            ->where('external_id', $externalId)
            ->first();
    }

    private function resolveSlug(ExternalProductData $data, Product $product): string
    {
        if ($data->slug && ($product->exists || ! Product::withTrashed()->where('slug', $data->slug)->exists())) {
            return $data->slug;
        }

        $base = Str::slug($data->name) ?: 'product';
        $candidate = $base;

        if (
            Product::withTrashed()
                ->where('slug', $candidate)
                ->when($product->exists, fn ($query) => $query->whereKeyNot($product->id))
                ->exists()
        ) {
            $candidate = $base . '-' . Str::lower(Str::substr(hash('sha256', 'nila:' . $data->externalId), 0, 10));
        }

        return $candidate;
    }
}
