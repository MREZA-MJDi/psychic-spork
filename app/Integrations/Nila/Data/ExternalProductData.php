<?php

namespace App\Integrations\Nila\Data;

final readonly class ExternalProductData
{
    /**
     * This is the normalized contract between Nila and Janan.
     *
     * The HTTP/API response is intentionally not represented here. A Nila
     * adapter can map whatever the real provider returns into this DTO later.
     */
    public function __construct(
        public string $externalId,
        public string $name,
        public array $variants,
        public ?string $slug = null,
        public ?string $shortDescription = null,
        public ?string $description = null,
        public ?array $attributes = null,
        public ?int $categoryId = null,
        public ?int $brandId = null,
        public bool $isActive = true,
        public bool $isFeatured = false,
        public int $sortOrder = 0,
        public array $metadata = [],
    ) {
        if ($externalId === '') {
            throw new \InvalidArgumentException('Nila product externalId is required.');
        }

        if (trim($name) === '') {
            throw new \InvalidArgumentException('Nila product name is required.');
        }

        foreach ($variants as $variant) {
            if (! $variant instanceof ExternalVariantData) {
                throw new \InvalidArgumentException(
                    'Nila product variants must contain ExternalVariantData instances.'
                );
            }
        }
    }
}
