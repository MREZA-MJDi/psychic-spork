<?php

namespace App\Providers;

use App\Contracts\PaymentGateway;
use App\Models\Cart;
use App\Services\ZarinPalPaymentGateway;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use RuntimeException;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(
            PaymentGateway::class,
            function (): PaymentGateway {
                return match (config('payment.driver')) {
                    'zarinpal' => app(ZarinPalPaymentGateway::class),
                    default => throw new RuntimeException(
                        'Payment gateway driver is not configured.'
                    ),
                };
            }
        );
    }

    public function boot(): void
    {
        View::share([
            'siteBrandNameLatin' => 'Janan',
            'siteBrandNameFa' => 'جانان',
            'siteBrandName' => 'جانان',
            'siteFooterText' => 'فروشگاه آنلاین جانان؛ انتخاب دقیق، تجربه‌ای ساده و سفارش مطمئن.',
            'siteStorePhone' => env('JANAN_STORE_PHONE'),
            'siteStoreEmail' => env('JANAN_STORE_EMAIL'),
            'siteStoreAddress' => env('JANAN_STORE_ADDRESS'),
            'siteStoreWorkingHours' => env('JANAN_STORE_WORKING_HOURS'),
            'siteInstagram' => env('JANAN_INSTAGRAM_URL'),
            'siteTelegram' => env('JANAN_TELEGRAM_URL'),
            'siteWhatsapp' => env('JANAN_WHATSAPP_URL'),
            'siteEnamad' => env('JANAN_ENAMAD_URL'),
            'siteLicense' => env('JANAN_LICENSE_URL'),
        ]);

        View::composer('layouts.store', function ($view): void {
            $cart = auth()->check()
                ? auth()->user()->cart
                : Cart::query()
                    ->where('session_id', request()->session()->getId())
                    ->first();

            $view->with(
                'cartCount',
                (int) ($cart?->items()->sum('quantity') ?? 0)
            );
        });
    }
}
