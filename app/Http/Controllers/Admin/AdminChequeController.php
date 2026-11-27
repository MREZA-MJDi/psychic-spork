<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChequePayment;
use App\Models\ChequePermission;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use App\Services\ChequePaymentService;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AdminChequeController extends Controller
{
    public function index(Request $request): View
    {
        $statusNames = [
            'submitted' => 'ثبت‌شده',
            'under_review' => 'در حال بررسی',
            'awaiting_original' => 'در انتظار ارسال اصل چک',
            'original_received' => 'اصل چک دریافت شد؛ منتظر تأیید',
            'accepted' => 'تأیید شده؛ سفارش آزاد است',
            'deposited' => 'واریز شده',
            'cleared' => 'تسویه شده',
            'rejected' => 'رد شده',
            'bounced' => 'برگشتی',
            'cancelled' => 'لغو شده',
        ];

        $filters = $request->validate([
            'tab' => ['nullable', 'in:payments,requests,customers'],
            'access' => ['nullable', 'in:approved,all,disabled'],
            'customer_id' => ['nullable', 'integer', 'min:1'],
            'edit_customer' => ['nullable', 'integer', 'min:1'],
            'cheque_id' => ['nullable', 'integer', 'min:1'],
            'status' => ['nullable', 'string', 'in:' . implode(',', array_keys($statusNames))],
            'q' => ['nullable', 'string', 'max:120'],
        ]);

        $tab = $filters['tab'] ?? 'payments';
        $access = $filters['access'] ?? 'approved';
        $customers = null;
        $cheques = null;
        $selectedCheque = null;
        $selectedCustomer = null;

        if ($tab !== 'payments') {
            $customers = User::query()
                ->customers()
                ->with('chequePermission')
                ->when($tab === 'requests', fn ($query) => $query->whereHas('chequePermission', fn ($permission) => $permission->pending()))
                ->when($tab === 'customers' && $access === 'approved', fn ($query) => $query->whereHas('chequePermission', fn ($permission) => $permission->approved()))
                ->when($tab === 'customers' && $access === 'disabled', fn ($query) => $query->whereHas('chequePermission', fn ($permission) => $permission->where('enabled', false)->whereNotNull('disabled_at')))
                ->when(isset($filters['customer_id']), fn ($query) => $query->whereKey($filters['customer_id']))
                ->when(! empty($filters['q']), function ($query) use ($filters): void {
                    $term = trim($filters['q']);
                    $query->where(fn ($search) => $search->where('name', 'like', "%{$term}%")
                        ->orWhere('phone', 'like', "%{$term}%")
                        ->orWhere('email', 'like', "%{$term}%"));
                })
                ->when($tab === 'requests', fn ($query) => $query->orderByDesc(
                    ChequePermission::query()->select('requested_at')->whereColumn('user_id', 'users.id')->limit(1)
                ))
                ->orderByDesc('id')
                ->paginate(20)
                ->appends($request->except('edit_customer', 'cheque_id'));

            $selectedId = $filters['edit_customer'] ?? $filters['customer_id'] ?? null;
            if ($selectedId) {
                $selectedCustomer = User::query()->customers()->with('chequePermission')->findOrFail($selectedId);
            }
        } else {
            $cheques = ChequePayment::query()
                ->with([
                    'order.user',
                    'payment',
                    'reviewedBy',
                ])
                ->when(! empty($filters['status']), function ($query) use ($filters): void {
                    $query->where('status', $filters['status']);
                })
                ->when(! empty($filters['q']), function ($query) use ($filters): void {
                    $term = trim($filters['q']);
    
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
                ->appends($request->except('cheque_id', 'edit_customer'));

            if (isset($filters['cheque_id'])) {
                $selectedCheque = ChequePayment::query()->with(['order.user', 'payment', 'reviewedBy'])->findOrFail($filters['cheque_id']);
            }
        }

        return view('admin.cheques.index', [
            'chequeAlerts' => self::pendingCounts(),
            'tab' => $tab,
            'access' => $access,
            'customers' => $customers,
            'cheques' => $cheques,
            'selectedCheque' => $selectedCheque,
            'selectedCustomer' => $selectedCustomer,
            'statusNames' => $statusNames,
        ]);
    }

    public static function pendingCounts(): array
    {
        return [
            'requests' => ChequePermission::query()->pending()
                ->whereHas('user', fn ($query) => $query->customers())->count(),
            'payments' => ChequePayment::query()->where('status', 'submitted')->count(),
        ];
    }

    public function alerts(): JsonResponse
    {
        return response()->json(self::pendingCounts())->header('Cache-Control', 'private, no-store');
    }

    public function image(ChequePayment $chequePayment): BinaryFileResponse
    {
        abort_unless(filled($chequePayment->image_path), 404);

        $disk = Storage::disk('local');
        abort_unless($disk->exists($chequePayment->image_path), 404);

        return response()->file($disk->path($chequePayment->image_path), [
            'Content-Type' => $disk->mimeType($chequePayment->image_path) ?: 'application/octet-stream',
            'Cache-Control' => 'private, no-store, max-age=0',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function review(
        ChequePayment $chequePayment,
        Request $request,
        ChequePaymentService $cheques
    ): RedirectResponse {
        $cheques->moveToReview($chequePayment, $request->user());

        return back()->with('success', 'چک وارد مرحله بررسی شد.');
    }

    public function requestOriginal(
        ChequePayment $chequePayment,
        Request $request,
        ChequePaymentService $cheques
    ): RedirectResponse {
        $data = $request->validate([
            'note' => ['required', 'string', 'min:5', 'max:2000'],
        ]);
        $cheques->requestOriginal($chequePayment, $request->user(), $data['note']);
        return back()->with('success', 'درخواست ارسال اصل چک برای مشتری ثبت شد.');
    }

    public function originalReceived(
        ChequePayment $chequePayment,
        Request $request,
        ChequePaymentService $cheques
    ): RedirectResponse {
        $cheques->markOriginalReceived(
            $chequePayment,
            $request->user(),
            $this->note($request)
        );
        return back()->with('success', 'دریافت اصل چک ثبت شد؛ اکنون می‌توانی چک را تأیید و سفارش را آزاد کنی.');
    }

    public function accept(
        ChequePayment $chequePayment,
        Request $request,
        ChequePaymentService $cheques
    ): RedirectResponse {
        $cheques->accept(
            $chequePayment,
            $request->user(),
            $this->note($request)
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
            $this->note($request)
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
            $this->note($request)
        );

        return back()->with('success', 'برگشت چک ثبت شد.');
    }

    private function note(Request $request): ?string
    {
        return $request->validate([
            'note' => ['nullable', 'string', 'max:2000'],
        ])['note'] ?? null;
    }
}
