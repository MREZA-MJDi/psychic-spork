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
            'orders' => $orders,
        ]);
    }
}
