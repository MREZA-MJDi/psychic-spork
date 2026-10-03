<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Services\SeoService;
use Illuminate\View\View;

class StoreBrandController extends Controller
{
    public function index(SeoService $seo): View
    {
        $brands = Brand::query()
            ->active()
            ->with('logoMedia')
            ->withCount([
                'products as active_products_count' => fn ($query) =>
                $query->where('is_active', true),
            ])
            ->orderBy('name')
            ->orderBy('id')
            ->get();

        return view('brands.index', [
            'seo' => $seo->page(
                'برندها — ' . config('app.store_name', 'Janan'),
                'معرفی برندهای فعال و محصولات مرتبط در فروشگاه جانان.',
                route('brands.index')
            ),
            'brands' => $brands,
        ]);
    }

    public function show(Brand $brand, SeoService $seo, Request $request): View
    {
        abort_unless($brand->is_active, 404);

        $brand->load('logoMedia');

        $sort = in_array($request->query('sort'), [
            'newest',
            'oldest',
            'price_asc',
            'price_desc',
            'name_asc',
            'name_desc',
        ], true) ? $request->query('sort') : 'newest';

        $perPage = in_array((int) $request->query('per_page', 6), [6, 12, 24, 36], true)
            ? (int) $request->query('per_page', 6)
            : 6;

        $products = Product::query()
            ->active()
            ->with([
                'category:id,name,slug',
                'brand:id,name,slug',
                'primaryActiveVariant',
                'primaryGalleryMedia',
                'activeVariants',
            ])
            ->where('brand_id', $brand->id)
            ->when($sort === 'oldest', fn ($query) => $query->orderBy('updated_at')->orderBy('id'))
            ->when($sort === 'price_asc', fn ($query) => $query->orderByEffectivePrice('asc'))
            ->when($sort === 'price_desc', fn ($query) => $query->orderByEffectivePrice('desc'))
            ->when($sort === 'name_asc', fn ($query) => $query->orderBy('name')->orderBy('id'))
            ->when($sort === 'name_desc', fn ($query) => $query->orderByDesc('name')->orderBy('id'))
            ->when($sort === 'newest', fn ($query) => $query->latest('updated_at')->latest('id'))
            ->paginate($perPage)
            ->withQueryString();

        $brandProfile = $this->profileFor($brand);

        return view('brands.show', [
            'seo' => $seo->brand($brand),
            'brand' => $brand,
            'products' => $products,
            'sort' => $sort,
            'perPage' => $perPage,
            'brandProfile' => $brandProfile,
        ]);
    }

    private function profileFor(Brand $brand): array
    {
        if ($brand->slug === 'emma') {
            return [
                'eyebrow' => 'EMA / BRAND PROFILE',
                'position' => 'لباس زیر زنانه با تمرکز روی مدل‌های متنوع و استفاده روزمره.',
                'summary' => 'اما در بازار لباس زیر زنانه ایران با مجموعه‌ای از مدل‌های سوتین، راحتی و روزمره شناخته می‌شود. در معرفی‌های منتشرشده درباره برند، استفاده از متریال باکیفیت و رویکرد تولید حرفه‌ای از محورهای اصلی آن عنوان شده است.',
                'strengths' => [
                    ['title' => 'تنوع کاربرد', 'text' => 'برای انتخاب روزمره و مدل‌های راحتی، تنوع محصول یکی از نقاط پررنگ این برند است.'],
                    ['title' => 'تمرکز روی راحتی', 'text' => 'در مدل‌های موجود، گزینه‌های بدون فنر و مناسب استفاده روزانه دیده می‌شود.'],
                    ['title' => 'تنوع رنگ و مدل', 'text' => 'در برخی مدل‌های EMA تنوع رنگ و سایزبندی قابل توجه است و انتخاب را گسترده‌تر می‌کند.'],
                ],
                'considerations' => [
                    ['title' => 'انتخاب سایز', 'text' => 'سایزبندی بین مدل‌ها می‌تواند متفاوت باشد؛ قبل از خرید باید جدول همان محصول بررسی شود.'],
                    ['title' => 'ساختار هر مدل', 'text' => 'همه محصولات یک سطح از حمایت، فرم‌دهی یا نرمی را ندارند؛ نوع کاپ، فنر و طراحی مدل مهم است.'],
                    ['title' => 'انتخاب بر اساس کاربرد', 'text' => 'برای استفاده روزمره، راحتی یا فرم‌دهی بهتر است مدل را بر اساس نیاز انتخاب کرد، نه فقط نام برند.'],
                ],
                'market' => [
                    'title' => 'جایگاه در بازار',
                    'text' => 'اما در بازاری فعالیت می‌کند که برندهای ایرانی و وارداتی متعددی در لباس زیر زنانه حضور دارند. مقایسه منطقی باید بر اساس شاخص‌هایی مثل تنوع، نوع ساخت، سایزبندی، قیمت و دسترسی انجام شود؛ نه صرفاً نام برند.',
                    'dimensions' => [
                        ['label' => 'محور رقابت', 'value' => 'تنوع + راحتی + قیمت'],
                        ['label' => 'دسته‌های اصلی', 'value' => 'سوتین، راحتی، روزمره'],
                        ['label' => 'مقایسه پیشنهادی', 'value' => 'سایز + متریال + ساخت'],
                    ],
                ],
            ];
        }

        return [
            'eyebrow' => strtoupper($brand->slug) . ' / BRAND PROFILE',
            'position' => $brand->description ?: 'معرفی و محصولات منتخب این برند در جانان.',
            'summary' => 'این پروفایل بر اساس اطلاعات ثبت‌شده در فروشگاه جانان و محصولات فعال این برند ساخته می‌شود و با کامل‌تر شدن اطلاعات برند قابل توسعه است.',
            'strengths' => [
                ['title' => 'هویت مشخص', 'text' => $brand->description ?: 'توضیح تکمیلی برند از داده‌های ثبت‌شده فروشگاه نمایش داده می‌شود.'],
                ['title' => 'محصولات قابل بررسی', 'text' => 'محصولات فعال برند در همین صفحه کنار اطلاعات برند قرار گرفته‌اند تا مقایسه ساده‌تر باشد.'],
                ['title' => 'انتخاب در بستر جانان', 'text' => 'قیمت، موجودی و مشخصات محصولات از داده‌های واقعی فروشگاه خوانده می‌شوند.'],
            ],
            'considerations' => [
                ['title' => 'سایزبندی', 'text' => 'جدول و ویژگی‌های همان محصول را قبل از خرید بررسی کن.'],
                ['title' => 'نوع استفاده', 'text' => 'ساختار، رنگ، سایز و ویژگی‌های هر مدل را جداگانه مقایسه کن.'],
                ['title' => 'موجودی و قیمت', 'text' => 'موجودی و قیمت را در صفحه محصول بررسی کن؛ این داده‌ها ممکن است تغییر کنند.'],
            ],
            'market' => [
                'title' => 'بازار و مقایسه',
                'text' => 'برای مقایسه این برند، معیارهای کاربردی مثل تنوع مدل، جنس، سایزبندی، قیمت و دسترسی را کنار هم ببین.',
                'dimensions' => [
                    ['label' => 'محور اول', 'value' => 'کیفیت و ساخت'],
                    ['label' => 'محور دوم', 'value' => 'تنوع و سایزبندی'],
                    ['label' => 'محور سوم', 'value' => 'قیمت و دسترسی'],
                ],
            ],
        ];
    }
}
