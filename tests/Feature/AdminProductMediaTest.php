<?php

namespace Tests\Feature;

use App\Models\Media;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminProductMediaTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_upload_multiple_product_images(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true])->save();

        $product = Product::query()->create([
            'name' => 'محصول تست',
            'slug' => 'test-product',
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.products.media.store', $product), [
                'images' => [
                    UploadedFile::fake()->image('front.jpg', 800, 800),
                    UploadedFile::fake()->image('back.jpg', 900, 900),
                ],
            ])
            ->assertRedirect();

        $this->assertDatabaseCount('media', 2);
        $this->assertSame(
            [0, 1],
            Media::query()
                ->where('mediable_id', $product->id)
                ->orderBy('sort_order')
                ->pluck('sort_order')
                ->all()
        );

        Storage::disk('public')->assertExists(
            Media::query()->where('original_name', 'front.jpg')->value('path')
        );
    }

    public function test_admin_can_reorder_product_images_and_first_image_is_primary(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true])->save();

        $product = Product::query()->create([
            'name' => 'محصول تست',
            'slug' => 'test-product-reorder',
            'is_active' => true,
        ]);

        $first = $product->media()->create([
            'collection' => 'gallery',
            'disk' => 'public',
            'path' => 'products/first.webp',
            'original_name' => 'first.webp',
            'mime_type' => 'image/webp',
            'sort_order' => 0,
        ]);

        $second = $product->media()->create([
            'collection' => 'gallery',
            'disk' => 'public',
            'path' => 'products/second.webp',
            'original_name' => 'second.webp',
            'mime_type' => 'image/webp',
            'sort_order' => 1,
        ]);

        $this->actingAs($admin)
            ->postJson(
                route('admin.products.media.reorder', $product),
                ['media' => [$second->id, $first->id]]
            )
            ->assertOk();

        $this->assertSame(0, $second->refresh()->sort_order);
        $this->assertSame(1, $first->refresh()->sort_order);
        $this->assertSame($second->id, $product->refresh()->primaryGalleryMedia()->value('id'));
    }

    public function test_non_admin_cannot_manage_product_media(): void
    {
        $customer = User::factory()->create();
        $customer->forceFill(['is_admin' => false])->save();

        $product = Product::query()->create([
            'name' => 'محصول تست',
            'slug' => 'test-product-auth',
            'is_active' => true,
        ]);

        $this->actingAs($customer)
            ->get(route('admin.products.media.index', $product))
            ->assertForbidden();
    }
}
