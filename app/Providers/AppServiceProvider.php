<?php

namespace App\Providers;

use App\Contracts\PaymentGateway;
use App\Models\Cart;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\Paginator;
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
        // Catch accidental N+1 queries while developing locally; production remains unchanged.
        Model::preventLazyLoading(app()->isLocal());
        Paginator::defaultView('vendor.pagination.store');
        Paginator::defaultSimpleView('vendor.pagination.simple-store');

        RateLimiter::for('login', function (Request $request): Limit {
            $identifier = Str::lower(trim((string) $request->input('phone')));

            return Limit::perMinute(5)
                ->by($identifier . '|' . $request->ip());
        });

        // Composer's package:discover and other maintenance commands must not
        // require a configured database or the cache table to bootstrap Laravel.
        // Real HTTP requests and feature tests still read persisted site settings.
        $contactSettings = ($this->app->runningInConsole() && ! $this->app->runningUnitTests())
            ? []
            : SiteSetting::contactValues();

        $brandNameFa = (string) config('app.store_name_fa', config('app.store_name', 'جانه جانان'));
        $brandNameLatin = (string) config('app.store_name_latin', 'Jane Janan');

        View::share([
            'siteBrandNameLatin' => $brandNameLatin,
            'siteBrandNameFa' => $brandNameFa,
            'siteBrandName' => $brandNameFa,
            'siteFooterText' => "فروشگاه آنلاین {$brandNameFa}؛ انتخاب دقیق، تجربه‌ای ساده و سفارش مطمئن.",
            'siteStorePhone' => $contactSettings['contact.phone'] ?? config('app.store_contact.phone'),
            'siteStoreEmail' => $contactSettings['contact.email'] ?? config('app.store_contact.email'),
            'siteStoreAddress' => $contactSettings['contact.address'] ?? config('app.store_contact.address'),
            'siteStoreWorkingHours' => $contactSettings['contact.working_hours'] ?? config('app.store_contact.working_hours'),
        ]);

        View::composer('layouts.admin', function ($view): void {
            $view->with('chequeAlerts', \App\Http\Controllers\Admin\AdminChequeController::pendingCounts());
        });

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
