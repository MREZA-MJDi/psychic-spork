<?php

namespace App\Services;

use App\Data\ExternalProductData;
use App\Models\Brand;
use App\Models\Category;
use App\Models\IntegrationMapping;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class NilaCatalogImporter
{
    public function import(array|iterable $records): int
    {
        $count = 0;

        foreach ($records as $record) {
            $data = $record instanceof ExternalProductData
                ? $record
                : ExternalProductData::fromArray($record);

            $this->importOne($data);
            $count++;
        }

        return $count;
    }

    public function importOne(ExternalProductData $data): Product
    {
        return DB::transaction(function () use ($data): Product {
            $category = $this->upsertCategory($data);
            $brand = $this->upsertBrand($data);

            $mapping = IntegrationMapping::query()
                ->where('integration', 'nila')
                ->where('entity_type', Product::class)
                ->where('external_id', $data->externalId)
                ->lockForUpdate()
                ->first();

            $product = $mapping
                ? Product::query()->withTrashed()->findOrFail($mapping->entity_id)
                : new Product();

            if ($product->trashed()) {
                $product->restore();
            }

            $product->fill([
                'category_id' => $category?->id,
                'brand_id' => $brand?->id,
                'name' => $data->name,
                'slug' => $data->slug ?: Str::slug($data->name) . '-' . $data->externalId,
                'short_description' => $data->shortDescription,
                'description' => $data->description,
                'attributes' => $data->attributes,
                'is_active' => $data->isActive,
            ]);
            $product->save();

            IntegrationMapping::updateOrCreate(
                [
                    'integration' => 'nila',
                    'entity_type' => Product::class,
                    'external_id' => $data->externalId,
                ],
                [
                    'entity_id' => $product->id,
                    'external_sku' => $data->sku,
                    'metadata' => ['source' => 'nila_import'],
                ]
            );

            if ($data->variants !== []) {
                foreach ($data->variants as $variant) {
                    $this->upsertVariant($product, $variant);
                }
            } else {
                $this->upsertVariant($product, [
                    'external_id' => $data->externalId . ':default',
                    'sku' => $data->sku,
                    'price' => $data->price,
                    'sale_price' => $data->salePrice,
                    'wholesale_price' => $data->wholesalePrice,
                    'stock' => $data->stock,
                ]);
            }

            return $product->fresh(['variants', 'category', 'brand']);
        });
    }

    private function upsertVariant(Product $product, array $data): ProductVariant
    {
        $externalId = (string) $data['external_id'];
        $mapping = IntegrationMapping::query()
            ->where('integration', 'nila')
            ->where('entity_type', ProductVariant::class)
            ->where('external_id', $externalId)
            ->lockForUpdate()
            ->first();

        $variant = $mapping
            ? ProductVariant::query()->withTrashed()->findOrFail($mapping->entity_id)
            : new ProductVariant();

        if ($variant->exists && $variant->trashed()) {
            $variant->restore();
        }

        $variant->product_id = $product->id;
        $variant->sku = (string) $data['sku'];
        $variant->size = $data['size'] ?? null;
        $variant->color = $data['color'] ?? null;
        $variant->color_code = $data['color_code'] ?? null;
        $variant->price = $data['price'] ?? null;
        $variant->sale_price = $data['sale_price'] ?? null;
        $variant->wholesale_price = $data['wholesale_price'] ?? null;
        $variant->stock = isset($data['stock']) ? (int) $data['stock'] : $variant->stock ?? 0;
        $variant->is_active = (bool) ($data['is_active'] ?? true);
        $variant->save();

        IntegrationMapping::updateOrCreate(
            [
                'integration' => 'nila',
                'entity_type' => ProductVariant::class,
                'external_id' => $externalId,
            ],
            [
                'entity_id' => $variant->id,
                'external_sku' => $variant->sku,
                'metadata' => ['source' => 'nila_import'],
            ]
        );

        return $variant;
    }

    private function upsertCategory(ExternalProductData $data): ?Category
    {
        if (! $data->externalCategoryId || ! $data->categoryName) {
            return null;
        }

        return $this->upsertMappedEntity(
            Category::class,
            $data->externalCategoryId,
            ['name' => $data->categoryName, 'is_active' => true]
        );
    }

    private function upsertBrand(ExternalProductData $data): ?Brand
    {
        if (! $data->externalBrandId || ! $data->brandName) {
            return null;
        }

        return $this->upsertMappedEntity(
            Brand::class,
            $data->externalBrandId,
            ['name' => $data->brandName, 'is_active' => true]
        );
    }

    private function upsertMappedEntity(string $type, string $externalId, array $attributes): Model
    {
        $mapping = IntegrationMapping::query()
            ->where('integration', 'nila')
            ->where('entity_type', $type)
            ->where('external_id', $externalId)
            ->lockForUpdate()
            ->first();

        $model = $mapping
            ? $type::query()->withTrashed()->findOrFail($mapping->entity_id)
            : new $type();

        if ($model->exists && method_exists($model, 'trashed') && $model->trashed()) {
            $model->restore();
        }

        $model->fill($attributes);
        $model->slug ??= Str::slug($attributes['name']) . '-' . $externalId;
        $model->save();

        IntegrationMapping::updateOrCreate(
            [
                'integration' => 'nila',
                'entity_type' => $type,
                'external_id' => $externalId,
            ],
            [
                'entity_id' => $model->id,
                'metadata' => ['source' => 'nila_import'],
            ]
        );

        return $model;
    }
}
