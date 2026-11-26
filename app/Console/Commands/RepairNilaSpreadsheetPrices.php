<?php

namespace App\Console\Commands;

use App\Integrations\Nila\NilaSpreadsheetParser;
use App\Integrations\Nila\SpreadsheetPriceUnit;
use App\Models\IntegrationMapping;
use App\Models\NilaImportBatch;
use App\Models\ProductVariant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

final class RepairNilaSpreadsheetPrices extends Command
{
    protected $signature = 'nila:repair-rial-prices
        {batch : Completed spreadsheet import to reconcile with its source file}
        {--source-is-toman : Restore raw file prices without dividing by ten}
        {--apply : Write reviewed changes; by default only report them}
        {--legacy-decimal-saves : Include exact 100× and 10000× amounts from the old amount form}
        {--sku= : Restrict the repair to this SKU}
        {--expected-price= : Allow a specific current price for the selected SKU}';

    protected $description = 'Reconcile base prices against a completed spreadsheet without importing stock';

    public function handle(NilaSpreadsheetParser $parser): int
    {
        $id = filter_var($this->argument('batch'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $sku = $this->option('sku');
        $expected = $this->option('expected-price');
        $sourceIsToman = (bool) $this->option('source-is-toman');
        $legacyDecimalSaves = (bool) $this->option('legacy-decimal-saves');

        if ($id === false || ($expected !== null && (!$sku || !preg_match('/^\d+(?:\.\d{1,2})?$/D', $expected)))) {
            $this->error('Provide a positive batch ID. An expected price requires a SKU and a plain decimal amount.');

            return self::INVALID;
        }

        $batch = NilaImportBatch::query()->find($id);
        if (!$batch || $batch->status !== 'completed'
            || SpreadsheetPriceUnit::forBatch($batch->metadata ?? []) !== SpreadsheetPriceUnit::Toman) {
            $this->error('This repair requires a completed batch originally interpreted as toman.');

            return self::FAILURE;
        }

        $disk = Storage::disk($batch->disk);
        if (!$disk->exists($batch->file_path)) {
            $this->error('The original spreadsheet is unavailable.');

            return self::FAILURE;
        }

        $parsed = $parser->parse($disk->path($batch->file_path));
        $validRows = collect($parsed['rows'])->filter(fn (array $row): bool =>
            $row['is_valid'] && (!$sku || $row['holoo_code'] === $sku)
        )->keyBy('holoo_code');
        $apply = (bool) $this->option('apply');

        $report = DB::transaction(function () use ($batch, $validRows, $apply, $expected, $legacyDecimalSaves, $sourceIsToman): ?array {
            $lockedBatch = NilaImportBatch::query()->lockForUpdate()->findOrFail($batch->id);
            $metadata = $lockedBatch->metadata ?? [];
            if (!$sourceIsToman && ($metadata['confirmed_source_unit'] ?? null) === 'toman') {
                return null;
            }

            $report = ['changed' => 0, 'correct' => 0, 'review' => [], 'legacy' => []];

            foreach ($validRows->keys()->chunk(500) as $codes) {
                $mappings = IntegrationMapping::query()->where('integration', 'nila')->where('entity_type', ProductVariant::class)
                    ->whereIn('external_id', $codes->all())->orderBy('id')->lockForUpdate()->get();

                foreach ($mappings as $mapping) {
                    if ((int) ($mapping->metadata['import_batch_id'] ?? 0) !== $batch->id) {
                        continue;
                    }

                    $row = $validRows->get($mapping->external_id);
                    $variant = ProductVariant::query()->lockForUpdate()->find($mapping->entity_id);
                    if (!$variant || $variant->sku !== $row['holoo_code']) {
                        continue;
                    }

                    $source = round((float) $row['price'], 2);
                    $converted = SpreadsheetPriceUnit::Rial->toToman((float) $row['price']);
                    $target = $sourceIsToman ? $source : $converted;
                    $current = (float) $variant->price;

                    if ($current === $target) {
                        $report['correct']++;
                        continue;
                    }

                    $expectedCurrent = $expected === null ? ($sourceIsToman ? $converted : $source) : (float) $expected;
                    $legacyAmount = $expected === null && $legacyDecimalSaves
                        && in_array($current, [$source * 100, $source * 10000], true);

                    if (($current !== $expectedCurrent && !$legacyAmount)
                        || ($variant->sale_price !== null && (float) $variant->sale_price >= $target)
                        || ($variant->wholesale_price !== null && (float) $variant->wholesale_price > $target)) {
                        $report['review'][] = [$variant->sku, $source, $current, $target];
                        continue;
                    }

                    if ($legacyAmount) {
                        $report['legacy'][] = [$variant->sku, $source, $current, $target];
                    }

                    if ($apply) {
                        $variant->update(['price' => $target]);
                    }
                    $report['changed']++;
                }
            }

            if ($apply && $sourceIsToman && $validRows->isNotEmpty() && !$this->option('sku')) {
                $metadata['confirmed_source_unit'] = 'toman';
                $lockedBatch->update(['metadata' => $metadata]);
            }

            return $report;
        }, 3);

        if ($report === null) {
            $this->error('The source of this batch was confirmed as toman. Use --source-is-toman.');
            return self::FAILURE;
        }

        $columns = ['SKU', $sourceIsToman ? 'Source toman' : 'Source rial', 'Previous toman', 'Expected toman'];

        $this->info(sprintf('%s: %d; already correct: %d; needs review: %d.',
            $apply ? 'Updated' : 'Would update', $report['changed'], $report['correct'], count($report['review'])
        ));
        if ($report['legacy']) {
            $this->line($apply ? 'Corrected decimal-save amounts:' : 'Decimal-save amounts proposed for correction:');
            $this->table($columns, $report['legacy']);
        }
        if ($report['review']) {
            $this->table($columns, $report['review']);
        }

        return self::SUCCESS;
    }
}
