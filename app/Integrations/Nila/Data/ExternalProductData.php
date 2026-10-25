<?php

namespace App\Integrations\Nila\Data;

final readonly class ExternalProductData
{
    /**
     * Normalized data from an external catalog provider.
     *
     * Nullable fields mean the provider doesn't own or didn't supply that field.
     * The importer uses this to preserve store-managed content during sync.
     *
     * @param array<int, ExternalVariantData> $variants
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
        public ?bool $isActive = null,
        public ?bool $isFeatured = null,
        public ?int $sortOrder = null,
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
