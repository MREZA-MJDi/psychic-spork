<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class StoreLocationTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_location_endpoints_use_the_local_catalog_without_network_access(): void
    {
        Http::preventStrayRequests();

        $this->getJson(route('locations.provinces'))
            ->assertOk()
            ->assertJsonCount(31)
            ->assertJsonFragment(['id' => 7, 'name' => 'تهران'])
            ->assertJsonFragment(['id' => 17, 'name' => 'قزوین']);

        $this->getJson(route('locations.cities', ['province' => 17]))
            ->assertOk()
            ->assertJsonFragment(['name' => 'محمدیه'])
            ->assertJsonFragment(['name' => 'قزوین'])
            ->assertJsonMissing(['name' => 'تهران']);

        $this->getJson(route('locations.cities', ['province' => 32]))
            ->assertOk()->assertExactJson([]);

        $this->getJson(route('locations.cities', ['province' => 'wrong']))
            ->assertUnprocessable();

        Http::assertNothingSent();
    }
}
