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
            ->assertSee('هنوز هیچ محصولی وارد دیتابیس نشده')
            ->assertSee('۵ گروه محصول (شروع پایلوت)')
            ->assertSee('۵۰ گروه محصول (حداکثر هر مرحله)')
            ->assertSee('قبل از هر Import بزرگ‌تر باید نتیجه این پایلوت بررسی و تأیید شود')
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
            ['file' => UploadedFile::fake()->createWithContent('holoo.csv', $csv)]
        )->assertRedirect();

        $batch = NilaImportBatch::query()->firstOrFail();

        $this->actingAs($admin)->post(
            route('admin.nila.imports.import', $batch),
            ['batch_size' => 5]
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
            'price' => 2150000,
            'stock' => 4,
            'is_active' => false,
        ]);

        $this->assertDatabaseHas('product_variants', [
            'product_id' => $product->id,
            'sku' => '0102001*2',
            'color' => 'طوسی',
            'price' => 2150000,
            'stock' => 0,
            'is_active' => false,
        ]);

        $batch->refresh();
        $this->assertSame('completed', $batch->status);
        $this->assertSame(1, $batch->imported_groups);
        $this->assertSame(0, $batch->failed_groups);
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
            ['batch_size' => 5]
        )->assertRedirect();

        $batch->refresh();
        $this->assertSame('ready', $batch->status);
        $this->assertSame(5, $batch->next_group_offset);
        $this->assertSame(5, $batch->imported_groups);
        $this->assertDatabaseCount('products', 5);

        $this->actingAs($admin)->post(
            route('admin.nila.imports.import', $batch),
            ['batch_size' => 5]
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
            ['batch_size' => 51]
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
            ['batch_size' => 5]
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
