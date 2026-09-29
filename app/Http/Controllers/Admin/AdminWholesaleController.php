<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChequePermission;
use App\Models\User;
use App\Models\WholesaleProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminWholesaleController extends Controller
{
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

        return back()->with('success', 'دسترسی خرید عمده مشتری تأیید شد.');
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

            $customer->chequePermission?->update([
                'enabled' => false,
                'disabled_by' => $request->user()->id,
                'disabled_at' => now(),
                'admin_note' => 'دسترسی عمده مشتری تعلیق شد.',
            ]);
        });

        return back()->with('success', 'دسترسی خرید عمده مشتری تعلیق شد.');
    }

    public function enableCheque(User $customer, Request $request): RedirectResponse
    {
        abort_unless($customer->isCustomer(), 404);

        $data = $request->validate([
            'max_order_amount' => ['nullable', 'numeric', 'min:0'],
            'note' => ['nullable', 'string', 'max:2000'],
        ]);

        abort_unless(
            $customer->wholesaleProfile?->isApproved(),
            422,
            'ابتدا باید دسترسی خرید عمده مشتری تأیید شود.'
        );

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
