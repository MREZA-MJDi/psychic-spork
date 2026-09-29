<?php

namespace App\Integrations\Nila;

use App\Integrations\Nila\Data\ExternalProductData;
use App\Integrations\Nila\Data\ExternalVariantData;
use App\Models\IntegrationMapping;
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

        $product->fill([
            'name' => $data->name,
            'slug' => $this->resolveSlug($data, $product),
            'short_description' => $data->shortDescription,
            'description' => $data->description,
            'attributes' => $data->attributes,
            'category_id' => $data->categoryId,
            'brand_id' => $data->brandId,
            'is_active' => $data->isActive,
            'is_featured' => $data->isFeatured,
            'sort_order' => $data->sortOrder,
        ]);
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

        $variant->fill([
            'product_id' => $product->id,
            'sku' => $data->sku,
            'size' => $data->size,
            'color' => $data->color,
            'color_code' => $data->colorCode,
            'price' => $data->price,
            'sale_price' => $data->salePrice,
            'stock' => $data->stock,
            'is_active' => $data->isActive,
            'sort_order' => $data->sortOrder,
        ]);
        $variant->save();

        $this->syncMapping(
            ProductVariant::class,
            $variant->id,
            $data->externalId,
            $data->sku,
            $data->metadata
        );

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
