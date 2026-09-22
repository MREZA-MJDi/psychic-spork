<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $orders = $user
            ->orders()
            ->withCount('items')
            ->latest('placed_at')
            ->latest('id')
            ->limit(10)
            ->get();

        return view('account', [
            'user' => $user,
            'orders' => $orders,
            'statusNames' => [
                'pending' => 'در انتظار',
                'confirmed' => 'تأیید شده',
                'preparing' => 'در حال آماده‌سازی',
                'shipped' => 'ارسال شده',
                'delivered' => 'تحویل شده',
                'cancelled' => 'لغو شده',
                'returned' => 'مرجوعی',
            ],
        ]);
    }
}
