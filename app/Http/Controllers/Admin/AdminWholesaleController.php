<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChequePermission;
use App\Models\User;
use App\Models\WholesaleProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminWholesaleController extends Controller
{
    public function index(Request $request): View
    {
        $profiles = WholesaleProfile::query()
            ->with(['user.chequePermission'])
            ->when($request->filled('q'), function ($query) use ($request): void {
                $term = $request->string('q')->toString();

                $query->whereHas('user', function ($userQuery) use ($term): void {
                    $userQuery->where('name', 'like', "%{$term}%")
                        ->orWhere('email', 'like', "%{$term}%")
                        ->orWhere('phone', 'like', "%{$term}%");
                })->orWhere('business_name', 'like', "%{$term}%");
            })
            ->when($request->filled('status'), fn ($query) =>
                $query->where('status', $request->string('status')->toString())
            )
            ->latest('updated_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.wholesale.index', [
            'profiles' => $profiles,
            'statuses' => WholesaleProfile::STATUSES,
        ]);
    }

    public function approve(User $customer, Request $request): RedirectResponse
    {
        abort_unless($customer->isCustomer(), 404);

        WholesaleProfile::updateOrCreate(
            ['user_id' => $customer->id],
            [
                'status' => 'approved',
                'approved_by' => $request->user()->id,
                'approved_at' => now(),
                'suspended_by' => null,
                'suspended_at' => null,
                'admin_note' => $request->input('note'),
            ]
        );

        return back()->with('success', 'پروفایل خرید عمده مشتری تأیید شد.');
    }

    public function reject(User $customer, Request $request): RedirectResponse
    {
        abort_unless($customer->isCustomer(), 404);

        $profile = WholesaleProfile::query()
            ->where('user_id', $customer->id)
            ->firstOrFail();

        $profile->update([
            'status' => 'rejected',
            'approved_by' => null,
            'approved_at' => null,
            'suspended_by' => null,
            'suspended_at' => null,
            'admin_note' => $request->input('note') ?: $profile->admin_note,
        ]);

        return back()->with('success', 'درخواست خرید عمده مشتری رد شد.');
    }

    public function updateTerms(User $customer, Request $request): RedirectResponse
    {
        abort_unless($customer->isCustomer(), 404);

        $data = $request->validate([
            'minimum_order_amount' => ['nullable', 'numeric', 'min:0'],
            'minimum_order_quantity' => ['nullable', 'integer', 'min:1'],
            'note' => ['nullable', 'string', 'max:2000'],
        ]);

        $profile = WholesaleProfile::query()
            ->where('user_id', $customer->id)
            ->firstOrFail();

        $profile->update([
            'minimum_order_amount' => $data['minimum_order_amount'] ?? null,
            'minimum_order_quantity' => $data['minimum_order_quantity'] ?? null,
            'admin_note' => $data['note'] ?? $profile->admin_note,
        ]);

        return back()->with('success', 'شرایط خرید عمده مشتری به‌روزرسانی شد.');
    }

    public function suspend(User $customer, Request $request): RedirectResponse
    {
        abort_unless($customer->isCustomer(), 404);

        DB::transaction(function () use ($customer, $request): void {
            $profile = WholesaleProfile::query()
                ->where('user_id', $customer->id)
                ->lockForUpdate()
                ->firstOrFail();

            $profile->update([
                'status' => 'suspended',
                'suspended_by' => $request->user()->id,
                'suspended_at' => now(),
                'admin_note' => $request->input('note') ?: $profile->admin_note,
            ]);

            // Wholesale profile status and cheque permission are independent controls.
        });

        return back()->with('success', 'پروفایل خرید عمده مشتری تعلیق شد.');
    }

    public function enableCheque(User $customer, Request $request): RedirectResponse
    {
        abort_unless($customer->isCustomer(), 404);

        $data = $request->validate([
            'max_order_amount' => ['nullable', 'numeric', 'min:0'],
            'note' => ['nullable', 'string', 'max:2000'],
        ]);

        ChequePermission::updateOrCreate(
            ['user_id' => $customer->id],
            [
                'enabled' => true,
                'max_order_amount' => $data['max_order_amount'] ?? null,
                'approved_by' => $request->user()->id,
                'approved_at' => now(),
                'disabled_by' => null,
                'disabled_at' => null,
                'admin_note' => $data['note'] ?? null,
            ]
        );

        return back()->with('success', 'پرداخت چکی برای این مشتری فعال شد.');
    }

    public function disableCheque(User $customer, Request $request): RedirectResponse
    {
        abort_unless($customer->isCustomer(), 404);

        $permission = $customer->chequePermission;

        if ($permission) {
            $permission->update([
                'enabled' => false,
                'disabled_by' => $request->user()->id,
                'disabled_at' => now(),
                'admin_note' => $request->input('note') ?: $permission->admin_note,
            ]);
        }

        return back()->with('success', 'پرداخت چکی برای این مشتری غیرفعال شد.');
    }
}
