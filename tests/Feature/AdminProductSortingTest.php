<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminProductSortingTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_product_list_sorts_and_paginates_with_query_state(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        foreach (range(1, 16) as $number) {
            Product::factory()->create([
                'name' => sprintf('محصول %02d', $number),
            ]);
        }

        $response = $this->actingAs($admin)->get(
            route('admin.products.index', [
                'sort' => 'name_desc',
                'per_page' => 15,
            ])
        );

        $response->assertOk()->assertViewHas('products', function ($products): bool {
            return $products->total() === 16
                && $products->count() === 15
                && str_ends_with($products->first()->name, '16')
                && str_ends_with($products->last()->name, '02');
        });
    }

    public function test_admin_product_list_rejects_unsupported_sort_and_page_size_values(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        Product::factory()->count(3)->create();

        $this->actingAs($admin)->get(
            route('admin.products.index', [
                'sort' => 'unsafe-column',
                'per_page' => 999,
            ])
        )->assertOk()->assertViewHas('products', function ($products): bool {
            return $products->perPage() === 15;
        });
    }
}
