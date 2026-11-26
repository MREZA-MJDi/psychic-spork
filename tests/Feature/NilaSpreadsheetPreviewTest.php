<?php

namespace Tests\Feature;

use App\Models\NilaImportBatch;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
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
            ->assertSee('هنوز هیچ محصولی وارد دیتابیس نشده')
            ->assertSee('۲۵ گروه محصول (پیشنهادی)')
            ->assertSee('۵۰ گروه محصول (حداکثر هر نوبت)')
            ->assertSee('موجودی نیازمند تطبیق')
            ->assertSee('موجودی فایل، مقدار نهایی موجودی');
    }

    public function test_admin_can_preview_legacy_xls_and_missing_prices_are_preserved(): void
    {
        Storage::fake('local');

        $admin = User::factory()->create(['is_admin' => true]);
        $workbook = new Spreadsheet();
        $workbook->getActiveSheet()->fromArray([
            ['holooCode', 'holooName', 'holooRegularPrice', 'holooStockQuantity', 'holooCustomerCode'],
            ['0102001*1', 'محصول - مشکی', 2150000, 4, 1],
            ['0102001*2', 'محصول - سفید', null, 0, 1],
        ]);
        $file = tmpfile();

        try {
            (new Xls($workbook))->save(stream_get_meta_data($file)['uri']);
            $response = $this->actingAs($admin)->post(route('admin.nila.imports.store'), [
                'file' => new UploadedFile(stream_get_meta_data($file)['uri'], 'product.xls', null, null, true),
            ]);

            $batch = NilaImportBatch::query()->firstOrFail();
            $response->assertRedirect(route('admin.nila.imports.show', $batch));
            $this->assertSame(2, $batch->total_rows);
            $this->assertSame(1, $batch->invalid_rows);
            $this->assertSame(1, $batch->product_groups);
            $this->get(route('admin.nila.imports.show', $batch))
                ->assertOk()
                ->assertSee('ردیف‌های دارای قیمت خالی یا نامعتبر')
                ->assertViewHas('comparison', fn (array $counts): bool =>
                    $counts['valid_groups'] === 0
                    && $counts['blocked_groups'] === 1
                    && $counts['invalid_price_rows'] === 1
                );
            $this->assertDatabaseCount('products', 0);
        } finally {
            fclose($file);
            $workbook->disconnectWorksheets();
        }
    }

    public function test_cli_resumes_a_reviewed_spreadsheet_in_bounded_batches(): void
    {
        Storage::fake('local');

        $admin = User::factory()->create(['is_admin' => true]);
        $csv = implode("\n", [
            'holooCode,holooName,holooRegularPrice,holooStockQuantity,holooCustomerCode',
            '0102001*1,First product,2150000,4,1',
            '0102002*1,Second product,2500000,2,1',
            '0102003*1,Third product,3200000,0,1',
        ]);

        $this->actingAs($admin)->post(
            route('admin.nila.imports.store'),
            ['file' => UploadedFile::fake()->createWithContent('holoo.csv', $csv)]
        )->assertRedirect();

        $batch = NilaImportBatch::query()->firstOrFail();
        $arguments = ['batch' => $batch->id, '--batch-size' => 1];

        $this->artisan('nila:import-spreadsheet', $arguments)->assertExitCode(2);
        $this->assertDatabaseCount('products', 0);

        $this->post(route('admin.nila.imports.import', $batch), [
            'batch_size' => 1,
            'confirm_stock_snapshot' => 1,
        ])->assertRedirect();
        $this->assertSame(1, $batch->fresh()->next_group_offset);

        $arguments['--confirm-stock-snapshot'] = true;
        $this->artisan('nila:import-spreadsheet', $arguments)
            ->expectsOutput('2/3 groups processed (1 imported, 0 failed in this batch).')
            ->expectsOutput('3/3 groups processed (1 imported, 0 failed in this batch).')
            ->assertExitCode(0);
        $this->artisan('nila:import-spreadsheet', $arguments)->assertExitCode(0);

        $this->assertDatabaseCount('products', 3);
        $this->assertDatabaseCount('product_variants', 3);
        $this->assertDatabaseCount('media', 0);
        $this->assertSame(0, Product::query()->active()->count());
        $this->assertSame('completed', $batch->fresh()->status);

        $variant = ProductVariant::query()->where('sku', '0102001*1')->firstOrFail();
        $edited = ProductVariant::query()->where('sku', '0102002*1')->firstOrFail();
        $edited->update(['price' => 250000000]);
        $variant->update(['stock' => 9]);

        $repair = ['batch' => $batch->id];
        $this->artisan('nila:repair-rial-prices', $repair)
            ->expectsOutput('Would update: 2; already correct: 0; needs review: 1.')
            ->assertExitCode(0);
        $this->artisan('nila:repair-rial-prices', $repair + ['--legacy-decimal-saves' => true])
            ->expectsOutput('Would update: 3; already correct: 0; needs review: 0.')
            ->assertExitCode(0);
        $this->assertEquals(2150000, $variant->fresh()->price);

        $this->artisan('nila:repair-rial-prices', $repair + ['--apply' => true])
            ->expectsOutput('Updated: 2; already correct: 0; needs review: 1.')
            ->assertExitCode(0);
        $this->assertEquals(215000, $variant->fresh()->price);
        $this->assertEquals(9, $variant->fresh()->stock);
        $this->assertEquals(250000000, $edited->fresh()->price);

        $this->artisan('nila:repair-rial-prices', $repair + [
            '--apply' => true,
            '--legacy-decimal-saves' => true,
        ])->assertExitCode(0);
        $this->assertEquals(250000, $edited->fresh()->price);
        $this->artisan('nila:repair-rial-prices', $repair)
            ->expectsOutput('Would update: 0; already correct: 3; needs review: 0.')
            ->assertExitCode(0);

        $restore = $repair + ['--source-is-toman' => true];
        $this->artisan('nila:repair-rial-prices', $restore)
            ->expectsOutput('Would update: 3; already correct: 0; needs review: 0.')
            ->assertExitCode(0);
        $this->assertEquals(215000, $variant->fresh()->price);

        $this->artisan('nila:repair-rial-prices', $restore + ['--apply' => true])->assertExitCode(0);
        $this->assertEquals(2150000, $variant->fresh()->price);
        $this->assertEquals(9, $variant->fresh()->stock);
        $this->artisan('nila:repair-rial-prices', $restore)
            ->expectsOutput('Would update: 0; already correct: 3; needs review: 0.')
            ->assertExitCode(0);
        $this->artisan('nila:repair-rial-prices', $repair + ['--apply' => true])->assertExitCode(1);
    }

    public function test_admin_can_import_one_complete_product_group_without_publishing_it(): void
    {
        Storage::fake('local');

        $admin = User::factory()->create(['is_admin' => true]);
        $csv = implode("\n", [
            'holooCode,holooName,holooRegularPrice,holooStockQuantity,holooCustomerCode',
            '0102001*1,80 کش پهن دانتل 5 سانتی-بادمجونی,2150000,4,1',
            '0102001*2,80 کش پهن دانتل 5 سانتی-طوسی,2150000,0,1',
        ]);

        $this->actingAs($admin)->post(
            route('admin.nila.imports.store'),
            ['file' => UploadedFile::fake()->createWithContent('holoo.csv', $csv), 'price_unit' => 'rial']
        )->assertRedirect();

        $batch = NilaImportBatch::query()->firstOrFail();

        $this->get(route('admin.nila.imports.show', $batch))
            ->assertOk()
            ->assertSee('۲۱۵٬۰۰۰');

        $this->actingAs($admin)->post(
            route('admin.nila.imports.import', $batch),
            ['batch_size' => 5, 'confirm_stock_snapshot' => 1]
        )->assertRedirect();

        $this->assertDatabaseCount('products', 1);
        $this->assertDatabaseCount('product_variants', 2);
        $this->assertDatabaseCount('integration_mappings', 3);

        $product = Product::query()->firstOrFail();

        $this->assertSame('80 کش پهن دانتل 5 سانتی', $product->name);
        $this->assertNull($product->category_id);
        $this->assertNull($product->brand_id);
        $this->assertFalse($product->is_active);

        $this->assertDatabaseHas('product_variants', [
            'product_id' => $product->id,
            'sku' => '0102001*1',
            'color' => 'بادمجونی',
            'price' => 215000,
            'stock' => 4,
            'is_active' => false,
        ]);

        $this->assertDatabaseHas('product_variants', [
            'product_id' => $product->id,
            'sku' => '0102001*2',
            'color' => 'طوسی',
            'price' => 215000,
            'stock' => 0,
            'is_active' => false,
        ]);

        $batch->refresh();
        $this->assertSame('completed', $batch->status);
        $this->assertSame(1, $batch->imported_groups);
        $this->assertSame(0, $batch->failed_groups);
    }

    public function test_monthly_preview_distinguishes_existing_products_new_variants_and_stock_differences(): void
    {
        Storage::fake('local');

        $admin = User::factory()->create(['is_admin' => true]);

        $existing = app(\App\Integrations\Nila\NilaCatalogImporter::class)->import(
            new \App\Integrations\Nila\Data\ExternalProductData(
                externalId: '0102001',
                name: 'محصول قبلی',
                variants: [
                    new \App\Integrations\Nila\Data\ExternalVariantData(
                        externalId: '0102001*1',
                        sku: '0102001*1',
                        price: 100000,
                        stock: 2,
                    ),
                ],
            )
        );

        $csv = implode("\n", [
            'holooCode,holooName,holooRegularPrice,holooStockQuantity,holooCustomerCode',
            '0102001*1,محصول قبلی-مشکی,120000,7,1',
            '0102001*2,محصول قبلی-طوسی,120000,3,1',
            '0102002*1,محصول کاملاً جدید-آبی,140000,5,1',
        ]);

        $this->actingAs($admin)->post(
            route('admin.nila.imports.store'),
            ['file' => UploadedFile::fake()->createWithContent('monthly-export.csv', $csv)]
        )->assertRedirect();

        $batch = NilaImportBatch::query()->latest('id')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('admin.nila.imports.show', $batch))
            ->assertOk()
            ->assertViewHas('comparison', function (array $summary) use ($existing): bool {
                return $summary['existing_products'] === 1
                    && $summary['new_products'] === 1
                    && $summary['existing_variants'] === 1
                    && $summary['new_variants'] === 2
                    && $summary['stock_change_count'] === 1
                    && $summary['stock_changes'][0]['current'] === 2
                    && $summary['stock_changes'][0]['incoming'] === 7;
            })
            ->assertSee('محصول قبلی')
            ->assertSee('موجودی فعلی')
            ->assertSee('0102001*1');

        $this->assertDatabaseCount('products', 1);
        $this->assertDatabaseCount('product_variants', 1);
        $this->assertDatabaseHas('integration_mappings', [
            'integration' => 'nila',
            'entity_type' => \App\Models\Product::class,
            'entity_id' => $existing->id,
            'external_id' => '0102001',
        ]);
    }

    public function test_import_progresses_in_bounded_chunks_and_can_resume(): void
    {
        Storage::fake('local');

        $admin = User::factory()->create(['is_admin' => true]);
        $lines = [
            'holooCode,holooName,holooRegularPrice,holooStockQuantity,holooCustomerCode',
        ];

        foreach (range(1, 6) as $number) {
            $baseCode = '01020' . str_pad((string) $number, 2, '0', STR_PAD_LEFT);
            $lines[] = $baseCode . '*1,محصول ' . $number . '-مشکی,100000,2,1';
        }

        $this->actingAs($admin)->post(
            route('admin.nila.imports.store'),
            ['file' => UploadedFile::fake()->createWithContent('six-products.csv', implode("\n", $lines))]
        )->assertRedirect();

        $batch = NilaImportBatch::query()->firstOrFail();

        $this->actingAs($admin)->post(
            route('admin.nila.imports.import', $batch),
            ['batch_size' => 5, 'confirm_stock_snapshot' => 1]
        )->assertRedirect();

        $batch->refresh();
        $this->assertSame('ready', $batch->status);
        $this->assertSame(5, $batch->next_group_offset);
        $this->assertSame(5, $batch->imported_groups);
        $this->assertDatabaseCount('products', 5);

        $this->actingAs($admin)->post(
            route('admin.nila.imports.import', $batch),
            ['batch_size' => 5, 'confirm_stock_snapshot' => 1]
        )->assertRedirect();

        $batch->refresh();
        $this->assertSame('completed', $batch->status);
        $this->assertSame(6, $batch->next_group_offset);
        $this->assertSame(6, $batch->imported_groups);
        $this->assertDatabaseCount('products', 6);
        $this->assertDatabaseCount('product_variants', 6);
    }

    public function test_import_rejects_batches_larger_than_fifty_product_groups(): void
    {
        Storage::fake('local');

        $admin = User::factory()->create(['is_admin' => true]);
        $csv = implode("\n", [
            'holooCode,holooName,holooRegularPrice,holooStockQuantity,holooCustomerCode',
            '0102001*1,محصول آزمایشی-مشکی,100000,2,1',
        ]);

        $this->actingAs($admin)->post(
            route('admin.nila.imports.store'),
            ['file' => UploadedFile::fake()->createWithContent('pilot.csv', $csv)]
        )->assertRedirect();

        $batch = NilaImportBatch::query()->firstOrFail();

        $this->actingAs($admin)->from(route('admin.nila.imports.show', $batch))->post(
            route('admin.nila.imports.import', $batch),
            ['batch_size' => 51, 'confirm_stock_snapshot' => 1]
        )->assertSessionHasErrors('batch_size');

        $this->assertDatabaseCount('products', 0);
        $this->assertDatabaseCount('product_variants', 0);
    }

    public function test_import_skips_the_entire_product_group_when_one_variant_is_invalid(): void
    {
        Storage::fake('local');

        $admin = User::factory()->create(['is_admin' => true]);
        $csv = implode("\n", [
            'holooCode,holooName,holooRegularPrice,holooStockQuantity,holooCustomerCode',
            '0102001*1,80 کش پهن دانتل 5 سانتی-بادمجونی,2150000,4,1',
            '0102001*2,80 کش پهن دانتل 5 سانتی-طوسی,,0,1',
        ]);

        $this->actingAs($admin)->post(
            route('admin.nila.imports.store'),
            ['file' => UploadedFile::fake()->createWithContent('partial-group.csv', $csv)]
        )->assertRedirect();

        $batch = NilaImportBatch::query()->firstOrFail();

        $this->actingAs($admin)->post(
            route('admin.nila.imports.import', $batch),
            ['batch_size' => 5, 'confirm_stock_snapshot' => 1]
        )->assertRedirect();

        $batch->refresh();

        $this->assertSame('completed', $batch->status);
        $this->assertSame(0, $batch->imported_groups);
        $this->assertSame(1, $batch->failed_groups);
        $this->assertDatabaseCount('products', 0);
        $this->assertDatabaseCount('product_variants', 0);
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
