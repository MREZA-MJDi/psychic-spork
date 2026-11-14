<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreCatalogPaginationTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_directory_is_database_paginated(): void
    {
        Category::factory()->count(25)->create();

        $this->get(route('categories.index'))
            ->assertOk()
            ->assertViewHas('categories', function ($categories): bool {
                return $categories->total() === 25
                    && $categories->count() === 24
                    && $categories->perPage() === 24;
            });
    }

    public function test_brand_directory_is_database_paginated(): void
    {
        Brand::factory()->count(25)->create();

        $this->get(route('brands.index'))
            ->assertOk()
            ->assertViewHas('brands', function ($brands): bool {
                return $brands->total() === 25
                    && $brands->count() === 24
                    && $brands->perPage() === 24;
            });
    }

    public function test_shop_pagination_keeps_sort_and_page_size_query_state(): void
    {
        Product::factory()->count(7)->create();

        $this->get(route('products.index', [
            'per_page' => 6,
            'sort' => 'name_asc',
            'page' => 2,
        ]))
            ->assertOk()
            ->assertViewHas('products', function ($products): bool {
                return $products->total() === 7
                    && $products->count() === 1
                    && $products->currentPage() === 2
                    && $products->perPage() === 6;
            });
    }

    public function test_homepage_featured_carousel_receives_up_to_thirty_six_active_products(): void
    {
        Product::factory()->count(40)->create([
            'is_active' => true,
            'is_featured' => true,
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertViewHas('products', function ($products): bool {
                return $products->count() === 36;
            });
    }

    public function test_category_and_brand_product_pages_are_database_paginated(): void
    {
        $category = Category::factory()->create();
        $brand = Brand::factory()->create();

        Product::factory()->count(7)->create([
            'category_id' => $category->id,
            'brand_id' => $brand->id,
        ]);

        $this->get(route('categories.show', $category) . '?per_page=6&page=2&sort=name_asc')
            ->assertOk()
            ->assertViewHas('products', function ($products): bool {
                return $products->total() === 7
                    && $products->count() === 1
                    && $products->currentPage() === 2
                    && $products->perPage() === 6;
            });

        $this->get(route('brands.show', $brand) . '?per_page=6&page=2&sort=name_asc')
            ->assertOk()
            ->assertViewHas('products', function ($products): bool {
                return $products->total() === 7
                    && $products->count() === 1
                    && $products->currentPage() === 2
                    && $products->perPage() === 6;
            });
    }
}
