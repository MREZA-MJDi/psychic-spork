<?php

namespace AppHttpControllers;

use AppModelsContactMessage;
use AppServicesSeoService;
use IlluminateHttpRedirectResponse;
use IlluminateHttpRequest;
use IlluminateViewView;
use Throwable;

class StorePageController extends Controller
{
    public function about(SeoService $seo): View
    {
        return view('pages.about', [
            'seo' => $seo->page(
                'درباره جانان — ' . config('app.store_name', 'Janan'),
                'آشنایی با فلسفه، ساختار و تجربه فروشگاه آنلاین جانان.',
                route('about')
            ),
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
                'phone' => env('JANAN_STORE_PHONE'),
                'email' => env('JANAN_STORE_EMAIL'),
                'address' => env('JANAN_STORE_ADDRESS'),
                'working_hours' => env('JANAN_STORE_WORKING_HOURS'),
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

            return back()->with('success', 'پیام شما با موفقیت برای جانان ارسال شد.');
        } catch (Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->with('error', 'ارسال پیام انجام نشد. دوباره تلاش کنید.');
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
