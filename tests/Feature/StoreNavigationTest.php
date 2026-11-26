<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class StoreNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_drclubz_route_and_store_navigation_contract_are_available(): void
    {
        $response = $this->get(route('club'));

        $response->assertOk();
        $response->assertSee('باشگاه مشتریان');

        // Categories remain a valid discovery route, but are no longer a
        // primary item in the shared storefront navbar.
        $response->assertSee('href="' . route('categories.index') . '"', false);
        $response->assertDontSee(
            'href="' . route('categories.index') . '" class="store-nav__link'
        );

        $response->assertSee('href="' . route('club') . '"', false);
        $this->assertMatchesRegularExpression(
            '/href="' . preg_quote(route('club'), '/') . '"\\s+class="store-nav__link(?:\\s+is-active)?"/',
            $response->getContent()
        );

        // The mobile storefront bar keeps the same customer-focused five-item
        // contract and must not silently fall back to the wholesale link.
        $response->assertSee('mobile-bottom-nav--store');
        $response->assertSee('aria-label="باشگاه مشتریان"', false);
        $this->assertDoesNotMatchRegularExpression(
            '/<nav[^>]+mobile-bottom-nav--store[^>]*>(?:(?!<\/nav>).)*aria-label="خرید عمده"/s',
            $response->getContent()
        );
    }
    public function test_brand_names_filter_products_instead_of_opening_a_standalone_brand_directory(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk()
            ->assertDontSee('href="' . route('brands.index') . '"', false);
    }

    public function test_no_blade_template_contains_decorative_arrow_spans(): void
    {
        $matches = [];

        foreach (File::allFiles(resource_path('views')) as $file) {
            $contents = File::get($file->getPathname());

            if (preg_match_all('/<span\\b[^>]*\\bclass=(["\\\\\'])[^\"\\\\\']*arrow[^\"\\\\\']*\\1[^>]*>/i', $contents, $found)) {
                foreach ($found[0] as $match) {
                    $matches[] = $file->getRelativePathname() . ': ' . $match;
                }
            }
        }

        $this->assertSame([], $matches, "Decorative arrow spans remain:\\n" . implode("\\n", $matches));
    }

}
