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
        {batch : Completed spreadsheet import that used rial figures as toman}
        {--apply : Write reviewed changes; by default only report them}
        {--sku= : Restrict the repair to this SKU}
        {--expected-price= : Allow a specific current price for the selected SKU}';

    protected $description = 'Correct base prices from a completed legacy rial spreadsheet without importing stock';

    public function handle(NilaSpreadsheetParser $parser): int
    {
        $id = filter_var($this->argument('batch'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $sku = $this->option('sku');
        $expected = $this->option('expected-price');

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

        $report = DB::transaction(function () use ($batch, $validRows, $apply, $expected): array {
            $report = ['changed' => 0, 'correct' => 0, 'review' => []];

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
                    $target = SpreadsheetPriceUnit::Rial->toToman((float) $row['price']);
                    $current = (float) $variant->price;

                    if ($current === $target) {
                        $report['correct']++;
                        continue;
                    }

                    $expectedCurrent = $expected === null ? $source : (float) $expected;
                    if ($current !== $expectedCurrent
                        || ($variant->sale_price !== null && (float) $variant->sale_price >= $target)
                        || ($variant->wholesale_price !== null && (float) $variant->wholesale_price > $target)) {
                        $report['review'][] = [$variant->sku, $source, $current, $target];
                        continue;
                    }

                    if ($apply) {
                        $variant->update(['price' => $target]);
                    }
                    $report['changed']++;
                }
            }

            return $report;
        }, 3);

        $this->info(sprintf('%s: %d; already correct: %d; needs review: %d.',
            $apply ? 'Updated' : 'Would update', $report['changed'], $report['correct'], count($report['review'])
        ));
        if ($report['review']) {
            $this->table(['SKU', 'Source rial', 'Current toman', 'Expected toman'], $report['review']);
        }

        return self::SUCCESS;
    }
}
