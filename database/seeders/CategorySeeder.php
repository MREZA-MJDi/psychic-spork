<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Media;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'سوتین',
                'slug' => 'bras',
                'description' => 'مدل‌های متنوع سوتین زنانه برای استفاده روزمره و خاص',
                'image' => 'categories/bras.jpg',
                'image_url' => 'https://images.unsplash.com/photo-1596755389378-c31d21fd1273?w=1000&q=85',
            ],
            [
                'name' => 'شورت',
                'slug' => 'panties',
                'description' => 'شورت‌های زنانه لطیف، راحت و متنوع',
                'image' => 'categories/panties.jpg',
                'image_url' => 'https://images.unsplash.com/photo-1541101767792-f9b2b1c4f127?w=1000&q=85',
            ],
            [
                'name' => 'ست لباس زیر',
                'slug' => 'lingerie-sets',
                'description' => 'ست‌های هماهنگ و ظریف لباس زیر زنانه',
                'image' => 'categories/lingerie-sets.jpg',
                'image_url' => 'https://images.unsplash.com/photo-1594633312681-425c7b97ccd1?w=1000&q=85',
            ],
            [
                'name' => 'لباس خواب',
                'slug' => 'sleepwear',
                'description' => 'لباس خواب‌های لطیف، راحت و زنانه',
                'image' => 'categories/sleepwear.jpg',
                'image_url' => 'https://images.unsplash.com/photo-1483985988355-763728e1935b?w=1000&q=85',
            ],
            [
                'name' => 'بادی',
                'slug' => 'bodysuits',
                'description' => 'بادی‌های زنانه با طراحی ظریف و مدرن',
                'image' => 'categories/bodysuits.jpg',
                'image_url' => 'https://images.unsplash.com/photo-1529139574466-a303027c1d8b?w=1000&q=85',
            ],
            [
                'name' => 'لباس زیر فانتزی',
                'slug' => 'fantasy',
                'description' => 'مدل‌های خاص و فانتزی لباس زیر زنانه',
                'image' => 'categories/fantasy.jpg',
                'image_url' => 'https://images.unsplash.com/photo-1490481651871-ab68de25d43d?w=1000&q=85',
            ],
        ];

        foreach ($categories as $index => $data) {
            $category = Category::updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'is_active' => true,
                    'sort_order' => $index + 1,
                    'meta_title' => $data['name'] . ' | جانان',
                    'meta_description' => $data['description'] . ' در فروشگاه جانان',
                ]
            );

            $this->downloadImage(
                $data['image_url'],
                $data['image']
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
                    'mime_type' => 'image/jpeg',
                    'alt_text' => $data['name'],
                    'sort_order' => 0,
                ]
            );
        }
    }

    private function downloadImage(string $url, string $path): void
    {
        if (Storage::disk('public')->exists($path)) {
            return;
        }

        $response = Http::timeout(30)->get($url);

        if ($response->successful()) {
            Storage::disk('public')->put($path, $response->body());
        }
    }
}

