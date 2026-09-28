<?php

namespace App\Providers;

use App\Contracts\PaymentGateway;
use App\Models\Cart;
use App\Models\SiteSetting;
use App\Services\ZarinPalPaymentGateway;
use Illuminate\Support\Facades\Schema;
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
        $contactSettings = Schema::hasTable('site_settings')
            ? SiteSetting::query()
                ->whereIn('key', [
                    'contact.phone',
                    'contact.email',
                    'contact.address',
                    'contact.working_hours',
                ])
                ->pluck('value', 'key')
            : collect();

        View::share([
            'siteBrandNameLatin' => 'Janan',
            'siteBrandNameFa' => 'جانان',
            'siteBrandName' => 'جانان',
            'siteFooterText' => 'فروشگاه آنلاین جانان؛ انتخاب دقیق، تجربه‌ای ساده و سفارش مطمئن.',
            'siteStorePhone' => $contactSettings['contact.phone'] ?? env('JANAN_STORE_PHONE'),
            'siteStoreEmail' => $contactSettings['contact.email'] ?? env('JANAN_STORE_EMAIL'),
            'siteStoreAddress' => $contactSettings['contact.address'] ?? env('JANAN_STORE_ADDRESS'),
            'siteStoreWorkingHours' => $contactSettings['contact.working_hours'] ?? env('JANAN_STORE_WORKING_HOURS'),
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
