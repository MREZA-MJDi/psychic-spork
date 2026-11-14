<?php

namespace App\Integrations\Nila;

use App\Integrations\Nila\Data\ExternalProductData;
use App\Integrations\Nila\Data\ExternalVariantData;
use App\Models\NilaImportBatch;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

final class NilaSpreadsheetImportService
{
    public function __construct(
        private readonly NilaSpreadsheetParser $parser,
        private readonly NilaCatalogImporter $importer,
    ) {
    }

    /**
     * Import a bounded number of complete product groups.
     *
     * A product group is atomic: if one of its variants is invalid, none of
     * that product's variants are written. Each valid product is imported in
     * its own transaction by NilaCatalogImporter.
     *
     * @return array{processed: int, imported: int, failed: int, next_offset: int, completed: bool}
     */
    public function importNext(NilaImportBatch $batch, int $limit = 5): array
    {
        $limit = max(1, min(50, $limit));

        $state = DB::transaction(function () use ($batch): array {
            $locked = NilaImportBatch::query()
                ->lockForUpdate()
                ->findOrFail($batch->id);

            if ($locked->status === 'completed') {
                throw new RuntimeException('این فایل قبلاً به پایان رسیده است.');
            }

            if (
                $locked->status === 'processing'
                && $locked->updated_at?->greaterThan(now()->subMinutes(15))
            ) {
                throw new RuntimeException('این فایل در حال پردازش است؛ کمی بعد دوباره تلاش کنید.');
            }

            $locked->update(['status' => 'processing']);

            return [
                'disk' => $locked->disk,
                'path' => $locked->file_path,
                'offset' => $locked->next_group_offset,
                'metadata' => $locked->metadata ?? [],
            ];
        });

        try {
            $file = Storage::disk($state['disk'])->path($state['path']);
            $parsed = $this->parser->parse($file);
            $groups = $parsed['groups'];
            $totalGroups = count($groups);
            $offset = (int) $state['offset'];

            if ($offset >= $totalGroups) {
                $this->finishBatch($batch->id, $offset, $totalGroups, 0, 0, []);

                return [
                    'processed' => 0,
                    'imported' => 0,
                    'failed' => 0,
                    'next_offset' => $offset,
                    'completed' => true,
                ];
            }

            $rowsByProduct = [];
            foreach ($parsed['rows'] as $row) {
                $rowsByProduct[$row['product_code']][] = $row;
            }

            $chunk = array_slice($groups, $offset, $limit);
            $imported = 0;
            $failed = 0;
            $failures = [];

            foreach ($chunk as $group) {
                $productRows = $rowsByProduct[$group['product_code']] ?? [];

                if (
                    $group['name_conflict']
                    || $group['invalid_rows'] > 0
                    || $productRows === []
                ) {
                    $failed++;
                    $failures[] = [
                        'product_code' => $group['product_code'],
                        'reason' => 'حداقل یک تنوع این محصول دارای داده نامعتبر است؛ کل گروه از ورود کنار گذاشته شد.',
                    ];
                    continue;
                }

                try {
                    $variants = array_map(
                        fn (array $row) => new ExternalVariantData(
                            externalId: $row['holoo_code'],
                            sku: $row['holoo_code'],
                            price: (float) $row['price'],
                            stock: (int) $row['stock'],
                            color: $row['color_candidate'],
                            isActive: false,
                            metadata: [
                                'holoo_customer_code' => $row['customer_code'],
                                'source_row' => $row['row_number'],
                                'holoo_name' => $row['holoo_name'],
                            ],
                        ),
                        $productRows
                    );

                    $this->importer->import(new ExternalProductData(
                        externalId: $group['product_code'],
                        name: $group['product_name_candidate'],
                        variants: $variants,
                        isActive: false,
                        metadata: [
                            'source' => 'holoo_spreadsheet',
                            'import_batch_id' => $batch->id,
                        ],
                    ));

                    $imported++;
                } catch (Throwable $exception) {
                    report($exception);
                    $failed++;
                    $failures[] = [
                        'product_code' => $group['product_code'],
                        'reason' => 'ثبت گروه محصول انجام نشد؛ جزئیات فنی در گزارش خطای سرور ثبت شده است.',
                    ];
                }
            }

            $nextOffset = $offset + count($chunk);
            $completed = $nextOffset >= $totalGroups;

            $this->finishBatch(
                $batch->id,
                $nextOffset,
                $totalGroups,
                $imported,
                $failed,
                $failures,
                $completed
            );

            return [
                'processed' => count($chunk),
                'imported' => $imported,
                'failed' => $failed,
                'next_offset' => $nextOffset,
                'completed' => $completed,
            ];
        } catch (Throwable $exception) {
            NilaImportBatch::query()
                ->whereKey($batch->id)
                ->where('status', 'processing')
                ->update(['status' => 'ready', 'updated_at' => now()]);

            throw $exception;
        }
    }

    private function finishBatch(
        int $batchId,
        int $nextOffset,
        int $totalGroups,
        int $imported,
        int $failed,
        array $failures,
        ?bool $completed = null
    ): void {
        DB::transaction(function () use (
            $batchId,
            $nextOffset,
            $totalGroups,
            $imported,
            $failed,
            $failures,
            $completed
        ): void {
            $batch = NilaImportBatch::query()
                ->lockForUpdate()
                ->findOrFail($batchId);

            $metadata = $batch->metadata ?? [];
            $existingFailures = $metadata['failed_products'] ?? [];
            $metadata['failed_products'] = array_slice(
                array_merge($existingFailures, $failures),
                -100
            );
            $metadata['preview_only'] = false;
            $metadata['last_imported_at'] = now()->toISOString();

            $batch->update([
                'status' => ($completed ?? ($nextOffset >= $totalGroups))
                    ? 'completed'
                    : 'ready',
                'next_group_offset' => $nextOffset,
                'imported_groups' => $batch->imported_groups + $imported,
                'failed_groups' => $batch->failed_groups + $failed,
                'metadata' => $metadata,
            ]);
        });
    }
}
