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

    private const SITEMAP_LIMIT = 10000;

    public function sitemap(): Response
    {
        $segments = [
            'pages' => count($this->staticPages()),
            'products' => Product::query()->active()->count(),
            'categories' => Category::query()->active()->count(),
            'brands' => Brand::query()->active()->count(),
        ];

        $xml = [
            '<?xml version="1.0" encoding="UTF-8"?>',
            '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">',
        ];

        foreach ($segments as $type => $total) {
            $pages = max(1, (int) ceil($total / self::SITEMAP_LIMIT));

            if ($total === 0) {
                continue;
            }

            for ($page = 1; $page <= $pages; $page++) {
                $xml[] = '    <sitemap>';
                $xml[] = '        <loc>' . $this->escapeXml(route(
                    'seo.sitemap.segment',
                    ['type' => $type, 'page' => $page]
                )) . '</loc>';
                $xml[] = '    </sitemap>';
            }
        }

        $xml[] = '</sitemapindex>';

        return response(implode("\n", $xml), 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
        ]);
    }

    public function sitemapSegment(string $type, int $page): Response
    {
        abort_unless(
            in_array($type, ['pages', 'products', 'categories', 'brands'], true)
            && $page >= 1,
            404
        );

        $offset = ($page - 1) * self::SITEMAP_LIMIT;

        if ($type === 'pages') {
            $entries = array_slice(
                $this->staticPages(),
                $offset,
                self::SITEMAP_LIMIT
            );
        } else {
            $query = match ($type) {
                'products' => Product::query()->active(),
                'categories' => Category::query()->active(),
                'brands' => Brand::query()->active(),
            };

            $items = $query
                ->select(['id', 'slug', 'updated_at'])
                ->orderBy('id')
                ->offset($offset)
                ->limit(self::SITEMAP_LIMIT)
                ->get();

            $routeName = match ($type) {
                'products' => 'products.show',
                'categories' => 'categories.show',
                'brands' => 'brands.show',
            };

            $entries = $items->map(fn ($item): array => [
                'loc' => route($routeName, $item),
                'lastmod' => $item->updated_at?->toAtomString(),
            ])->all();
        }

        abort_if($entries === [] && $page > 1, 404);

        return $this->urlset($entries);
    }

    private function staticPages(): array
    {
        return [
            ['loc' => route('home')],
            ['loc' => route('products.index')],
            ['loc' => route('categories.index')],
            ['loc' => route('brands.index')],
            ['loc' => route('about')],
            ['loc' => route('shipping')],
            ['loc' => route('returns')],
            ['loc' => route('faq')],
        ];
    }

    private function urlset(array $entries): Response
    {
        $xml = [
            '<?xml version="1.0" encoding="UTF-8"?>',
            '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">',
        ];

        foreach ($entries as $entry) {
            $xml[] = '    <url>';
            $xml[] = '        <loc>' . $this->escapeXml($entry['loc']) . '</loc>';

            if (! empty($entry['lastmod'])) {
                $xml[] = '        <lastmod>' . $this->escapeXml($entry['lastmod']) . '</lastmod>';
            }

            $xml[] = '    </url>';
        }

        $xml[] = '</urlset>';

        return response(implode("\n", $xml), 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
        ]);
    }

    private function escapeXml(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

}
