<?php

namespace Tests\Feature;

use App\Models\NilaImportBatch;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class NilaSpreadsheetPreviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_upload_csv_for_preview_without_creating_catalog_records(): void
    {
        Storage::fake('local');

        $admin = User::factory()->create(['is_admin' => true]);
        $csv = implode("\n", [
            'holooCode,holooName,holooRegularPrice,holooStockQuantity,holooCustomerCode',
            '0102001*1,80 کش پهن دانتل 5 سانتی-بادمجونی,2150000,4,1',
            '0102001*2,80 کش پهن دانتل 5 سانتی-طوسی,2150000,0,1',
        ]);

        $response = $this->actingAs($admin)->post(
            route('admin.nila.imports.store'),
            ['file' => UploadedFile::fake()->createWithContent('holoo.csv', $csv)]
        );

        $batch = NilaImportBatch::query()->firstOrFail();

        $response->assertRedirect(route('admin.nila.imports.show', $batch));

        $this->assertSame(2, $batch->total_rows);
        $this->assertSame(2, $batch->valid_rows);
        $this->assertSame(0, $batch->invalid_rows);
        $this->assertSame(1, $batch->product_groups);
        $this->assertSame('preview_ready', $batch->status);

        $this->assertDatabaseCount('products', 0);
        $this->assertDatabaseCount('product_variants', 0);

        $this->actingAs($admin)
            ->get(route('admin.nila.imports.show', $batch))
            ->assertOk()
            ->assertSee('0102001*1')
            ->assertSee('بادمجونی')
            ->assertSee('هنوز هیچ محصولی وارد دیتابیس نشده');
    }

    public function test_blank_price_is_reported_as_invalid_and_is_not_converted_to_zero(): void
    {
        Storage::fake('local');

        $admin = User::factory()->create(['is_admin' => true]);
        $csv = implode("\n", [
            'holooCode,holooName,holooRegularPrice,holooStockQuantity,holooCustomerCode',
            '0102001*1,80 کش پهن دانتل 5 سانتی-بادمجونی,,4,1',
        ]);

        $this->actingAs($admin)->post(
            route('admin.nila.imports.store'),
            ['file' => UploadedFile::fake()->createWithContent('holoo.csv', $csv)]
        )->assertRedirect();

        $batch = NilaImportBatch::query()->firstOrFail();

        $this->assertSame(0, $batch->valid_rows);
        $this->assertSame(1, $batch->invalid_rows);
        $this->assertDatabaseCount('products', 0);
        $this->assertDatabaseCount('product_variants', 0);
    }
}
