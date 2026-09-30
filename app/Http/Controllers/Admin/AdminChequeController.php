<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChequePayment;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use App\Services\ChequePaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AdminChequeController extends Controller
{
    public function index(Request $request): View
    {
        $statuses = [
            'submitted' => 'ثبت‌شده',
            'under_review' => 'در حال بررسی',
            'accepted' => 'تأیید شده',
            'deposited' => 'واریز شده',
            'cleared' => 'تسویه شده',
            'rejected' => 'رد شده',
            'bounced' => 'برگشتی',
        ];

        $cheques = ChequePayment::query()
            ->with(['order.user', 'payment'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
            ->when($request->filled('q'), function ($query) use ($request): void {
                $search = $request->string('q')->toString();

                $query->where(function ($query) use ($search): void {
                    $query->where('sayad_id', 'like', '%' . $search . '%')
                        ->orWhere('cheque_number', 'like', '%' . $search . '%')
                        ->orWhere('bank_name', 'like', '%' . $search . '%')
                        ->orWhereHas('order', function ($orderQuery) use ($search): void {
                            $orderQuery->where('order_number', 'like', '%' . $search . '%')
                                ->orWhere('customer_name', 'like', '%' . $search . '%');
                        });
                });
            })
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        $awaitingCount = ChequePayment::query()
            ->whereIn('status', ['submitted', 'under_review'])
            ->count();

        $awaitingAmount = (float) ChequePayment::query()
            ->whereIn('status', ['submitted', 'under_review'])
            ->sum('amount');

        return view('admin.cheques.index', compact(
            'cheques',
            'statuses',
            'awaitingCount',
            'awaitingAmount'
        ));
    }

    public function image(ChequePayment $chequePayment): Response
    {
        abort_unless($chequePayment->image_path, 404);

        $disk = Storage::disk('local');
        abort_unless($disk->exists($chequePayment->image_path), 404);

        return response()->file($disk->path($chequePayment->image_path));
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
