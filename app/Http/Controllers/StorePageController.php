<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\Product;
use App\Models\SiteSetting;
use App\Services\SeoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class StorePageController extends Controller
{
    public function about(SeoService $seo): View
    {
        $latestProduct = Product::query()
            ->active()
            ->with(['category', 'brand'])
            ->latest('id')
            ->first();

        $defaults = [
            "hero_title" => "انتخاب خوب،
از شناخت شروع می‌شود.",
            'hero_description' => 'جانان یک فروشگاه آنلاین برای انتخاب آگاهانه‌تر است؛ محصول را واضح می‌بینی، اطلاعاتش را مقایسه می‌کنی و بدون پیچیدگی به خرید می‌رسی.',
            'story_title' => 'قرار نیست برای پیدا کردن یک محصول خوب، بین صفحه‌های شلوغ گم شوی.',
            'story_text_1' => 'جانان با یک ایده ساده ساخته شده: تجربه خرید باید سریع، قابل فهم و قابل اعتماد باشد. برای همین ساختار فروشگاه حول سه چیز می‌چرخد؛ ارائه روشن اطلاعات، مسیر ساده انتخاب و اتصال مستقیم به داده‌های واقعی فروشگاه.',
            'story_text_2' => 'از محصول و دسته‌بندی تا برند، سبد خرید و پرداخت، هر بخش بخشی از یک مسیر واحد است؛ نه چند صفحه جدا از هم.',
            'principle_1_title' => 'شفافیت',
            'principle_1_text' => 'نام، قیمت، موجودی، مشخصات و مسیر خرید باید همان‌جایی دیده شوند که کاربر به آن‌ها نیاز دارد.',
            'principle_2_title' => 'سادگی',
            'principle_2_text' => 'کم کردن مراحل اضافه، پیدا کردن محصول را سریع‌تر می‌کند و تصمیم‌گیری را سبک‌تر نگه می‌دارد.',
            'principle_3_title' => 'جزئیات',
            'principle_3_text' => 'فاصله‌ها، تایپوگرافی، حالت‌های تعاملی و بازخوردهای کوچک بخشی از خود محصول دیجیتال هستند.',
            'cta_title' => 'از کشف شروع کن.',
            'cta_text' => 'محصولی که دنبالش هستی را پیدا کن یا مستقیم با تیم جانان در ارتباط باش.',
        ];

        $about = collect($defaults)
            ->mapWithKeys(fn ($default, $key) => [$key => SiteSetting::getValue("about.{$key}", $default)])
            ->all();

        return view('pages.about', [
            'seo' => $seo->page(
                'درباره جانان — ' . config('app.store_name', 'Janan'),
                'با فلسفه، ساختار و تجربه فروشگاه آنلاین جانان آشنا شوید.',
                route('about')
            ),
            'about' => $about,
            'latestProduct' => $latestProduct,
            'aboutStats' => [
                'products' => Product::query()->active()->count(),
                'categories' => Category::query()->active()->count(),
                'brands' => Brand::query()->active()->count(),
            ],
        ]);
    }

    public function contact(SeoService $seo): View
    {
        return view('pages.contact', [
            'seo' => $seo->page(
                'تماس با جانان — ' . config('app.store_name', 'Janan'),
                'راه‌های ارتباط با فروشگاه و ارسال پیام به پشتیبانی جانان.',
                route('contact')
            ),
            'contactStore' => [
                'phone' => SiteSetting::getValue('contact.phone', env('JANAN_STORE_PHONE')),
                'email' => SiteSetting::getValue('contact.email', env('JANAN_STORE_EMAIL')),
                'address' => SiteSetting::getValue('contact.address', env('JANAN_STORE_ADDRESS')),
                'working_hours' => SiteSetting::getValue('contact.working_hours', env('JANAN_STORE_WORKING_HOURS')),
            ],
        ]);
    }

    public function submitContact(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'subject' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        try {
            ContactMessage::create([
                ...$data,
                'status' => ContactMessage::STATUS_NEW,
            ]);

            return back()->with('success', 'پیامت با موفقیت ثبت شد. تیم جانان بعد از بررسی با تو در ارتباط می‌شود.');
        } catch (Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->with('error', 'ثبت پیام انجام نشد. لطفاً دوباره تلاش کن.');
        }
    }

    public function shipping(SeoService $seo): View
    {
        return view('pages.shipping', [
            'seo' => $seo->page(
                'روش‌های ارسال — ' . config('app.store_name', 'Janan'),
                'اطلاعات و سیاست‌های ارسال سفارش‌های فروشگاه جانان.',
                route('shipping')
            ),
        ]);
    }

    public function returns(SeoService $seo): View
    {
        return view('pages.returns', [
            'seo' => $seo->page(
                'شرایط مرجوعی — ' . config('app.store_name', 'Janan'),
                'اطلاعات و سیاست‌های مرجوعی سفارش‌های فروشگاه جانان.',
                route('returns')
            ),
        ]);
    }

    public function faq(SeoService $seo): View
    {
        return view('pages.faq', [
            'seo' => $seo->page(
                'سوالات متداول — ' . config('app.store_name', 'Janan'),
                'پاسخ به سوالات متداول درباره محصولات، سبد خرید و مسیر سفارش جانان.',
                route('faq')
            ),
        ]);
    }
}
