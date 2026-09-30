@extends('layouts.admin')

@section('title', 'پرداخت‌های چکی')
@section('page-title', 'پرداخت‌های چکی')

@section('content')
<div class="admin-page-head">
    <div>
        <h1 class="admin-page-head__title">پرداخت‌های چکی</h1>
        <p class="admin-page-head__text">
            بررسی، تأیید، واریز و تسویه چک‌های ثبت‌شده توسط مشتریان عمده.
        </p>
    </div>
</div>

<div class="admin-card admin-filter-card">
    <form method="GET" action="{{ route('admin.cheques.index') }}">
        <div class="admin-filter-grid">
            <div class="admin-field">
                <label for="q">جستجو</label>
                <input
                    id="q"
                    type="search"
                    name="q"
                    value="{{ request('q') }}"
                    placeholder="نام مشتری، موبایل، شناسه صیادی یا بانک..."
                >
            </div>

            <div class="admin-field">
                <label for="status">وضعیت</label>
                <select id="status" name="status">
                    <option value="">همه وضعیت‌ها</option>
                    @foreach($statusNames as $status => $label)
                        <option value="{{ $status }}" @selected(request('status') === $status)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="admin-filter-actions">
                <button class="admin-btn admin-btn--secondary" type="submit">اعمال فیلتر</button>
                <a class="admin-btn admin-btn--ghost" href="{{ route('admin.cheques.index') }}">پاک کردن</a>
            </div>
        </div>
    </form>
</div>

<div class="admin-card">
    <div class="admin-card-header">
        <div>
            <h2 class="admin-card-title">فهرست چک‌ها</h2>
            <p class="admin-card-description">{{ number_format($cheques->total()) }} مورد</p>
        </div>
    </div>

    @if($cheques->count())
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                <tr>
                    <th>مشتری / سفارش</th>
                    <th>چک</th>
                    <th>مبلغ</th>
                    <th>سررسید</th>
                    <th>وضعیت</th>
                    <th>عملیات</th>
                </tr>
                </thead>
                <tbody>
                @foreach($cheques as $cheque)
                    @php
                        $status = $cheque->status;
                        $statusClass = match($status) {
                            'submitted', 'under_review' => 'warning',
                            'accepted', 'deposited' => 'info',
                            'cleared' => 'success',
                            'rejected', 'bounced', 'cancelled' => 'danger',
                            default => 'neutral',
                        };
                    @endphp
                    <tr>
                        <td>
                            <div class="admin-product-name">
                                {{ $cheque->order?->user?->name ?: $cheque->order?->customer_name ?: 'بدون نام' }}
                            </div>
                            <div class="admin-muted" dir="ltr">
                                {{ $cheque->order?->order_number ?: '—' }}
                            </div>
                            @if($cheque->order?->user?->phone)
                                <div class="admin-muted" dir="ltr">{{ $cheque->order->user->phone }}</div>
                            @endif
                        </td>

                        <td>
                            <div class="admin-product-name">{{ $cheque->bank_name }}</div>
                            <div class="admin-muted" dir="ltr">صیادی: {{ $cheque->sayad_id }}</div>
                            @if($cheque->cheque_number)
                                <div class="admin-muted" dir="ltr">شماره: {{ $cheque->cheque_number }}</div>
                            @endif
                        </td>

                        <td>
                            <div class="admin-price">{{ number_format((float) $cheque->amount) }}</div>
                            <div class="admin-muted">تومان</div>
                        </td>

                        <td>
                            <span class="admin-muted">
                                {{ $cheque->due_date?->format('Y/m/d') ?: '—' }}
                            </span>
                        </td>

                        <td>
                            <span class="admin-badge admin-badge--{{ $statusClass }}">
                                {{ $statusNames[$status] ?? $status }}
                            </span>
                            @if($cheque->reviewedBy)
                                <div class="admin-muted" style="margin-top:5px;">
                                    بررسی توسط {{ $cheque->reviewedBy->name ?: 'مدیر' }}
                                </div>
                            @endif
                        </td>

                        <td>
                            <div class="admin-actions">
                                @if($status === 'submitted')
                                    <form method="POST" action="{{ route('admin.cheques.review', $cheque) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button class="admin-btn admin-btn--secondary admin-btn--sm" type="submit">شروع بررسی</button>
                                    </form>
                                @elseif($status === 'under_review')
                                    <form method="POST" action="{{ route('admin.cheques.accept', $cheque) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button class="admin-btn admin-btn--secondary admin-btn--sm" type="submit">تأیید چک</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.cheques.reject', $cheque) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button class="admin-btn admin-btn--ghost admin-btn--sm" type="submit">رد</button>
                                    </form>
                                @elseif($status === 'accepted')
                                    <form method="POST" action="{{ route('admin.cheques.deposit', $cheque) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button class="admin-btn admin-btn--secondary admin-btn--sm" type="submit">ثبت واریز</button>
                                    </form>
                                @elseif($status === 'deposited')
                                    <form method="POST" action="{{ route('admin.cheques.clear', $cheque) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button class="admin-btn admin-btn--secondary admin-btn--sm" type="submit">ثبت تسویه</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.cheques.bounce', $cheque) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button class="admin-btn admin-btn--ghost admin-btn--sm" type="submit">برگشتی</button>
                                    </form>
                                @else
                                    <span class="admin-muted">عملیات دیگری لازم نیست.</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        @if($cheques->hasPages())
            <div class="admin-pagination">
                {{ $cheques->links() }}
            </div>
        @endif
    @else
        <div class="admin-empty">
            <div class="admin-empty__icon">—</div>
            <h3 class="admin-empty__title">چکی پیدا نشد</h3>
            <p class="admin-empty__text">برای فیلتر فعلی موردی برای نمایش وجود ندارد.</p>
        </div>
    @endif
</div>
@endsection
