<?php

namespace App\Integrations\Nila\Data;

final readonly class ExternalVariantData
{
    public function __construct(
        public string $externalId,
        public string $sku,
        public float $price,
        public ?float $salePrice = null,
        public int $stock = 0,
        public ?string $size = null,
        public ?string $color = null,
        public ?string $colorCode = null,
        public bool $isActive = true,
        public int $sortOrder = 0,
        public array $metadata = [],
    ) {
        if ($externalId === '') {
            throw new \InvalidArgumentException('Nila variant externalId is required.');
        }

        if ($sku === '') {
            throw new \InvalidArgumentException('Nila variant SKU is required.');
        }

        if ($price < 0 || ($salePrice !== null && $salePrice < 0)) {
            throw new \InvalidArgumentException('Nila variant prices cannot be negative.');
        }

        if ($stock < 0) {
            throw new \InvalidArgumentException('Nila variant stock cannot be negative.');
        }
    }
}
