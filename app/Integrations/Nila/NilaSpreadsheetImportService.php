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
    public function importNext(NilaImportBatch $batch, int $limit = 25, ?string $queueToken = null): array
    {
        $limit = max(1, min(50, $limit));

        $state = DB::transaction(function () use ($batch, $queueToken): array {
            $locked = NilaImportBatch::query()
                ->lockForUpdate()
                ->findOrFail($batch->id);

            if ($locked->status === 'completed') {
                throw new RuntimeException('این فایل قبلاً به پایان رسیده است.');
            }

            $metadata = $locked->metadata ?? [];
            $storedToken = (string) ($metadata['queue_token'] ?? '');

            if ($queueToken !== null) {
                if (
                    $locked->status !== 'queued'
                    || $storedToken === ''
                    || ! hash_equals($storedToken, $queueToken)
                ) {
                    return ['skip' => true];
                }
            } else {
                if ($locked->status === 'queued') {
                    throw new RuntimeException('این فایل در صف اجرا قرار دارد.');
                }

                if ($storedToken !== '') {
                    throw new RuntimeException('این فایل تحت مالکیت یک اجرای صف است؛ اجرای مستقیم مجاز نیست.');
                }

                if (
                    $locked->status === 'processing'
                    && $locked->updated_at?->greaterThan(now()->subMinutes(15))
                ) {
                    throw new RuntimeException('این فایل در حال پردازش است؛ کمی بعد دوباره تلاش کنید.');
                }
            }

            $locked->update(['status' => 'processing']);

            return [
                'disk' => $locked->disk,
                'path' => $locked->file_path,
                'offset' => $locked->next_group_offset,
                'metadata' => $metadata,
                'skip' => false,
            ];
        });

        if ($state['skip'] ?? false) {
            return [
                'processed' => 0,
                'imported' => 0,
                'failed' => 0,
                'next_offset' => (int) $batch->next_group_offset,
                'completed' => false,
                'skipped' => true,
            ];
        }

        try {
            $previewPath = $state['metadata']['preview_path'] ?? null;
            if (is_string($previewPath) && $previewPath !== '' && Storage::disk('local')->exists($previewPath)) {
                $parsed = json_decode(
                    Storage::disk('local')->get($previewPath),
                    true,
                    512,
                    JSON_THROW_ON_ERROR
                );
                if (! is_array($parsed) || ! isset($parsed['rows'], $parsed['groups'])) {
                    throw new RuntimeException('فایل پیش‌نمایش ذخیره‌شده معتبر نیست.');
                }
            } else {
                $file = Storage::disk($state['disk'])->path($state['path']);
                $parsed = $this->parser->parse($file);
            }
            $priceUnit = SpreadsheetPriceUnit::forBatch($state['metadata']);
            $groups = $parsed['groups'];
            $totalGroups = count($groups);
            $offset = (int) $state['offset'];

            if ($offset >= $totalGroups) {
                $this->finishBatch($batch->id, $offset, $totalGroups, 0, 0, [], null, $queueToken);

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
                $this->assertQueueOwnership($batch->id, $queueToken);

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
                            price: $priceUnit->toToman((float) $row['price']),
                            stock: (int) $row['stock'],
                            color: $row['color_candidate'],
                            metadata: [
                                'holoo_customer_code' => $row['customer_code'],
                                'source_row' => $row['row_number'],
                                'holoo_name' => $row['holoo_name'],
                                'import_batch_id' => $batch->id,
                            ],
                        ),
                        $productRows
                    );

                    $this->importer->import(new ExternalProductData(
                        externalId: $group['product_code'],
                        name: $group['product_name_candidate'],
                        variants: $variants,
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
                $completed,
                $queueToken
            );

            return [
                'processed' => count($chunk),
                'imported' => $imported,
                'failed' => $failed,
                'next_offset' => $nextOffset,
                'completed' => $completed,
            ];
        } catch (Throwable $exception) {
            DB::transaction(function () use ($batch, $queueToken): void {
                $locked = NilaImportBatch::query()
                    ->lockForUpdate()
                    ->find($batch->id);

                if (! $locked || $locked->status !== 'processing') {
                    return;
                }

                $metadata = $locked->metadata ?? [];
                $storedToken = (string) ($metadata['queue_token'] ?? '');

                $ownsBatch = $queueToken !== null
                    ? $storedToken !== '' && hash_equals($storedToken, $queueToken)
                    : $storedToken === '';

                if (! $ownsBatch) {
                    return;
                }

                // A queued Job keeps its lease while Laravel retries it.
                // The final failed() callback releases the lease after the
                // configured retry budget has been exhausted.
                if ($queueToken === null) {
                    unset($metadata['queue_token']);
                }

                $locked->update([
                    'status' => $queueToken !== null ? 'queued' : 'ready',
                    'metadata' => $metadata,
                ]);
            });

            throw $exception;
        }
    }

    /**
     * Refresh the queue lease before each product group. A stale worker whose
     * token has been replaced must stop before it can commit another group.
     */
    private function assertQueueOwnership(int $batchId, ?string $queueToken): void
    {
        if ($queueToken === null) {
            return;
        }

        DB::transaction(function () use ($batchId, $queueToken): void {
            $batch = NilaImportBatch::query()
                ->lockForUpdate()
                ->findOrFail($batchId);

            $metadata = $batch->metadata ?? [];
            $storedToken = (string) ($metadata['queue_token'] ?? '');

            if (
                $batch->status !== 'processing'
                || $storedToken === ''
                || ! hash_equals($storedToken, $queueToken)
            ) {
                throw new RuntimeException('این اجرای Import دیگر مالک فایل نیست؛ اجرای جدید حفظ شد.');
            }

            $metadata['last_queue_heartbeat_at'] = now()->toISOString();
            $batch->update(['metadata' => $metadata]);
        });
    }

    private function finishBatch(
        int $batchId,
        int $nextOffset,
        int $totalGroups,
        int $imported,
        int $failed,
        array $failures,
        ?bool $completed = null,
        ?string $queueToken = null
    ): void {
        DB::transaction(function () use (
            $batchId,
            $nextOffset,
            $totalGroups,
            $imported,
            $failed,
            $failures,
            $completed,
            $queueToken
        ): void {
            $batch = NilaImportBatch::query()
                ->lockForUpdate()
                ->findOrFail($batchId);

            if ($batch->status !== 'processing') {
                throw new RuntimeException('وضعیت Import پیش از ثبت نتیجه تغییر کرده است.');
            }

            $metadata = $batch->metadata ?? [];
            $storedToken = (string) ($metadata['queue_token'] ?? '');

            if (
                ($queueToken !== null && (
                    $storedToken === ''
                    || ! hash_equals($storedToken, $queueToken)
                ))
                || ($queueToken === null && $storedToken !== '')
            ) {
                throw new RuntimeException('مالکیت این اجرای Import تغییر کرده است؛ نتیجه قدیمی ثبت نشد.');
            }

            unset($metadata['queue_token']);
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
