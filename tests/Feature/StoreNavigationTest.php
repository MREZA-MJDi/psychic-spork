<?php

namespace Tests\Feature;

use Tests\TestCase;

class StoreNavigationTest extends TestCase
{
    public function test_drclubz_route_and_store_navigation_contract_are_available(): void
    {
        $response = $this->get(route('club'));

        $response->assertOk();
        $response->assertSee('DrClubz');
        $response->assertSee('باشگاه مشتریان');

        // Categories remain a valid discovery route, but are no longer a
        // primary item in the shared storefront navbar.
        $response->assertSee('href="' . route('categories.index') . '"');
        $response->assertDontSee(
            'href="' . route('categories.index') . '" class="store-nav__link'
        );

        $response->assertSee(
            'href="' . route('club') . '" class="store-nav__link'
        );

        // The mobile storefront bar keeps the same customer-focused five-item
        // contract and must not silently fall back to the wholesale link.
        $response->assertSee('class="store-mobile-bottom"');
        $response->assertSee('aria-label="باشگاه مشتریان DrClubz"');
        $response->assertDontSee('aria-label="خرید عمده"');
    }
}
