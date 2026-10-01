<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChequePayment;
use App\Services\ChequePaymentService;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AdminChequeController extends Controller
{
    public function index(Request $request): View
    {
        $statusNames = [
            'submitted' => 'ثبت‌شده',
            'under_review' => 'در حال بررسی',
            'accepted' => 'تأیید شده',
            'deposited' => 'واریز شده',
            'cleared' => 'تسویه شده',
            'rejected' => 'رد شده',
            'bounced' => 'برگشتی',
            'cancelled' => 'لغو شده',
        ];

        $cheques = ChequePayment::query()
            ->with([
                'order.user',
                'payment',
                'reviewedBy',
            ])
            ->when($request->filled('status'), function ($query) use ($request): void {
                $query->where('status', $request->string('status')->toString());
            })
            ->when($request->filled('q'), function ($query) use ($request): void {
                $term = $request->string('q')->toString();

                $query->where(function ($search) use ($term): void {
                    $search
                        ->where('sayad_id', 'like', "%{$term}%")
                        ->orWhere('cheque_number', 'like', "%{$term}%")
                        ->orWhere('bank_name', 'like', "%{$term}%")
                        ->orWhereHas('order.user', function ($userQuery) use ($term): void {
                            $userQuery
                                ->where('name', 'like', "%{$term}%")
                                ->orWhere('phone', 'like', "%{$term}%")
                                ->orWhere('email', 'like', "%{$term}%");
                        });
                });
            })
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.cheques.index', [
            'cheques' => $cheques,
            'statusNames' => $statusNames,
        ]);
    }

    public function review(
        ChequePayment $chequePayment,
        ChequePaymentService $cheques
    ): RedirectResponse {
        $cheques->moveToReview($chequePayment);

        return back()->with('success', 'چک وارد مرحله بررسی شد.');
    }

    public function accept(
        ChequePayment $chequePayment,
        Request $request,
        ChequePaymentService $cheques
    ): RedirectResponse {
        $cheques->accept(
            $chequePayment,
            $request->user(),
            $request->input('note')
        );

        return back()->with('success', 'چک پذیرفته شد و سفارش تأیید شد.');
    }

    public function reject(
        ChequePayment $chequePayment,
        Request $request,
        ChequePaymentService $cheques
    ): RedirectResponse {
        $cheques->reject(
            $chequePayment,
            $request->user(),
            $request->input('note')
        );

        return back()->with('success', 'چک رد شد.');
    }

    public function deposit(
        ChequePayment $chequePayment,
        Request $request,
        ChequePaymentService $cheques
    ): RedirectResponse {
        $cheques->markDeposited(
            $chequePayment,
            $request->user()
        );

        return back()->with('success', 'واریز چک ثبت شد.');
    }

    public function clear(
        ChequePayment $chequePayment,
        Request $request,
        ChequePaymentService $cheques
    ): RedirectResponse {
        $cheques->markCleared(
            $chequePayment,
            $request->user()
        );

        return back()->with('success', 'تسویه چک ثبت شد و پرداخت نهایی شد.');
    }

    public function bounce(
        ChequePayment $chequePayment,
        Request $request,
        ChequePaymentService $cheques
    ): RedirectResponse {
        $cheques->markBounced(
            $chequePayment,
            $request->user(),
            $request->input('note')
        );

        return back()->with('success', 'برگشت چک ثبت شد.');
    }
}
