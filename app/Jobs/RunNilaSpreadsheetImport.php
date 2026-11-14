<?php

namespace App\Jobs;

use App\Integrations\Nila\NilaSpreadsheetImportService;
use App\Models\NilaImportBatch;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Throwable;

final class RunNilaSpreadsheetImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public int $timeout = 300;

    public function __construct(
        public int $batchId,
        public int $limit,
        public string $token
    ) {
    }

    public function handle(NilaSpreadsheetImportService $service): void
    {
        $batch = NilaImportBatch::query()->findOrFail($this->batchId);
        $service->importNext($batch, $this->limit, $this->token);
    }

    public function failed(Throwable $exception): void
    {
        report($exception);

        DB::transaction(function (): void {
            $batch = NilaImportBatch::query()
                ->lockForUpdate()
                ->find($this->batchId);

            if (! $batch) {
                return;
            }

            $metadata = $batch->metadata ?? [];

            if (($metadata['queue_token'] ?? null) !== $this->token) {
                return;
            }

            unset($metadata['queue_token']);

            $batch->update([
                'status' => 'ready',
                'metadata' => $metadata,
            ]);
        });
    }
}
