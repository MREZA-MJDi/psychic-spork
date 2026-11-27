<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\ChequePermission;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminChequeHubAndProductFiltersTest extends TestCase
{
    use RefreshDatabase;

    public function test_permissions_are_centralized_and_alerts_follow_pending_state(): void
    {
        $this->withoutVite();
        $admin = User::factory()->create(['is_admin' => true]);
        $pending = User::factory()->create(['name' => 'Pending cheque customer']);
        $approved = User::factory()->create(['name' => 'Approved cheque customer']);
        $other = User::factory()->create(['name' => 'Other cheque customer']);

        ChequePermission::create(['user_id' => $approved->id, 'enabled' => true,
            'approved_at' => now(), 'approved_by' => $admin->id, 'max_order_amount' => 500000]);

        $this->actingAs($pending)->post(route('wholesale.cheque.request'), ['requested_amount' => '۱٬۰۰۰٬۰۰۰'])
            ->assertRedirect();
        $this->getJson(route('admin.cheques.alerts'))->assertForbidden();

        $this->actingAs($admin);
        $this->getJson(route('admin.cheques.alerts'))->assertOk()->assertExactJson(['requests' => 1, 'payments' => 0]);
        $this->get(route('admin.cheques.index', ['tab' => 'requests']))->assertOk()
            ->assertViewHas('customers', fn ($customers) => $customers->pluck('id')->all() === [$pending->id]);
        $this->get(route('admin.cheques.index', ['tab' => 'customers']))->assertOk()
            ->assertViewHas('customers', fn ($customers) => $customers->pluck('id')->all() === [$approved->id]);
        $this->get(route('admin.cheques.index', ['tab' => 'customers', 'access' => 'all', 'customer_id' => $other->id]))
            ->assertOk()->assertViewHas('customers', fn ($customers) => $customers->pluck('id')->all() === [$other->id]);

        $this->patch(route('admin.customers.cheque.enable', $pending), ['enabled' => 1, 'max_order_amount' => 900000])
            ->assertRedirect()->assertSessionHasNoErrors();
        $this->getJson(route('admin.cheques.alerts'))->assertExactJson(['requests' => 0, 'payments' => 0]);
        $this->get(route('admin.cheques.index', ['tab' => 'customers', 'q' => 'Pending cheque']))
            ->assertOk()->assertViewHas('customers', fn ($customers) => $customers->pluck('id')->all() === [$pending->id]);
        $this->patch(route('admin.customers.cheque.disable', $pending))->assertRedirect();
        $this->get(route('admin.cheques.index', ['tab' => 'customers', 'access' => 'disabled']))
            ->assertOk()->assertViewHas('customers', fn ($customers) => $customers->pluck('id')->all() === [$pending->id]);
        $this->get(route('admin.cheques.index'))->assertOk();
        $this->get(route('admin.wholesale.index'))->assertOk();
    }

    public function test_product_filters_and_sorting_return_the_correct_rows(): void
    {
        $this->withoutVite();
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $category = Category::factory()->create(['is_active' => false]);
        $brand = Brand::factory()->create(['is_active' => false]);
        $cheap = Product::factory()->create([
            'name' => 'Filter Alpha', 'category_id' => $category->id, 'brand_id' => $brand->id,
            'is_active' => true, 'is_featured' => true,
        ]);
        $expensive = Product::factory()->create(['name' => 'Filter Zulu', 'is_active' => false]);
        ProductVariant::factory()->create(['product_id' => $cheap->id, 'sku' => 'FILTER-ALPHA-001', 'price' => 10000, 'stock' => 0]);
        ProductVariant::factory()->create(['product_id' => $expensive->id, 'sku' => 'FILTER-ZULU-002', 'price' => 20000, 'stock' => 20]);

        foreach ([
            [['q' => 'FILTER-ALPHA-001'], [$cheap->id]],
            [['q' => 'Filter Zulu'], [$expensive->id]],
            [['category_id' => $category->id], [$cheap->id]],
            [['brand_id' => $brand->id], [$cheap->id]],
            [['status' => 'active'], [$cheap->id]],
            [['status' => 'inactive'], [$expensive->id]],
            [['status' => 'featured'], [$cheap->id]],
            [['stock' => 'low'], [$cheap->id]],
            [['stock' => 'out'], [$cheap->id]],
            [['stock' => 'in_stock'], [$expensive->id]],
            [['quality' => 'missing-seo'], [$cheap->id]],
            [['quality' => 'missing-image'], [$cheap->id]],
            [['sort' => 'price_asc'], [$cheap->id, $expensive->id]],
            [['sort' => 'price_desc'], [$expensive->id, $cheap->id]],
            [['sort' => 'name_asc'], [$cheap->id, $expensive->id]],
            [['sort' => 'name_desc'], [$expensive->id, $cheap->id]],
            [['sort' => 'stock_asc'], [$cheap->id, $expensive->id]],
            [['sort' => 'stock_desc'], [$expensive->id, $cheap->id]],
            [['category_id' => $category->id, 'status' => 'inactive'], []],
        ] as [$filters, $ids]) {
            $this->get(route('admin.products.index', $filters))->assertOk()
                ->assertViewHas('products', fn ($products) => $products->pluck('id')->all() === $ids);
        }

        $response = $this->get(route('admin.products.index', ['per_page' => 30, 'category_id' => $category->id]));
        $response->assertOk()->assertViewHas('products', fn ($products) => $products->perPage() === 30);
        $response->assertViewHas('categories', fn ($categories) => $categories->contains('id', $category->id));
        $response->assertViewHas('brands', fn ($brands) => $brands->contains('id', $brand->id));
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
    }
}
