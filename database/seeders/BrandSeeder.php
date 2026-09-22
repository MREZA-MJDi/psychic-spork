<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Media;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class BrandSeeder extends Seeder
{
    public function run(): void
    {
        $brands = [
            ['name' => 'ایزابلا', 'slug' => 'isabella', 'description' => 'لباس زیر زنانه با تمرکز بر ظرافت و طراحی روزمره', 'image' => 'brands/isabella.svg'],
            ['name' => 'پانیذ', 'slug' => 'paniz', 'description' => 'مدل‌های متنوع و کاربردی برای استفاده روزمره', 'image' => 'brands/paniz.svg'],
            ['name' => 'آوینا', 'slug' => 'avina', 'description' => 'کالکشن‌های لطیف و رنگی لباس زیر زنانه', 'image' => 'brands/avina.svg'],
            ['name' => 'نوشه', 'slug' => 'nosheh', 'description' => 'مدل‌های راحتی و فانتزی زنانه', 'image' => 'brands/nosheh.svg'],
            ['name' => 'لعیا', 'slug' => 'laya', 'description' => 'طراحی‌های ظریف و کلاسیک زنانه', 'image' => 'brands/laya.svg'],
            ['name' => 'اما', 'slug' => 'emma', 'description' => 'مدل‌های مدرن و متنوع لباس زیر زنانه', 'image' => 'brands/emma.svg'],
            ['name' => 'جان جانان', 'slug' => 'jan-janan', 'description' => 'برند اختصاصی جانان', 'image' => 'brands/jan-janan.svg'],
        ];

        foreach ($brands as $data) {
            $brand = Brand::firstOrCreate(
                ['slug' => $data['slug']],
                [
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'is_active' => true,
                ]
            );

            $this->ensureLocalImage(
                $data['image'],
                $data['name'],
                'JANAN BRAND'
            );

            Media::firstOrCreate(
                [
                    'mediable_type' => Brand::class,
                    'mediable_id' => $brand->id,
                    'collection' => 'logo',
                ],
                [
                    'disk' => 'public',
                    'path' => $data['image'],
                    'original_name' => basename($data['image']),
                    'mime_type' => 'image/svg+xml',
                    'alt_text' => 'برند ' . $data['name'],
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
            <stop offset="0%" stop-color="#f5eee8"/>
            <stop offset="100%" stop-color="#ddd0c5"/>
        </linearGradient>
    </defs>
    <rect width="1200" height="800" fill="url(#bg)"/>
    <circle cx="980" cy="160" r="190" fill="#ffffff" opacity=".35"/>
    <circle cx="220" cy="670" r="240" fill="#ffffff" opacity=".22"/>
    <text x="600" y="340" text-anchor="middle" font-family="Arial, sans-serif" font-size="34" letter-spacing="7" fill="#6c5c54">{$safeEyebrow}</text>
    <text x="600" y="430" text-anchor="middle" font-family="Arial, sans-serif" font-size="58" font-weight="700" fill="#2f2925">{$safeTitle}</text>
    <text x="600" y="500" text-anchor="middle" font-family="Arial, sans-serif" font-size="24" fill="#6c5c54">Janan Lingerie</text>
</svg>
SVG;

        $disk->put($path, $svg);
    }
}
