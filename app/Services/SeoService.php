<?php

namespace App\Services;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;

final class SeoService
{
    public function page(
        string $title,
        ?string $description = null,
        ?string $canonical = null,
        string $robots = 'index,follow',
        ?string $image = null,
        ?array $schema = null
    ): array {
        $brand = $this->brandName();

        return [
            'title' => $title,
            'description' => $description ?: "فروشگاه آنلاین {$brand}.",
            'canonical' => $canonical ?: url()->current(),
            'robots' => $robots,
            'image' => $image,
            'schema' => $schema,
        ];
    }

    public function product(Product $product): array
    {
        $brand = $this->brandName();
        $variant = $product->defaultVariant();
        $image = $product->galleryMedia->first()?->url;
        $description = $product->description ?: "مشاهده و خرید {$product->name} از {$brand}.";

        return $this->page(
            "{$product->name} — {$brand}",
            $description,
            route('products.show', $product),
            'index,follow',
            $image,
            [
                '@context' => 'https://schema.org',
                '@type' => 'Product',
                'name' => $product->name,
                'sku' => $variant?->sku,
                'description' => $description,
                'image' => array_values(array_filter([$image])),
                'offers' => [
                    '@type' => 'Offer',
                    'priceCurrency' => 'IRR',
                    'price' => (float) (($variant?->effective_price ?? 0) * 10),
                    'availability' => ($variant?->stock ?? 0) > 0
                        ? 'https://schema.org/InStock'
                        : 'https://schema.org/OutOfStock',
                    'url' => route('products.show', $product),
                ],
            ]
        );
    }

    public function category(Category $category): array
    {
        return $this->page(
            "{$category->name} — {$this->brandName()}",
            $category->description ?: "محصولات {$category->name} در فروشگاه.",
            route('categories.show', $category),
            'index,follow',
            $category->coverMedia?->url
        );
    }

    public function brand(Brand $brand): array
    {
        return $this->page(
            "{$brand->name} — {$this->brandName()}",
            $brand->description ?: "معرفی برند {$brand->name} و محصولات مرتبط.",
            route('brands.show', $brand),
            'index,follow',
            $brand->logoMedia?->url
        );
    }

    public function storeHome(): array
    {
        $brand = $this->brandName();

        return $this->page(
            "{$brand} — فروشگاه آنلاین",
            "خرید آنلاین محصولات {$brand} با مشاهده کالکشن‌ها و محصولات جدید.",
            route('home'),
            'index,follow',
            null,
            [
                '@context' => 'https://schema.org',
                '@type' => 'WebSite',
                'name' => $brand,
                'url' => route('home'),
            ]
        );
    }

    public function collection(string $title, string $description, string $url): array
    {
        return $this->page($title, $description, $url);
    }

    public function robots(): string
    {
        return implode("\n", [
            'User-agent: *',
            'Allow: /',
            'Disallow: /admin',
            'Disallow: /cart',
            'Disallow: /checkout',
            'Disallow: /account',
            'Disallow: /login',
            'Disallow: /register',
            'Sitemap: ' . route('seo.sitemap'),
            '',
        ]);
    }

    private function brandName(): string
    {
        return (string) config('app.store_name', 'Janan');
    }
}
