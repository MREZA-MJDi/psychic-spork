<?php

namespace App\Integrations\Nila;

use App\Integrations\Nila\Data\ExternalProductData;
use App\Integrations\Nila\Data\ExternalVariantData;
use App\Integrations\Nila\NilaCatalogSource;
use App\Models\Category;
use App\Models\IntegrationMapping;
use Illuminate\Support\Str;
use RuntimeException;

final class HolooNilaCatalogSource implements NilaCatalogSource
{
    private const PRICE_FIELDS = [
        'SellPrice',
        'SellPrice2',
        'SellPrice3',
        'SellPrice4',
        'SellPrice5',
        'SellPrice6',
        'SellPrice7',
        'SellPrice8',
        'SellPrice9',
        'SellPrice10',
        'selpricekarton',
    ];

    public function __construct(
        private readonly HolooWebServiceClient $client,
    ) {
    }

    /**
     * Sync Holoo main/side groups to dedicated categories before importing products.
     *
     * Existing categories are touched only when they already have a Nila mapping;
     * a matching display name alone is never used to adopt a hand-managed category.
     *
     * @return array{main_groups:int,side_groups:int}
     */
    public function syncCategories(): array
    {
        $mainCategoryIds = [];
        $mainGroups = $this->client->getMainGroups();

        foreach ($mainGroups as $group) {
            $externalId = trim((string) $this->value($group, 'ErpCode', ''));
            $name = trim((string) $this->value($group, 'Name', ''));

            if ($externalId === '' || $name === '') {
                continue;
            }

            $category = $this->syncCategory(
                externalId: $externalId,
                name: $name,
                parentId: null,
                metadata: ['group_type' => 'main']
            );

            $mainCategoryIds[$externalId] = $category->id;
        }

        $sideGroups = $this->client->getSideGroups();
        $syncedSideGroups = 0;

        foreach ($sideGroups as $group) {
            $externalId = trim((string) $this->value($group, 'ErpCode', ''));
            $name = trim((string) $this->value($group, 'Name', ''));
            $mainExternalId = trim((string) $this->value($group, 'MainErpCode', ''));

            if ($externalId === '' || $name === '') {
                continue;
            }

            $parentId = $mainCategoryIds[$mainExternalId]
                ?? $this->mappedCategoryId($mainExternalId);

            $this->syncCategory(
                externalId: $externalId,
                name: $name,
                parentId: $parentId,
                metadata: [
                    'group_type' => 'side',
                    'main_erp_code' => $mainExternalId ?: null,
                    'main_group_name' => $this->value($group, 'MainGroupName'),
                ]
            );
            $syncedSideGroups++;
        }

        return [
            'main_groups' => count($mainCategoryIds),
            'side_groups' => $syncedSideGroups,
        ];
    }

