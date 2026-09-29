<?php

namespace App\Jobs;

use App\Integrations\Nila\Data\ExternalProductData;
use App\Integrations\Nila\NilaCatalogImporter;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

final class ImportNilaProductBatch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public array $products
    ) {
    }

    public function handle(NilaCatalogImporter $importer): void
    {
        foreach ($this->products as $product) {
            if (! $product instanceof ExternalProductData) {
                continue;
            }

            try {
                $importer->import($product);
            } catch (\Throwable $e) {
                Log::error('Nila product import failed.', [
                    'external_id' => $product->externalId,
                    'name' => $product->name,
                    'exception' => $e,
                ]);
            }
        }
    }
}
