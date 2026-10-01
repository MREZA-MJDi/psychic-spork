<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminMediaUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_upload_variant_media(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['is_admin' => true]);
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);
        $variant = ProductVariant::factory()->create(['product_id' => $product->id]);

        $response = $this->actingAs($admin)->post(route('admin.media.store', [
            'type' => 'variant',
            'id' => $variant->id,
        ]), [
            'media_file' => UploadedFile::fake()->image('variant.webp', 800, 800),
            'alt_text' => 'تصویر واریانت',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('media', [
            'mediable_type' => $variant->getMorphClass(),
            'mediable_id' => $variant->id,
            'collection' => 'gallery',
            'alt_text' => 'تصویر واریانت',
        ]);

        $this->assertTrue($variant->galleryMedia()->exists());
    }

    public function test_admin_media_target_is_scoped_to_the_requested_entity(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['is_admin' => true]);
        $brand = Brand::factory()->create();
        $otherBrand = Brand::factory()->create();

        $media = $brand->media()->create([
            'collection' => 'logo',
            'disk' => 'public',
            'path' => 'brands/logo.webp',
            'mime_type' => 'image/webp',
        ]);

        $response = $this->actingAs($admin)->delete(route('admin.media.destroy', [
            'type' => 'brand',
            'id' => $otherBrand->id,
            'media' => $media->id,
        ]));

        $response->assertNotFound();
        $this->assertDatabaseHas('media', ['id' => $media->id]);
    }
}