    /**
     * @return iterable<ExternalProductData>
     */
    public function fetchProducts(): iterable
    {
        $pageSize = max(1, min(500, (int) config('nila.page_size', 100)));
        $maxPages = max(1, (int) config('nila.max_pages', 1000));
        $priceField = (string) config('nila.price_field', 'SellPrice');

        if (! in_array($priceField, self::PRICE_FIELDS, true)) {
            throw new RuntimeException(
                'NILA_PRICE_FIELD must be one of SellPrice, SellPrice2-SellPrice10 or selpricekarton.'
            );
        }

        $multiplier = (float) config('nila.price_multiplier', 1);
        if (! is_finite($multiplier) || $multiplier <= 0) {
            throw new RuntimeException('NILA_PRICE_MULTIPLIER must be a finite number greater than zero.');
        }

        $seen = [];
        for ($page = 1; $page <= $maxPages; $page++) {
            $rows = $this->client->getProductsPage($page, $pageSize);

            if ($rows === []) {
                return;
            }

            $newIdsOnPage = 0;
            $validRowsOnPage = 0;

            foreach ($rows as $row) {
                $externalId = trim((string) $this->value($row, 'ErpCode', ''));
                $name = trim((string) $this->value($row, 'Name', ''));

                if ($externalId === '' || $name === '') {
                    throw new RuntimeException(
                        'Holoo returned a product row without ErpCode or Name on page ' . $page . '. No fallback ID was guessed.'
                    );
                }

                $validRowsOnPage++;

                // ErpCode is Holoo's stable record identifier; do not guess an ID.
                if (isset($seen[$externalId])) {
                    continue;
                }

                $seen[$externalId] = true;
                $newIdsOnPage++;

                $sku = trim((string) $this->value($row, 'Code', ''));
                if ($sku === '') {
                    $sku = 'HOL-' . strtoupper(substr(hash('sha256', $externalId), 0, 16));
                }

                $rawPrice = $this->numericString($this->value($row, $priceField, null));
                if (! is_numeric($rawPrice)) {
                    throw new RuntimeException(
                        'Holoo product ' . $externalId . ' has no numeric value for configured price field ' . $priceField . '.'
                    );
                }

                $price = round((float) $rawPrice * $multiplier, 2);
                if (! is_finite($price) || $price < 0) {
                    throw new RuntimeException('Holoo product ' . $externalId . ' has an invalid price.');
                }

                $rawStock = $this->numericString($this->value($row, 'Few', 0));
                if (! is_numeric($rawStock)) {
                    throw new RuntimeException('Holoo product ' . $externalId . ' has an invalid stock quantity.');
                }

                // The local schema uses unsigned integer quantities. Flooring
                // prevents a fractional ERP quantity from overstating sellable stock.
                $stock = max(0, (int) floor((float) $rawStock));
                $sideGroupCode = trim((string) $this->value($row, 'SideGroupErpCode', ''));
                $mainGroupCode = trim((string) $this->value($row, 'MainGroupErpCode', ''));

                yield new ExternalProductData(
                    externalId: $externalId,
                    name: $name,
                    variants: [
                        new ExternalVariantData(
                            externalId: $externalId,
                            sku: $sku,
                            price: $price,
                            stock: $stock,
                            // The documented response doesn't define size/color/status.
                            size: null,
                            color: null,
                            colorCode: null,
                            isActive: null,
                            sortOrder: null,
                            metadata: [
                                'provider' => 'holoo',
                                'erp_code' => $externalId,
                                'product_code' => $sku,
                                'price_field' => $priceField,
                            ]
                        ),
                    ],
                    categoryId: $this->mappedCategoryId($sideGroupCode)
                        ?? $this->mappedCategoryId($mainGroupCode),
                    // The documented endpoint doesn't return storefront-managed fields.
                    isActive: null,
                    isFeatured: null,
                    sortOrder: null,
                    metadata: [
                        'provider' => 'holoo',
                        'product_code' => $sku,
                        'main_group_erp_code' => $mainGroupCode ?: null,
                        'main_group_name' => $this->value($row, 'MainGroupName'),
                        'side_group_erp_code' => $sideGroupCode ?: null,
                        'side_group_name' => $this->value($row, 'SideGroupName'),
                        'price_field' => $priceField,
                    ]
                );
            }

            if ($validRowsOnPage === 0 || $newIdsOnPage === 0 || count($rows) < $pageSize) {
                return;
            }

            if ($page === $maxPages) {
                throw new RuntimeException(
                    'Holoo page safety limit reached. Increase NILA_MAX_PAGES only after confirming the catalog size.'
                );
            }
        }
    }

    private function syncCategory(
        string $externalId,
        string $name,
        ?int $parentId,
        array $metadata
    ): Category {
        $mapping = $this->mapping(Category::class, $externalId);
        $category = $mapping
            ? Category::withTrashed()->find($mapping->entity_id)
            : null;

        if (! $category) {
            $slugBase = Str::slug($name) ?: 'holoo-category';
            $slug = substr($slugBase, 0, 170) . '-h-' . substr(hash('sha256', $externalId), 0, 10);

            $slugOwner = Category::withTrashed()->where('slug', $slug)->first();
            if ($slugOwner) {
                throw new RuntimeException(
                    'Cannot safely map Holoo category ' . $externalId . ': generated slug is already in use.'
                );
            }

            $category = new Category;
            $category->fill([
                'name' => $name,
                'slug' => $slug,
                'parent_id' => $parentId,
                'is_active' => true,
                'sort_order' => 0,
            ]);
        } else {
            if ($category->trashed()) {
                $category->restore();
            }

            $category->fill([
                'name' => $name,
                'parent_id' => $parentId,
            ]);
        }

        $category->save();

        $payload = $metadata + ['provider' => 'holoo'];
        if ($mapping) {
            $mapping->update([
                'entity_id' => $category->id,
                'metadata' => $payload,
            ]);
        } else {
            IntegrationMapping::query()->create([
                'integration' => 'nila',
                'entity_type' => Category::class,
                'entity_id' => $category->id,
                'external_id' => $externalId,
                'metadata' => $payload,
            ]);
        }

        return $category;
    }

    private function mappedCategoryId(string $externalId): ?int
    {
        if ($externalId === '') {
            return null;
        }

        $mapping = $this->mapping(Category::class, $externalId);

        if (! $mapping) {
            return null;
        }

        $category = Category::query()->find($mapping->entity_id);

        return $category?->id;
    }

    private function mapping(string $entityType, string $externalId): ?IntegrationMapping
    {
        return IntegrationMapping::query()
            ->where('integration', 'nila')
            ->where('entity_type', $entityType)
            ->where('external_id', $externalId)
            ->first();
    }

    private function value(array $array, string $key, mixed $default = null): mixed
    {
        if (array_key_exists($key, $array)) {
            return $array[$key];
        }

        foreach ($array as $candidate => $value) {
            if (is_string($candidate) && strcasecmp($candidate, $key) === 0) {
                return $value;
            }
        }

        return $default;
    }

    private function numericString(mixed $value): string
    {
        $string = trim((string) $value);
        $string = strtr($string, [
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
            '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
            ',' => '', '٬' => '', ' ' => '',
        ]);

        return $string;
    }
}
