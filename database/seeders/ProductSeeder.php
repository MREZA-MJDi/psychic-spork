<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Media;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::query()
            ->get()
            ->keyBy('slug');

        $brands = Brand::query()
            ->get()
            ->keyBy('slug');

        $products = [
            ['category' => 'bras', 'brand' => 'isabella', 'name' => 'سوتین دانتل ایزابلا', 'slug' => 'isabella-lace-bra', 'sku' => 'JAN-ISA-BRA-001', 'short' => 'سوتین ظریف با طراحی دانتل و فرم راحت', 'description' => 'مدلی ظریف و زنانه با طراحی مناسب استفاده روزمره.', 'size' => '75', 'color' => 'مشکی', 'color_code' => '#211e20', 'price' => 1290000, 'sale_price' => 1090000, 'stock' => 18, 'featured' => true],
            ['category' => 'bras', 'brand' => 'paniz', 'name' => 'سوتین کلاسیک پانیذ', 'slug' => 'paniz-classic-bra', 'sku' => 'JAN-PAN-BRA-001', 'short' => 'مدل کلاسیک و راحت برای استفاده روزمره', 'description' => 'طراحی ساده و کاربردی با تمرکز بر راحتی.', 'size' => '75', 'color' => 'نود', 'color_code' => '#d9b6a7', 'price' => 990000, 'sale_price' => null, 'stock' => 22, 'featured' => true],
            ['category' => 'panties', 'brand' => 'avina', 'name' => 'شورت نخی آوینا', 'slug' => 'avina-cotton-panty', 'sku' => 'JAN-AVI-PAN-001', 'short' => 'شورت نخی نرم و لطیف', 'description' => 'انتخابی راحت برای استفاده روزمره با طراحی ساده.', 'size' => 'M', 'color' => 'سفید', 'color_code' => '#f7f7f2', 'price' => 420000, 'sale_price' => 360000, 'stock' => 35, 'featured' => false],
            ['category' => 'panties', 'brand' => 'nosheh', 'name' => 'شورت فانتزی نوشه', 'slug' => 'nosheh-fancy-panty', 'sku' => 'JAN-NOS-PAN-001', 'short' => 'مدلی ظریف با طراحی فانتزی', 'description' => 'طراحی زنانه و ظریف برای کالکشن فانتزی.', 'size' => 'M', 'color' => 'زرشکی', 'color_code' => '#7a2638', 'price' => 540000, 'sale_price' => 470000, 'stock' => 14, 'featured' => true],
            ['category' => 'lingerie-sets', 'brand' => 'laya', 'name' => 'ست ظریف لعیا', 'slug' => 'laya-delicate-set', 'sku' => 'JAN-LAY-SET-001', 'short' => 'ست هماهنگ با طراحی کلاسیک', 'description' => 'ست زنانه با طراحی ظریف و هماهنگ.', 'size' => '75/M', 'color' => 'کرم', 'color_code' => '#e9d6c5', 'price' => 1890000, 'sale_price' => 1590000, 'stock' => 11, 'featured' => true],
            ['category' => 'sleepwear', 'brand' => 'emma', 'name' => 'لباس خواب مدرن اما', 'slug' => 'emma-modern-sleepwear', 'sku' => 'JAN-EMM-SLP-001', 'short' => 'لباس خواب لطیف با طراحی مدرن', 'description' => 'مدلی راحت و ظریف برای کالکشن لباس خواب.', 'size' => 'M', 'color' => 'صورتی پودری', 'color_code' => '#d9adb7', 'price' => 1450000, 'sale_price' => null, 'stock' => 9, 'featured' => false],
            ['category' => 'bodysuits', 'brand' => 'jan-janan', 'name' => 'بادی جان جانان', 'slug' => 'jan-janan-bodysuit', 'sku' => 'JAN-JAN-BOD-001', 'short' => 'بادی اختصاصی جانان با طراحی ظریف', 'description' => 'طراحی اختصاصی جانان با تمرکز بر ظرافت و فرم.', 'size' => 'M', 'color' => 'مشکی', 'color_code' => '#1d1a1b', 'price' => 1690000, 'sale_price' => 1490000, 'stock' => 12, 'featured' => true],
            ['category' => 'fantasy', 'brand' => 'isabella', 'name' => 'کالکشن فانتزی ایزابلا', 'slug' => 'isabella-fantasy-collection', 'sku' => 'JAN-ISA-FAN-001', 'short' => 'مدلی خاص از کالکشن فانتزی ایزابلا', 'description' => 'طراحی خاص و ظریف برای کالکشن فانتزی.', 'size' => 'M', 'color' => 'آلبالویی', 'color_code' => '#6d2635', 'price' => 1590000, 'sale_price' => 1390000, 'stock' => 8, 'featured' => true],
        ];

        foreach ($products as $index => $data) {
            $categoryId = $categories[$data['category']]?->id;
            $brandId = $brands[$data['brand']]?->id;

            if (! $categoryId || ! $brandId) {
                $this->command?->warn(
                    "Skipping {$data['name']}: category or brand seed data is missing."
                );
                continue;
            }

            $product = Product::updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'category_id' => $categoryId,
                    'brand_id' => $brandId,
                    'name' => $data['name'],
                    'short_description' => $data['short'],
                    'description' => $data['description'],
                    'is_active' => true,
                    'is_featured' => $data['featured'],
                    'sort_order' => $index + 1,
                ]
            );

            $variant = ProductVariant::updateOrCreate(
                ['sku' => $data['sku']],
                [
                    'product_id' => $product->id,
                    'size' => $data['size'],
                    'color' => $data['color'],
                    'color_code' => $data['color_code'],
                    'price' => $data['price'],
                    'sale_price' => $data['sale_price'],
                    'stock' => $data['stock'],
                    'low_stock_threshold' => 5,
                    'is_active' => true,
                    'sort_order' => 0,
                ]
            );

            $this->ensureLocalImage(
                'products/' . $data['slug'] . '.svg',
                $data['name'],
                'JANAN PRODUCT'
            );

            Media::updateOrCreate(
                [
                    'mediable_type' => Product::class,
                    'mediable_id' => $product->id,
                    'collection' => 'gallery',
                    'sort_order' => 0,
                ],
                [
                    'disk' => 'public',
                    'path' => 'products/' . $data['slug'] . '.svg',
                    'original_name' => $data['slug'] . '.svg',
                    'mime_type' => 'image/svg+xml',
                    'alt_text' => $data['name'],
                    'sort_order' => 0,
                ]
            );
        }
    }

    private function ensureLocalImage(
        string $path,
        string $title,
        string $eyebrow
    ): void {
        $disk = Storage::disk('public');

        if ($disk->exists($path)) {
            return;
        }

        $safeTitle = htmlspecialchars(
            $title,
            ENT_QUOTES | ENT_XML1,
            'UTF-8'
        );

        $safeEyebrow = htmlspecialchars(
            $eyebrow,
            ENT_QUOTES | ENT_XML1,
            'UTF-8'
        );

        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 800">
    <defs>
        <linearGradient id="bg" x1="0" y1="0" x2="1" y2="1">
            <stop offset="0%" stop-color="#f6eee8"/>
            <stop offset="100%" stop-color="#d7c8bd"/>
        </linearGradient>
    </defs>
    <rect width="1200" height="800" fill="url(#bg)"/>
    <circle cx="930" cy="190" r="210" fill="#ffffff" opacity=".32"/>
    <circle cx="250" cy="660" r="250" fill="#ffffff" opacity=".2"/>
    <rect x="120" y="120" width="960" height="560" rx="42" fill="#ffffff" opacity=".18"/>
    <text x="600" y="330" text-anchor="middle" font-family="Arial, sans-serif" font-size="32" letter-spacing="7" fill="#6c5c54">{$safeEyebrow}</text>
    <text x="600" y="430" text-anchor="middle" font-family="Arial, sans-serif" font-size="54" font-weight="700" fill="#2f2925">{$safeTitle}</text>
    <text x="600" y="500" text-anchor="middle" font-family="Arial, sans-serif" font-size="24" fill="#6c5c54">Janan Lingerie</text>
</svg>
SVG;

        $disk->put($path, $svg);
    }
}
