<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Media;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'سوتین', 'slug' => 'bras', 'description' => 'مدل‌های متنوع سوتین زنانه برای استفاده روزمره و خاص', 'image' => 'categories/bras.svg'],
            ['name' => 'شورت', 'slug' => 'panties', 'description' => 'شورت‌های زنانه لطیف، راحت و متنوع', 'image' => 'categories/panties.svg'],
            ['name' => 'ست لباس زیر', 'slug' => 'lingerie-sets', 'description' => 'ست‌های هماهنگ و ظریف لباس زیر زنانه', 'image' => 'categories/lingerie-sets.svg'],
            ['name' => 'لباس خواب', 'slug' => 'sleepwear', 'description' => 'لباس خواب‌های لطیف، راحت و زنانه', 'image' => 'categories/sleepwear.svg'],
            ['name' => 'بادی', 'slug' => 'bodysuits', 'description' => 'بادی‌های زنانه با طراحی ظریف و مدرن', 'image' => 'categories/bodysuits.svg'],
            ['name' => 'لباس زیر فانتزی', 'slug' => 'fantasy', 'description' => 'مدل‌های خاص و فانتزی لباس زیر زنانه', 'image' => 'categories/fantasy.svg'],
        ];

        foreach ($categories as $index => $data) {
            $category = Category::updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'is_active' => true,
                    'sort_order' => $index + 1,
                ]
            );

            $this->ensureLocalImage(
                $data['image'],
                $data['name'],
                'JANAN COLLECTION'
            );

            Media::updateOrCreate(
                [
                    'mediable_type' => Category::class,
                    'mediable_id' => $category->id,
                    'collection' => 'cover',
                ],
                [
                    'disk' => 'public',
                    'path' => $data['image'],
                    'original_name' => basename($data['image']),
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
            <stop offset="0%" stop-color="#f8f2ec"/>
            <stop offset="100%" stop-color="#dbcfc5"/>
        </linearGradient>
    </defs>
    <rect width="1200" height="800" fill="url(#bg)"/>
    <rect x="90" y="90" width="1020" height="620" rx="36" fill="#ffffff" opacity=".28"/>
    <text x="600" y="350" text-anchor="middle" font-family="Arial, sans-serif" font-size="34" letter-spacing="6" fill="#6c5c54">{$safeEyebrow}</text>
    <text x="600" y="440" text-anchor="middle" font-family="Arial, sans-serif" font-size="62" font-weight="700" fill="#2f2925">{$safeTitle}</text>
    <text x="600" y="510" text-anchor="middle" font-family="Arial, sans-serif" font-size="24" fill="#6c5c54">Janan Lingerie</text>
</svg>
SVG;

        $disk->put($path, $svg);
    }
}
