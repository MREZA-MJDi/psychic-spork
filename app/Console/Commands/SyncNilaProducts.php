<?php

namespace App\Console\Commands;

use App\Integrations\Nila\Data\ExternalProductData;
use App\Integrations\Nila\HolooNilaCatalogSource;
use App\Integrations\Nila\NilaCatalogImporter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

final class SyncNilaProducts extends Command
{
    protected $signature = 'nila:sync-products
        {--dry-run : Fetch and preview data without writing to the database}
        {--limit= : Maximum number of products to preview or import}
        {--confirm-production : Required before writes in production}';

    protected $description = 'Fetch product data from the configured Holoo/Nila web service and optionally sync it into Janan';

    public function handle(
        HolooNilaCatalogSource $source,
        NilaCatalogImporter $importer
    ): int {
        if (! config('nila.enabled')) {
            $this->error('Holoo/Nila sync is disabled. Set NILA_ENABLED=true after configuring the service credentials.');

            return self::FAILURE;
        }

        $limit = $this->productLimit();
        if ($limit === false) {
            $this->error('--limit must be a positive integer.');

            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');

        if (app()->environment('production') && ! $dryRun && ! $this->option('confirm-production')) {
            $this->error('Production writes are blocked by default. Review a dry run first, then add --confirm-production to import.');

            return self::FAILURE;
        }

        try {
            if (! $dryRun) {
                $groups = $source->syncCategories();
                $this->info(sprintf(
                    'Category mappings ready: %d main groups, %d side groups.',
                    $groups['main_groups'],
                    $groups['side_groups']
                ));
            }

            $this->line('Configured Holoo price field: ' . config('nila.price_field', 'SellPrice'));
            $this->line('Price multiplier: ' . config('nila.price_multiplier', 1));
            $this->warn('Confirm the Holoo price unit matches Janan before writing catalog prices. New products are hidden by default; media remains managed in Janan.');

            $seen = 0;
            $imported = 0;
            $failed = 0;
            $previewRows = [];

            foreach ($source->fetchProducts() as $product) {
                if ($limit !== null && $seen >= $limit) {
                    break;
                }

                if (! $product instanceof ExternalProductData) {
                    $failed++;
                    $this->warn('Skipped a response item that could not be normalized.');

                    continue;
                }

                $seen++;
                $variant = $product->variants[0] ?? null;
                $holoo = $product->metadata;
                $previewRows[] = [
                    $product->externalId,
                    (string) ($holoo['product_code'] ?? $variant?->sku ?? ''),
                    $product->name,
                    $variant ? number_format($variant->price, 2, '.', ',') : '—',
                    $variant ? (string) $variant->stock : '—',
                    (string) ($holoo['side_group_name'] ?? '—'),
                ];

                if ($dryRun) {
                    continue;
                }

                try {
                    $importer->import($product);
                    $imported++;
                } catch (Throwable $exception) {
                    $failed++;
                    Log::error('Holoo/Nila product import failed.', [
                        'external_id' => $product->externalId,
                        'exception_class' => $exception::class,
                        'message' => $exception->getMessage(),
                    ]);
                    $this->warn('Import failed for external product ' . $product->externalId . '. Check the Laravel log for details.');
                }
            }

            if ($previewRows !== []) {
                $this->table(
                    ['Holoo ERP ID', 'Product code / SKU', 'Name', 'Price', 'Stock', 'Side group'],
                    array_slice($previewRows, 0, 20)
                );

                if (count($previewRows) > 20) {
                    $this->line('Preview table shows the first 20 rows.');
                }
            }

            if ($dryRun) {
                $this->info(sprintf('Dry run complete: fetched %d products; no catalog rows were written.', $seen));

                return $failed > 0 ? self::FAILURE : self::SUCCESS;
            }

            $this->info(sprintf('Holoo/Nila sync complete: %d imported/updated, %d failed.', $imported, $failed));

            if ($failed > 0) {
                $this->warn('The sync may have partially succeeded. Re-running is safe for mapped records; resolve logged errors before relying on the full catalog.');

                return self::FAILURE;
            }

            return self::SUCCESS;
        } catch (Throwable $exception) {
            Log::error('Holoo/Nila catalog sync could not complete.', [
                'exception_class' => $exception::class,
                'message' => $exception->getMessage(),
            ]);

            $this->error($exception->getMessage());
            $this->warn('No all-or-nothing guarantee exists across a full catalog run. Check the log and rerun only after resolving the connection or payload issue.');

            return self::FAILURE;
        }
    }

    private function productLimit(): int|false|null
    {
        $value = $this->option('limit');

        if ($value === null || $value === '') {
            return null;
        }

        $limit = filter_var($value, FILTER_VALIDATE_INT);

        return $limit !== false && $limit > 0 ? $limit : false;
    }
}
