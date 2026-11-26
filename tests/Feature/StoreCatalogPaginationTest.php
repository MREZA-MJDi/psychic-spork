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

    public function test_shop_pagination_keeps_sort_and_page_size_query_state(): void
    {
        Product::factory()->count(7)->create();

        $response = $this->get(route('products.index', [
            'per_page' => 6,
            'sort' => 'name_asc',
            'page' => 2,
            'tracking' => 'campaign',
        ]));

        $response->assertOk()
            ->assertSee('<input type="hidden" name="per_page" value="6">', false)
            ->assertDontSee('name="tracking"', false)
            ->assertViewHas('products', function ($products): bool {
                parse_str(parse_url($products->url(1), PHP_URL_QUERY), $query);

                return $products->total() === 7
                    && $products->count() === 1
                    && $products->currentPage() === 2
                    && $products->perPage() === 6
                    && $query === ['sort' => 'name_asc', 'per_page' => '6', 'page' => '1'];
            });
    }

    public function test_homepage_featured_carousel_receives_up_to_thirty_six_active_products(): void
    {
        $active = Product::factory()->count(40)->create([
            'is_active' => true,
            'is_featured' => true,
        ]);
        Product::factory()->count(5)->create([
            'is_active' => false,
            'is_featured' => true,
        ]);

        $activeIds = $active->pluck('id');

        $this->get(route('home'))
            ->assertOk()
            ->assertViewHas('products', function ($products) use ($activeIds): bool {
                return $products->count() === 36
                    && $products->pluck('id')->diff($activeIds)->isEmpty();
            });
    }

    public function test_category_pages_and_brand_filters_are_database_paginated(): void
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

        $this->get(route('products.index', ['brand' => $brand->slug, 'per_page' => 6, 'page' => 2, 'sort' => 'name_asc']))
            ->assertOk()
            ->assertViewHas('products', function ($products): bool {
                return $products->total() === 7
                    && $products->count() === 1
                    && $products->currentPage() === 2
                    && $products->perPage() === 6;
            });
    }

    public function test_shop_can_filter_brands_by_a_customer_typed_name(): void
    {
        $brand = Brand::factory()->create([
            'name' => 'North Star',
            'slug' => 'north-star',
            'is_active' => true,
        ]);
        $otherBrand = Brand::factory()->create([
            'name' => 'South Moon',
            'slug' => 'south-moon',
            'is_active' => true,
        ]);

        Product::factory()->count(2)->create([
            'brand_id' => $brand->id,
            'is_active' => true,
        ]);
        Product::factory()->create([
            'brand_id' => $otherBrand->id,
            'is_active' => true,
        ]);

        $this->get(route('products.index', ['brand' => 'North']))
            ->assertOk()
            ->assertViewHas('products', function ($products): bool {
                return $products->total() === 2;
            });
    }

    public function test_shop_discovery_renders_the_admin_selected_hero_product_banner(): void
    {
        Product::factory()->create([
            'name' => 'Curated Catalog Banner Product',
            'slug' => 'curated-catalog-banner-product',
            'is_active' => true,
            'is_hero' => true,
        ]);

        $this->get(route('products.index'))
            ->assertOk()
            ->assertSee('catalog-category-sidebar')
            ->assertSee('catalog-brand-filter')
            ->assertSee('Curated Catalog Banner Product')
            ->assertViewHas('featuredBanner', function ($banner): bool {
                return is_array($banner)
                    && ($banner['title'] ?? null) === 'Curated Catalog Banner Product';
            });
    }


    public function test_shop_displays_twelve_products_per_page_and_renders_next_page(): void
    {
        Product::factory()->count(13)->create([
            'is_active' => true,
        ]);

        $firstPage = $this->get(route('products.index'));

        $firstPage->assertOk()
            ->assertSee('store-pagination')
            ->assertSee('صفحه ۱ از ۲')
            ->assertViewHas('products', function ($products): bool {
                return $products->total() === 13
                    && $products->count() === 12
                    && $products->perPage() === 12
                    && $products->currentPage() === 1;
            });

        $this->get(route('products.index', ['page' => 2]))
            ->assertOk()
            ->assertViewHas('products', function ($products): bool {
                return $products->total() === 13
                    && $products->count() === 1
                    && $products->perPage() === 12
                    && $products->currentPage() === 2;
            });
    }

}
