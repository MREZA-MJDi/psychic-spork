<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class StoreLocationTest extends TestCase
{
    use RefreshDatabase;

    public function test_location_api_returns_all_provinces_and_caches_normalized_results(): void
    {
        $rows = array_map(fn ($id) => ['id' => $id, 'name' => "استان {$id}"], range(1, 31));
        $rows[0]['name'] = 'تهران';
        Http::fake(['https://iran-locations-api.ir/api/v1/fa/states' => Http::response($rows)]);

        $this->getJson(route('locations.provinces'))->assertOk()->assertJsonCount(31)->assertJsonFragment(['id' => 1, 'name' => 'تهران']);
        $this->getJson(route('locations.provinces'))->assertOk()->assertJsonCount(31);
        Http::assertSentCount(1);
    }

    public function test_city_api_filters_by_province_and_normalizes_persian_names(): void
    {
        Http::fake(['https://iran-locations-api.ir/api/v1/fa/cities*' => Http::response([['name'=>'ورامين'], ['name'=>'ورامین']])]);

        $this->getJson(route('locations.cities', ['province' => 7]))->assertOk()->assertExactJson([['name'=>'ورامین']]);
        Http::assertSent(fn ($request) => str_contains($request->url(), 'state_id=7'));
        $this->getJson(route('locations.cities', ['province' => 'wrong']))->assertUnprocessable();
    }

    public function test_stale_cache_and_manual_fallback_are_available_when_upstream_fails(): void
    {
        Cache::put('store:iran-locations:v1:cities:7', ['items' => [['name' => 'تهران']], 'fresh_until' => 0], 600);
        Http::fake(['*' => Http::response([], 503)]);

        $this->getJson(route('locations.cities', ['province'=>7]))->assertOk()->assertExactJson([['name'=>'تهران']]);
        $this->getJson(route('locations.provinces'))->assertStatus(503);
    }
}
