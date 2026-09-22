<?php

namespace App\Providers;

use App\Models\Cart;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        View::composer('layouts.store', function ($view): void {
            $cart = auth()->check()
                ? auth()->user()->cart
                : Cart::query()
                    ->where('session_id', request()->session()->getId())
                    ->first();

            $view->with([
                'cartCount' => (int) ($cart?->items()->sum('quantity') ?? 0),
                'siteBrandNameLatin' => 'Janan',
                'siteBrandName' => 'جانان',
            ]);
        });
    }
}
