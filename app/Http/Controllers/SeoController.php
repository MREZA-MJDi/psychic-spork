<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Response;

class SeoController extends Controller
{
    public function robots(): Response
    {
        $sitemapUrl = url('/sitemap.xml');

        $body = implode("\n", [
            'User-agent: *',
            'Allow: /',
            'Disallow: /admin/',
            'Disallow: /account',
            'Disallow: /cart',
            'Disallow: /checkout',
            'Disallow: /login',
            'Disallow: /register',
            'Sitemap: ' . $sitemapUrl,
        ]);

        return response($body, 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }

    public function sitemap(): Response
    {
        $urls = [
            ['loc' => route('home')],
            ['loc' => route('products.index')],
            ['loc' => route('categories.index')],
            ['loc' => route('brands.index')],
            ['loc' => route('about')],
            ['loc' => route('shipping')],
            ['loc' => route('returns')],
            ['loc' => route('faq')],
        ];

        Product::query()
            ->active()
            ->orderBy('id')
            ->get(['slug', 'updated_at'])
            ->each(function (Product $product) use (&$urls): void {
                $urls[] = [
                    'loc' => route('products.show', $product),
                    'lastmod' => $product->updated_at?->toAtomString(),
                ];
            });

        Category::query()
            ->active()
            ->orderBy('id')
            ->get(['slug', 'updated_at'])
            ->each(function (Category $category) use (&$urls): void {
                $urls[] = [
                    'loc' => route('categories.show', $category),
                    'lastmod' => $category->updated_at?->toAtomString(),
                ];
            });

        Brand::query()
            ->active()
            ->orderBy('id')
            ->get(['slug', 'updated_at'])
            ->each(function (Brand $brand) use (&$urls): void {
                $urls[] = [
                    'loc' => route('brands.show', $brand),
                    'lastmod' => $brand->updated_at?->toAtomString(),
                ];
            });

        $xml = [
            '<?xml version="1.0" encoding="UTF-8"?>',
            '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">',
        ];

        foreach ($urls as $entry) {
            $xml[] = '    <url>';
            $xml[] = '        <loc>' . htmlspecialchars($entry['loc'], ENT_XML1 | ENT_QUOTES, 'UTF-8') . '</loc>';

            if (!empty($entry['lastmod'])) {
                $xml[] = '        <lastmod>' . htmlspecialchars($entry['lastmod'], ENT_XML1 | ENT_QUOTES, 'UTF-8') . '</lastmod>';
            }

            $xml[] = '    </url>';
        }

        $xml[] = '</urlset>';

        return response(implode("\n", $xml), 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
        ]);
    }
}
