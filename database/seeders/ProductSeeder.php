<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Media;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $bras = Category::where('slug', 'bras')->firstOrFail();
        $panties = Category::where('slug', 'panties')->firstOrFail();
        $sets = Category::where('slug', 'lingerie-sets')->firstOrFail();
        $sleepwear = Category::where('slug', 'sleepwear')->firstOrFail();
        $bodysuits = Category::where('slug', 'bodysuits')->firstOrFail();
        $fantasy = Category::where('slug', 'fantasy')->firstOrFail();

        $isabella = Brand::where('slug', 'isabella')->firstOrFail();
        $paniz = Brand::where('slug', 'paniz')->firstOrFail();
        $avina = Brand::where('slug', 'avina')->firstOrFail();
        $nosheh = Brand::where('slug', 'nosheh')->firstOrFail();
        $laya = Brand::where('slug', 'laya')->firstOrFail();
        $emma = Brand::where('slug', 'emma')->firstOrFail();
        $janJanan = Brand::where('slug', 'jan-janan')->firstOrFail();

        $products = [
            [
                'category_id' => $bras->id,
                'brand_id' => $isabella->id,
                'name' => 'سوتین دانتل ایزابلا',
                'slug' => 'isabella-lace-bra',
                'short_description' => 'سوتین ظریف با طراحی دانتل و فرم راحت',
                'description' => 'مدلی ظریف و زنانه با طراحی مناسب استفاده روزمره.',
                'image' => 'products/isabella-lace-bra.jpg',
                'is_featured' => true,
            ],
            [
                'category_id' => $bras->id,
                'brand_id' => $paniz->id,
                'name' => 'سوتین کلاسیک پانیذ',
                'slug' => 'paniz-classic-bra',
                'short_description' => 'مدل کلاسیک و راحت برای استفاده روزمره',
                'description' => 'طراحی ساده و کاربردی با تمرکز بر راحتی.',
                'image' => 'products/paniz-classic-bra.jpg',
                'is_featured' => true,
            ],
            [
                'category_id' => $panties->id,
                'brand_id' => $avina->id,
                'name' => 'شورت نخی آوینا',
                'slug' => 'avina-cotton-panty',
                'short_description' => 'شورت نخی نرم و لطیف',
                'description' => 'انتخابی راحت برای استفاده روزمره با طراحی ساده.',
                'image' => 'products/avina-cotton-panty.jpg',
                'is_featured' => false,
            ],
            [
                'category_id' => $panties->id,
                'brand_id' => $nosheh->id,
                'name' => 'شورت فانتزی نوشه',
                'slug' => 'nosheh-fancy-panty',
                'short_description' => 'مدلی ظریف با طراحی فانتزی',
                'description' => 'طراحی زنانه و ظریف برای کالکشن فانتزی.',
                'image' => 'products/nosheh-fancy-panty.jpg',
                'is_featured' => true,
            ],
            [
                'category_id' => $sets->id,
                'brand_id' => $laya->id,
                'name' => 'ست ظریف لعیا',
                'slug' => 'laya-delicate-set',
                'short_description' => 'ست هماهنگ با طراحی کلاسیک',
                'description' => 'ست زنانه با طراحی ظریف و هماهنگ.',
                'image' => 'products/laya-delicate-set.jpg',
                'is_featured' => true,
            ],
            [
                'category_id' => $sleepwear->id,
                'brand_id' => $emma->id,
                'name' => 'لباس خواب مدرن اما',
                'slug' => 'emma-modern-sleepwear',
                'short_description' => 'لباس خواب لطیف با طراحی مدرن',
                'description' => 'مدلی راحت و ظریف برای کالکشن لباس خواب.',
                'image' => 'products/emma-modern-sleepwear.jpg',
                'is_featured' => false,
            ],
            [
                'category_id' => $bodysuits->id,
                'brand_id' => $janJanan->id,
                'name' => 'بادی جان جانان',
                'slug' => 'jan-janan-bodysuit',
                'short_description' => 'بادی اختصاصی جانان با طراحی ظریف',
                'description' => 'طراحی اختصاصی جانان با تمرکز بر ظرافت و فرم.',
                'image' => 'products/jan-janan-bodysuit.jpg',
                'is_featured' => true,
            ],
            [
                'category_id' => $fantasy->id,
                'brand_id' => $isabella->id,
                'name' => 'کالکشن فانتزی ایزابلا',
                'slug' => 'isabella-fantasy-collection',
                'short_description' => 'مدلی خاص از کالکشن فانتزی ایزابلا',
                'description' => 'طراحی خاص و ظریف برای کالکشن فانتزی.',
                'image' => 'products/isabella-fantasy-collection.jpg',
                'is_featured' => true,
            ],
        ];

        foreach ($products as $index => $data) {
            $product = Product::updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'category_id' => $data['category_id'],
                    'brand_id' => $data['brand_id'],
                    'name' => $data['name'],
                    'short_description' => $data['short_description'],
                    'description' => $data['description'],
                    'is_active' => true,
                    'is_featured' => $data['is_featured'],
                    'sort_order' => $index + 1,
                    'meta_title' => $data['name'] . ' | جانان',
                    'meta_description' => $data['short_description'] . ' در فروشگاه جانان',
                ]
            );

            $this->downloadImage(
                $this->productImageUrl($index),
                $data['image']
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
                    'path' => $data['image'],
                    'original_name' => basename($data['image']),
                    'mime_type' => 'image/jpeg',
                    'alt_text' => $data['name'],
                ]
            );
        }
    }

    private function productImageUrl(int $index): string
    {
        $images = [
            'https://images.unsplash.com/photo-1596755389378-c31d21fd1273?w=1000&q=85',
            'https://images.unsplash.com/photo-1594633312681-425c7b97ccd1?w=1000&q=85',
            'https://images.unsplash.com/photo-1541101767792-f9b2b1c4f127?w=1000&q=85',
            'https://images.unsplash.com/photo-1529139574466-a303027c1d8b?w=1000&q=85',
            'https://images.unsplash.com/photo-1490481651871-ab68de25d43d?w=1000&q=85',
            'https://images.unsplash.com/photo-1483985988355-763728e1935b?w=1000&q=85',
            'https://images.unsplash.com/photo-1525507119028-ed4c629a60a3?w=1000&q=85',
            'https://images.unsplash.com/photo-1445205170230-053b83016050?w=1000&q=85',
        ];

        return $images[$index % count($images)];
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

