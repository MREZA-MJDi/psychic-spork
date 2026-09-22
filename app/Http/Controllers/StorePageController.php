<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class StorePageController extends Controller
{
    public function about(): View
    {
        return view('pages.about');
    }

    public function contact(): View
    {
        return view('pages.contact', [
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

    public function shipping(): View
    {
        return view('pages.shipping');
    }

    public function returns(): View
    {
        return view('pages.returns');
    }

    public function faq(): View
    {
        return view('pages.faq');
    }
}
