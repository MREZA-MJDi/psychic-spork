<?php

namespace App\Providers;

use App\Contracts\PaymentGateway;
use App\Models\Cart;
use App\Models\SiteSetting;
use App\Services\ZarinPalPaymentGateway;
use App\Services\PaymentMethodManager;
use App\Services\OnlinePaymentMethod;
use App\Services\ChequePaymentMethod;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
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

        $this->app->singleton(PaymentMethodManager::class, function (): PaymentMethodManager {
            return new PaymentMethodManager([
                'online' => app(OnlinePaymentMethod::class),
                'cheque' => app(ChequePaymentMethod::class),
            ]);
        });
    }

    public function boot(): void
    {
        RateLimiter::for('login', function (Request $request): Limit {
            $identifier = Str::lower(trim((string) $request->input('phone')));

            return Limit::perMinute(5)
                ->by($identifier . '|' . $request->ip());
        });

        $contactSettings = SiteSetting::contactValues();

        $brandNameFa = (string) config('app.store_name_fa', config('app.store_name', 'جانه جانان'));
        $brandNameLatin = (string) config('app.store_name_latin', 'Jane Janan');

        View::share([
            'siteBrandNameLatin' => $brandNameLatin,
            'siteBrandNameFa' => $brandNameFa,
            'siteBrandName' => $brandNameFa,
            'siteFooterText' => "فروشگاه آنلاین {$brandNameFa}؛ انتخاب دقیق، تجربه‌ای ساده و سفارش مطمئن.",
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
