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
            ->with(['chequePayment'])
            ->withCount('items')
            ->latest('placed_at')
            ->latest('id')
            ->limit(10)
            ->get();

        $chequePermission = $user->chequePermission()->first();

        return view('account', [
            'user' => $user,
            'orders' => $orders,
            'chequePermission' => $chequePermission,
            'chequeStatusNames' => [
                'submitted' => 'اطلاعات چک ثبت شده و در صف بررسی است',
                'under_review' => 'مدیر در حال بررسی چک است',
                'awaiting_original' => 'برای این سفارش، ارسال اصل چک درخواست شده است',
                'original_received' => 'اصل چک دریافت شده و در انتظار تأیید نهایی است',
                'accepted' => 'چک پذیرفته شده و سفارش آزاد است؛ تسویه هنوز انجام نشده',
                'deposited' => 'چک به بانک واگذار شده و منتظر نتیجه است',
                'cleared' => 'چک پاس شده و پرداخت تسویه شده است',
                'rejected' => 'درخواست چک رد شده است',
                'bounced' => 'چک برگشت خورده است',
                'cancelled' => 'فرآیند چک لغو شده است',
            ],
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
