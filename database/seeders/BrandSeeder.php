<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Media;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class BrandSeeder extends Seeder
{
    public function run(): void
    {
        $brands = [
            [
                'name' => 'ایزابلا',
                'slug' => 'isabella',
                'description' => 'لباس زیر زنانه با تمرکز بر ظرافت و طراحی روزمره',
                'image' => 'brands/isabella.jpg',
                'image_url' => 'https://images.unsplash.com/photo-1596755389378-c31d21fd1273?w=900&q=85',
            ],
            [
                'name' => 'پانیذ',
                'slug' => 'paniz',
                'description' => 'مدل‌های متنوع و کاربردی برای استفاده روزمره',
                'image' => 'brands/paniz.jpg',
                'image_url' => 'https://images.unsplash.com/photo-1594633312681-425c7b97ccd1?w=900&q=85',
            ],
            [
                'name' => 'آوینا',
                'slug' => 'avina',
                'description' => 'کالکشن‌های لطیف و رنگی لباس زیر زنانه',
                'image' => 'brands/avina.jpg',
                'image_url' => 'https://images.unsplash.com/photo-1541101767792-f9b2b1c4f127?w=900&q=85',
            ],
            [
                'name' => 'نوشه',
                'slug' => 'nosheh',
                'description' => 'مدل‌های راحتی و فانتزی زنانه',
                'image' => 'brands/nosheh.jpg',
                'image_url' => 'https://images.unsplash.com/photo-1596755389378-c31d21fd1273?w=901&q=85',
            ],
            [
                'name' => 'لعیا',
                'slug' => 'laya',
                'description' => 'طراحی‌های ظریف و کلاسیک زنانه',
                'image' => 'brands/laya.jpg',
                'image_url' => 'https://images.unsplash.com/photo-1529139574466-a303027c1d8b?w=900&q=85',
            ],
            [
                'name' => 'اما',
                'slug' => 'emma',
                'description' => 'مدل‌های مدرن و متنوع لباس زیر زنانه',
                'image' => 'brands/emma.jpg',
                'image_url' => 'https://images.unsplash.com/photo-1483985988355-763728e1935b?w=900&q=85',
            ],
            [
                'name' => 'جان جانان',
                'slug' => 'jan-janan',
                'description' => 'برند اختصاصی جانان',
                'image' => 'brands/jan-janan.jpg',
                'image_url' => 'https://images.unsplash.com/photo-1490481651871-ab68de25d43d?w=900&q=85',
            ],
        ];

        foreach ($brands as $data) {
            $brand = Brand::updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'is_active' => true,
                    'meta_title' => $data['name'] . ' | جانان',
                    'meta_description' => 'محصولات برند ' . $data['name'] . ' در فروشگاه جانان',
                ]
            );

            $this->downloadImage(
                $data['image_url'],
                $data['image']
            );

            Media::updateOrCreate(
                [
                    'mediable_type' => Brand::class,
                    'mediable_id' => $brand->id,
                    'collection' => 'logo',
                ],
                [
                    'disk' => 'public',
                    'path' => $data['image'],
                    'original_name' => basename($data['image']),
                    'mime_type' => 'image/jpeg',
                    'alt_text' => 'برند ' . $data['name'],
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
