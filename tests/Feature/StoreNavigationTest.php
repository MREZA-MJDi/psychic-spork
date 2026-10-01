<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_drclubz_route_and_store_navigation_contract_are_available(): void
    {
        $response = $this->get(route('club'));

        $response->assertOk();
        $response->assertSee('DrClubz');
        $response->assertSee('باشگاه مشتریان');

        // Categories remain a valid discovery route, but are no longer a
        // primary item in the shared storefront navbar.
        $response->assertSee('href="' . route('categories.index') . '"', false);
        $response->assertDontSee(
            'href="' . route('categories.index') . '" class="store-nav__link'
        );

        $response->assertSee('href="' . route('club') . '"', false);
        $response->assertSee('DrClubz');
        $this->assertMatchesRegularExpression(
            '/href="' . preg_quote(route('club'), '/') . '"\\s+class="store-nav__link(?:\\s+is-active)?"/',
            $response->getContent()
        );

        // The mobile storefront bar keeps the same customer-focused five-item
        // contract and must not silently fall back to the wholesale link.
        $response->assertSee('store-mobile-bottom');
        $response->assertSee('aria-label="باشگاه مشتریان DrClubz"', false);
        $response->assertDontSee('aria-label="خرید عمده"', false);
    }
}
