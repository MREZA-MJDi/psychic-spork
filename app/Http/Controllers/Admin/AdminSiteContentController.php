<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class AdminSiteContentController extends Controller
{
    public function about(): View
    {
        $defaults = config('about');

        $about = collect($defaults)
            ->mapWithKeys(
                function ($default, $key) {
                    $value = SiteSetting::getValue("about.$key");

                    return [$key => filled($value) ? $value : $default];
                }
            )
            ->all();

        return view('admin.content.about', compact('about'));
    }

    public function updateAbout(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'hero_title' => ['required', 'string', 'max:180'],
            'hero_description' => ['required', 'string', 'max:1000'],
            'story_title' => ['required', 'string', 'max:240'],
            'story_text_1' => ['required', 'string', 'max:1500'],
            'story_text_2' => ['nullable', 'string', 'max:1500'],
            'principle_1_title' => ['required', 'string', 'max:100'],
            'principle_1_text' => ['required', 'string', 'max:700'],
            'principle_2_title' => ['required', 'string', 'max:100'],
            'principle_2_text' => ['required', 'string', 'max:700'],
            'principle_3_title' => ['required', 'string', 'max:100'],
            'principle_3_text' => ['required', 'string', 'max:700'],
            'cta_title' => ['required', 'string', 'max:140'],
            'cta_text' => ['required', 'string', 'max:500'],
        ]);

        try {
            foreach ($data as $key => $value) {
                SiteSetting::setValue("about.$key", trim($value ?? ''));
            }

            return back()->with('success', 'محتوای درباره ما با موفقیت ذخیره شد.');
        } catch (Throwable $e) {
            report($e);

            return back()->withInput()->with('error', 'ذخیره محتوای درباره ما انجام نشد.');
        }
    }

    public function contact(Request $request): View
    {
        $messages = ContactMessage::query()
            ->when($request->filled('q'), function ($query) use ($request): void {
                $search = trim((string) $request->input('q'));

                $query->where(function ($query) use ($search): void {
                    $query
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('subject', 'like', "%{$search}%")
                        ->orWhere('message', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $contact = [
            'phone' => SiteSetting::getValue('contact.phone', config('app.store_contact.phone')),
            'email' => SiteSetting::getValue('contact.email', config('app.store_contact.email')),
            'address' => SiteSetting::getValue('contact.address', config('app.store_contact.address')),
            'working_hours' => SiteSetting::getValue('contact.working_hours', config('app.store_contact.working_hours')),
        ];

        return view('admin.contact.index', compact('messages', 'contact'));
    }

    public function updateContact(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'working_hours' => ['nullable', 'string', 'max:180'],
        ]);

        try {
            foreach ($data as $key => $value) {
                SiteSetting::setValue("contact.$key", trim((string) $value));
            }

            return back()->with('success', 'اطلاعات تماس فروشگاه ذخیره شد.');
        } catch (Throwable $e) {
            report($e);

            return back()->withInput()->with('error', 'ذخیره اطلاعات تماس انجام نشد.');
        }
    }

    public function showContact(ContactMessage $message): View
    {
        if ($message->status === ContactMessage::STATUS_NEW) {
            $message->markAsRead();
        }

        return view('admin.contact.show', compact('message'));
    }

    public function updateContactStatus(Request $request, ContactMessage $message): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:new,read,replied'],
        ]);

        try {
            match ($data['status']) {
                ContactMessage::STATUS_REPLIED => $message->markAsReplied(),
                ContactMessage::STATUS_READ => $message->markAsRead(),
                default => $message->forceFill([
                    'status' => ContactMessage::STATUS_NEW,
                    'read_at' => null,
                    'replied_at' => null,
                ])->save(),
            };

            return back()->with('success', 'وضعیت پیام به‌روزرسانی شد.');
        } catch (Throwable $e) {
            report($e);

            return back()->with('error', 'تغییر وضعیت پیام انجام نشد.');
        }
    }
}
