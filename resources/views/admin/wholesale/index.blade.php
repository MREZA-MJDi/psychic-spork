@extends('layouts.admin')

@section('title', 'شرایط خرید عمده')
@section('page-title', 'شرایط خرید عمده')

@section('content')
@php
    $number = [\App\Support\PersianNumber::class, 'format'];
    $statusLabels = ['pending' => 'در انتظار', 'approved' => 'تأیید شده', 'suspended' => 'تعلیق شده', 'rejected' => 'رد شده'];
    $listUrl = route('admin.wholesale.index', request()->only('q', 'status', 'page'));
@endphp
<div class="wholesale-admin">
    <header class="wholesale-admin__head">
        <div>
            <h1>شرایط خرید عمده</h1>
            <p>حداقل مبلغ و تعداد سفارش را برای حساب‌های عمده دارای پرونده تنظیم کنید.</p>
        </div>
        <a href="{{ route('admin.wholesale-packs.index') }}" class="admin-btn admin-btn--secondary">مدیریت پک‌های عمده</a>
    </header>

    <p class="wholesale-admin__hint">خرید عمده آنلاین برای همه مشتریان باز است. این تنظیمات فقط حداقل سفارش حساب‌های دارای پرونده را تعیین می‌کند. <a href="{{ route('admin.cheques.index', ['tab' => 'requests']) }}">مدیریت مجوز چک</a> جداگانه انجام می‌شود.</p>

    @if($editing)
        <section id="wholesale-editor" class="wholesale-admin__editor" aria-labelledby="wholesale-editor-title">
            <header class="wholesale-admin__section-head">
                <div>
                    <h2 id="wholesale-editor-title">ویرایش شرایط {{ $editing->user?->name ?: 'مشتری' }}</h2>
                    <p>{{ $editing->business_name ?: 'حساب مشتری' }}</p>
                </div>
                <a class="admin-btn admin-btn--ghost admin-btn--sm" href="{{ $listUrl }}">بستن</a>
            </header>
            <form method="POST" action="{{ route('admin.customers.wholesale.terms', $editing->user) }}" class="wholesale-admin__form">
                @csrf @method('PATCH')
                <div class="admin-field">
                    <label for="minimum_order_amount">حداقل مبلغ سفارش (تومان)</label>
                    <input id="minimum_order_amount" type="text" name="minimum_order_amount" inputmode="decimal" data-money-input value="{{ old('minimum_order_amount', $editing->minimum_order_amount) }}" placeholder="بدون محدودیت">
                    <small>برای حذف محدودیت، خالی بگذارید.</small>
                </div>
                <div class="admin-field">
                    <label for="minimum_order_quantity">حداقل تعداد اقلام سفارش</label>
                    <input id="minimum_order_quantity" type="number" name="minimum_order_quantity" min="1" step="1" value="{{ old('minimum_order_quantity', $editing->minimum_order_quantity) }}" placeholder="بدون محدودیت">
                </div>
                <div class="admin-field wholesale-admin__note">
                    <label for="note">یادداشت داخلی مدیریت</label>
                    <textarea id="note" name="note" rows="2" maxlength="2000" placeholder="اختیاری">{{ old('note', $editing->admin_note) }}</textarea>
                </div>
                <div class="wholesale-admin__save">
                    <button type="submit" class="admin-btn admin-btn--secondary">ذخیره شرایط</button>
                    <a class="admin-btn admin-btn--ghost" href="{{ $listUrl }}">بازگشت به فهرست</a>
                </div>
            </form>
            <details class="wholesale-admin__legacy">
                <summary>وضعیت پرونده: {{ $statusLabels[$editing->status] ?? $editing->status }}</summary>
                <p>این وضعیت برای مدیریت پرونده است و خرید عمده آنلاین یا مجوز چک مشتری را مسدود نمی‌کند.</p>
                <div class="wholesale-admin__actions">
                    @if(in_array($editing->status, ['pending', 'rejected', 'suspended'], true))
                        <form method="POST" action="{{ route('admin.customers.wholesale.approve', $editing->user) }}">@csrf @method('PATCH')<button class="admin-btn admin-btn--secondary admin-btn--sm" type="submit">تأیید پرونده</button></form>
                    @endif
                    @if($editing->status === 'pending')
                        <form method="POST" action="{{ route('admin.customers.wholesale.reject', $editing->user) }}">@csrf @method('PATCH')<button class="admin-btn admin-btn--ghost admin-btn--sm" type="submit">رد پرونده</button></form>
                    @elseif($editing->status === 'approved')
                        <form method="POST" action="{{ route('admin.customers.wholesale.suspend', $editing->user) }}">@csrf @method('PATCH')<button class="admin-btn admin-btn--ghost admin-btn--sm" type="submit">تعلیق پرونده</button></form>
                    @endif
                </div>
            </details>
        </section>
    @endif

    <section class="wholesale-admin__list" aria-labelledby="wholesale-list-title">
        <header class="wholesale-admin__section-head">
            <div>
                <h2 id="wholesale-list-title">حساب‌های دارای پرونده عمده</h2>
                <p>{{ $number($profiles->total()) }} حساب مطابق فیلتر</p>
            </div>
        </header>
        <form method="GET" action="{{ route('admin.wholesale.index') }}" class="wholesale-admin__filters">
            <div class="admin-field">
                <label for="q">جستجوی مشتری یا فروشگاه</label>
                <input id="q" type="search" name="q" value="{{ request('q') }}" maxlength="120" placeholder="نام یا شماره تماس">
            </div>
            <div class="admin-field">
                <label for="status">وضعیت پرونده</label>
                <select id="status" name="status">
                    <option value="">همه</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ $statusLabels[$status] }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="admin-btn admin-btn--secondary">جستجو</button>
            <a href="{{ route('admin.wholesale.index') }}" class="admin-btn admin-btn--ghost">پاک کردن</a>
        </form>
        <div class="wholesale-admin__table-wrap">
            <table class="wholesale-admin__table">
                <thead><tr><th scope="col">مشتری / کسب‌وکار</th><th scope="col">حداقل مبلغ</th><th scope="col">حداقل تعداد</th><th scope="col">وضعیت پرونده</th><th scope="col">عملیات</th></tr></thead>
                <tbody>
                @forelse($profiles as $profile)
                    <tr @class(['wholesale-admin__selected' => $editing?->id === $profile->id])>
                        <td data-label="مشتری"><strong>{{ $profile->user?->name ?: 'بدون نام' }}</strong>
                            <small>{{ $profile->business_name ?: '—' }}</small>
                            <small dir="ltr">{{ $profile->user?->phone ?: $profile->user?->email ?: '—' }}</small>
                        </td>
                        <td data-label="حداقل مبلغ">{{ $profile->minimum_order_amount !== null ? $number((float) $profile->minimum_order_amount) . ' تومان' : 'بدون محدودیت' }}</td>
                        <td data-label="حداقل تعداد">{{ $profile->minimum_order_quantity !== null ? $number($profile->minimum_order_quantity) . ' عدد' : 'بدون محدودیت' }}</td>
                        <td data-label="وضعیت پرونده"><span @class(['wholesale-admin__status', 'wholesale-admin__status--approved' => $profile->status === 'approved', 'wholesale-admin__status--pending' => $profile->status === 'pending'])>{{ $statusLabels[$profile->status] ?? $profile->status }}</span></td>
                        <td data-label="عملیات"><a class="admin-btn admin-btn--ghost admin-btn--sm" href="{{ route('admin.wholesale.index', array_merge(request()->only('q', 'status', 'page'), ['edit' => $profile->id])) }}#wholesale-editor">ویرایش شرایط</a></td>
                    </tr>
                @empty
                    <tr class="wholesale-admin__empty"><td colspan="5">حساب عمده‌ای مطابق فیلتر پیدا نشد.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($profiles->hasPages())<div class="admin-pagination">{{ $profiles->links() }}</div>@endif
    </section>
</div>
@endsection
