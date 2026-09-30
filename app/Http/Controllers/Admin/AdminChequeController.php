<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChequePayment;
use App\Services\ChequePaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AdminChequeController extends Controller
{
    public function index(Request $request): View
    {
        $query = ChequePayment::query()
            ->with(['order.user'])
            ->latest('id');

        if ($status = $request->string('status')->toString()) {
            $query->where('status', $status);
        }

        if ($search = trim($request->string('q')->toString())) {
            $query->where(function ($q) use ($search): void {
                $q->where('cheque_number', 'like', "%{$search}%")
                    ->orWhere('sayad_id', 'like', "%{$search}%")
                    ->orWhere('bank_name', 'like', "%{$search}%")
                    ->orWhereHas('order.user', function ($userQuery) use ($search): void {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
            });
        }

        $cheques = $query->paginate(20)->withQueryString();

        return view('admin.cheques.index', [
            'cheques' => $cheques,
            'statuses' => ChequePayment::STATUSES,
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
