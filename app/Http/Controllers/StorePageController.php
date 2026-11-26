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
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class StorePageController extends Controller
{
    public function dashboardGuide(SeoService $seo): View
    {
        $markdown = file_get_contents(base_path('docs/راهنمای-مدیریت-فروشگاه.md'));

        return view('pages.dashboard-guide', [
            'seo' => $seo->page(
                'راهنمای استفاده از پنل جانان',
                'راهنمای ساده و کاربردی مدیریت محصولات، سفارش‌ها، مشتریان و محتوای فروشگاه جانان.',
                route('dashboard.guide')
            ),
            'guideHtml' => Str::markdown($markdown, [
                'html_input' => 'strip',
                'allow_unsafe_links' => false,
            ]),
        ]);
    }

    public function about(SeoService $seo): View
    {
        $latestProduct = Product::query()
            ->active()
            ->with(['category', 'brand'])
            ->latest('id')
            ->first();

        $defaults = config('about');

        $settingKeys = collect(array_keys($defaults))
            ->map(fn ($key) => "about.{$key}")
            ->push(
                'contact.phone',
                'contact.email',
                'contact.address',
                'contact.working_hours',
            )
            ->all();

        $settings = SiteSetting::query()
            ->whereIn('key', $settingKeys)
            ->pluck('value', 'key');

        $about = collect($defaults)
            ->mapWithKeys(fn ($default, $key) => [
                $key => filled($settings->get("about.{$key}")) ? $settings->get("about.{$key}") : $default,
            ])
            ->all();

        return view('pages.about', [
            'seo' => $seo->page(
                'درباره و تماس با جانان | فروشگاه لباس زیر',
                'با فروشگاه آنلاین لباس زیر جانان آشنا شوید. برای راهنمایی انتخاب محصول و سایز، پیگیری سفارش یا خرید عمده از فرم تماس با پشتیبانی جانان استفاده کنید.',
                route('about')
            ),
            'about' => $about,
            'latestProduct' => $latestProduct,
            'aboutStats' => [
                'products' => Product::query()->active()->count(),
                'categories' => Category::query()->active()->count(),
                'brands' => Brand::query()->active()->count(),
            ],
            'contactStore' => [
                'phone' => $settings->get('contact.phone', config('app.store_contact.phone')),
                'email' => $settings->get('contact.email', config('app.store_contact.email')),
                'address' => $settings->get('contact.address', config('app.store_contact.address')),
                'working_hours' => $settings->get('contact.working_hours', config('app.store_contact.working_hours')),
            ],
        ]);
    }

    public function contact(): RedirectResponse
    {
        return redirect()->to(route('about') . '#contact');
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

            return redirect()
                ->to(route('about') . '#contact')
                ->with('success', 'پیامت با موفقیت ثبت شد. تیم جانان بعد از بررسی با تو در ارتباط می‌شود.');
        } catch (Throwable $e) {
            report($e);

            return redirect()
                ->to(route('about') . '#contact')
                ->withInput()
                ->with('error', 'ثبت پیام انجام نشد. لطفاً دوباره تلاش کن.');
        }
    }

    public function club(Request $request, SeoService $seo): View
    {
        return view('pages.club', [
            'isCustomer' => $request->user()?->isCustomer() ?? false,
            'seo' => $seo->page(
                'باشگاه مشتریان جانان',
                'باشگاه مشتریان جانان برای پیگیری سفارش، دسترسی به حساب کاربری، خرید عمده و ارتباط با پشتیبانی.',
                route('club')
            ),
        ]);
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
