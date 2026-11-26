<?php

namespace App\Console\Commands;

use App\Integrations\Nila\NilaSpreadsheetImportService;
use App\Models\NilaImportBatch;
use Illuminate\Console\Command;
use RuntimeException;

final class ImportNilaSpreadsheet extends Command
{
    protected $signature = 'nila:import-spreadsheet
        {batch : ID of the preview reviewed in the admin panel}
        {--batch-size=50 : Product groups per transaction batch (1-50)}
        {--confirm-stock-snapshot : Confirm the file contains final stock quantities}
        {--confirm-production : Confirm catalog writes in production}';

    protected $description = 'Import or resume a reviewed Holoo spreadsheet in bounded product groups';

    public function handle(NilaSpreadsheetImportService $importer): int
    {
        $id = filter_var($this->argument('batch'), FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1],
        ]);
        $limit = filter_var($this->option('batch-size'), FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1, 'max_range' => 50],
        ]);

        if ($id === false || $limit === false) {
            $this->error('Provide a positive batch ID and a batch size between 1 and 50.');

            return self::INVALID;
        }

        if (! $this->option('confirm-stock-snapshot')
            || (app()->isProduction() && ! $this->option('confirm-production'))) {
            $this->error('Review the admin preview, confirm the stock snapshot, and confirm production writes if applicable.');

            return self::INVALID;
        }

        $batch = NilaImportBatch::query()->find($id);

        if (! $batch) {
            $this->error('The reviewed import batch was not found.');

            return self::FAILURE;
        }

        if ($batch->status !== 'completed') {
            try {
                do {
                    $result = $importer->importNext($batch, $limit);
                    $this->line(sprintf(
                        '%d/%d groups processed (%d imported, %d failed in this batch).',
                        $result['next_offset'],
                        $batch->product_groups,
                        $result['imported'],
                        $result['failed']
                    ));
                } while (! $result['completed']);
            } catch (RuntimeException $exception) {
                $this->error($exception->getMessage());

                return self::FAILURE;
            }

            $batch->refresh();
        }

        $this->info(sprintf(
            'Import %d complete: %d groups imported, %d failed.',
            $batch->id,
            $batch->imported_groups,
            $batch->failed_groups
        ));

        return $batch->failed_groups === 0 ? self::SUCCESS : self::FAILURE;
    }
}
