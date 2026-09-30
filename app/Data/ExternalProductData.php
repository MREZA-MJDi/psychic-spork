<?php

namespace App\Data;

final readonly class ExternalProductData
{
    public function __construct(
        public string $externalId,
        public string $sku,
        public string $name,
        public ?string $slug = null,
        public ?string $shortDescription = null,
        public ?string $description = null,
        public ?string $externalCategoryId = null,
        public ?string $categoryName = null,
        public ?string $externalBrandId = null,
        public ?string $brandName = null,
        public ?float $price = null,
        public ?float $salePrice = null,
        public ?float $wholesalePrice = null,
        public ?int $stock = null,
        public bool $isActive = true,
        public array $attributes = [],
        public array $variants = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            externalId: (string) $data['external_id'],
            sku: (string) $data['sku'],
            name: (string) $data['name'],
            slug: $data['slug'] ?? null,
            shortDescription: $data['short_description'] ?? null,
            description: $data['description'] ?? null,
            externalCategoryId: isset($data['category_external_id']) ? (string) $data['category_external_id'] : null,
            categoryName: $data['category_name'] ?? null,
            externalBrandId: isset($data['brand_external_id']) ? (string) $data['brand_external_id'] : null,
            brandName: $data['brand_name'] ?? null,
            price: isset($data['price']) ? (float) $data['price'] : null,
            salePrice: isset($data['sale_price']) ? (float) $data['sale_price'] : null,
            wholesalePrice: isset($data['wholesale_price']) ? (float) $data['wholesale_price'] : null,
            stock: isset($data['stock']) ? (int) $data['stock'] : null,
            isActive: (bool) ($data['is_active'] ?? true),
            attributes: (array) ($data['attributes'] ?? []),
            variants: (array) ($data['variants'] ?? []),
        );
    }
}
