<?php

namespace Tests\Feature;

use App\Integrations\Nila\NilaSpreadsheetImportService;
use App\Jobs\RunNilaSpreadsheetImport;
use App\Models\NilaImportBatch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Tests\TestCase;

class NilaImportQueueOwnershipTest extends TestCase
{
    use RefreshDatabase;

    public function test_stale_job_cannot_start_after_a_new_queue_token_has_been_issued(): void
    {
        $batch = $this->queuedBatch('new-execution-token');

        $result = app(NilaSpreadsheetImportService::class)
            ->importNext($batch, 5, 'old-execution-token');

        $this->assertTrue($result['skipped']);
        $this->assertSame('queued', $batch->fresh()->status);
        $this->assertSame(
            'new-execution-token',
            $batch->fresh()->metadata['queue_token']
        );
    }

    public function test_failed_old_job_cannot_reset_a_newer_queued_execution(): void
    {
        $batch = $this->queuedBatch('new-execution-token');

        $job = new RunNilaSpreadsheetImport(
            $batch->id,
            5,
            'old-execution-token'
        );

        $job->failed(new RuntimeException('Old worker timed out'));

        $this->assertSame('queued', $batch->fresh()->status);
        $this->assertSame(
            'new-execution-token',
            $batch->fresh()->metadata['queue_token']
        );
    }

    public function test_import_exception_releases_only_the_execution_that_owns_the_batch(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('nila/previews/broken.json', '{ invalid');

        $batch = NilaImportBatch::query()->create([
            'original_name' => 'broken-preview.xlsx',
            'disk' => 'local',
            'file_path' => 'nila/imports/broken.xlsx',
            'status' => 'queued',
            'total_rows' => 1,
            'valid_rows' => 1,
            'invalid_rows' => 0,
            'product_groups' => 1,
            'metadata' => [
                'preview_only' => true,
                'preview_path' => 'nila/previews/broken.json',
                'queue_token' => 'current-execution-token',
            ],
        ]);

        try {
            app(NilaSpreadsheetImportService::class)->importNext(
                $batch,
                5,
                'current-execution-token'
            );
            $this->fail('The invalid cached preview must fail the job.');
        } catch (\JsonException $exception) {
            $this->assertNotEmpty($exception->getMessage());
        }

        $batch->refresh();
        $this->assertSame('ready', $batch->status);
        $this->assertArrayNotHasKey('queue_token', $batch->metadata);
    }

    private function queuedBatch(string $token): NilaImportBatch
    {
        return NilaImportBatch::query()->create([
            'original_name' => 'catalog.csv',
            'disk' => 'local',
            'file_path' => 'nila/imports/catalog.csv',
            'status' => 'queued',
            'total_rows' => 0,
            'valid_rows' => 0,
            'invalid_rows' => 0,
            'product_groups' => 0,
            'metadata' => [
                'preview_only' => true,
                'queue_token' => $token,
            ],
        ]);
    }
}
