@extends('layouts.admin')

@section('title', 'همگام‌سازی نیلا')
@section('page-title', 'همگام‌سازی نیلا')

@section('content')
<div class="admin-page-head">
    <div>
        <h1 class="admin-page-head__title">ورود ماهانه اطلاعات نیلا</h1>
        <p class="admin-page-head__text">فایل خروجی را بگیر، اینجا بارگذاری کن و قبل از اعمال تغییرات گزارش را بررسی کن.</p>
    </div>
</div>

<div class="admin-dashboard-stats">
    <div class="admin-stat-card">
        <div class="admin-stat-card__label">محصولات شناسایی‌شده</div>
        <div class="admin-stat-card__value">{{ number_format($products) }}</div>
        <div class="admin-stat-card__meta">با کد اتصال نیلا</div>
    </div>
    <div class="admin-stat-card">
        <div class="admin-stat-card__label">تنوع‌های شناسایی‌شده</div>
        <div class="admin-stat-card__value">{{ number_format($variants) }}</div>
        <div class="admin-stat-card__meta">SKUهای متصل به نیلا</div>
    </div>
    <div class="admin-stat-card">
        <div class="admin-stat-card__label">آخرین همگام‌سازی</div>
        <div class="admin-stat-card__value" style="font-size:1.1rem;">
            {{ $latestSyncAt?->format('Y/m/d H:i') ?? 'هنوز انجام نشده' }}
        </div>
        <div class="admin-stat-card__meta">آخرین به‌روزرسانی اتصال‌ها</div>
    </div>
</div>

<div class="admin-card nila-panel nila-panel--upload">
    <div class="admin-card-header">
        <div>
            <span class="nila-step-label">شروع کار</span>
            <h2 class="admin-card-title">فایل ماه جدید را بارگذاری کن</h2>
            <p class="admin-card-description">از نیلا خروجی اکسل بگیر و فایل را همین‌جا انتخاب کن. هنوز هیچ داده‌ای تغییر نمی‌کند.</p>
        </div>
    </div>

    <div class="nila-info-grid" aria-label="مراحل همگام‌سازی">
        <div class="nila-info-card">
            <strong>۱. خروجی از نیلا</strong>
            <p class="admin-muted">فایل اکسل محصولات و تنوع‌ها را از نیلا دریافت کن.</p>
        </div>
        <div class="nila-info-card">
            <strong>۲. بررسی در جانان</strong>
            <p class="admin-muted">جانان موارد جدید، تکراری، تغییر قیمت و اختلاف موجودی را نشان می‌دهد.</p>
        </div>
        <div class="nila-info-card">
            <strong>۳. تأیید و ثبت</strong>
            <p class="admin-muted">بعد از تأیید تو، اطلاعات شناسایی‌شده به‌صورت مرحله‌ای اعمال می‌شوند.</p>
        </div>
    </div>

    <form class="nila-upload-form" method="POST" action="{{ route('admin.nila.imports.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="admin-field nila-file-field">
            <label for="nila-import-file">فایل خروجی نیلا</label>
            <input
                id="nila-import-file"
                type="file"
                name="file"
                accept=".xlsx,.csv,.txt"
                required
            >
            <p class="admin-muted nila-file-hint">فرمت قابل قبول: XLSX یا CSV · حداکثر ۱۰ مگابایت</p>
            @error('file')
                <div class="alert error">{{ $message }}</div>
            @enderror
        </div>
        <div class="admin-form-actions nila-upload-actions">
            <span class="nila-safe-note">بارگذاری فایل فقط گزارش می‌سازد؛ برای اعمال تغییرات باید مرحله بعد را تأیید کنی.</span>
            <button type="submit" class="admin-btn admin-btn--secondary">بررسی فایل</button>
        </div>
    </form>
</div>

<div class="admin-card nila-panel nila-panel--ownership">
    <div class="admin-card-header">
        <div>
            <span class="nila-step-label">قواعد مهم</span>
            <h2 class="admin-card-title">جانان چه چیزی را تغییر می‌دهد؟</h2>
        </div>
    </div>
    <div class="nila-info-grid">
        <div class="nila-info-card">
            <strong>محصول و تنوع تکراری</strong>
            <p class="admin-muted">با کد ثابت نیلا شناسایی می‌شود؛ رکورد تکراری ساخته نمی‌شود.</p>
        </div>
        <div class="nila-info-card">
            <strong>موجودی</strong>
            <p class="admin-muted">عدد فایل، موجودی نهایی نیلاست. اختلاف آن با موجودی جانان به‌عنوان گردش اصلاحی ثبت می‌شود؛ به موجودی قبلی اضافه نمی‌شود.</p>
        </div>
        <div class="nila-info-card">
            <strong>اطلاعات مخصوص جانان</strong>
            <p class="admin-muted">عکس، توضیحات، برند، دسته‌بندی و وضعیت انتشار دستی جانان با فایل نیلا بازنویسی نمی‌شوند.</p>
        </div>
    </div>
</div>

<div class="admin-card nila-panel nila-panel--history">
    <div class="admin-card-header">
        <div>
            <span class="nila-step-label">سوابق</span>
            <h2 class="admin-card-title">فایل‌های بررسی‌شده</h2>
            <p class="admin-card-description">برای دیدن گزارش هر فایل، روی «مشاهده گزارش» بزن.</p>
        </div>
    </div>

    @if($batches->count() > 0)
        <div class="admin-table-wrap">
            <table class="admin-table nila-table">
                <thead>
                <tr>
                    <th>فایل</th>
                    <th>ردیف‌ها</th>
                    <th>معتبر</th>
                    <th>نیازمند اصلاح</th>
                    <th>محصول‌ها</th>
                    <th>ثبت موفق</th>
                    <th>ناموفق</th>
                    <th>وضعیت</th>
                    <th>گزارش</th>
                </tr>
                </thead>
                <tbody>
                @foreach($batches as $batch)
                    <tr>
                        <td>
                            <strong>{{ $batch->original_name }}</strong>
                            <small class="admin-muted">{{ $batch->created_at?->format('Y/m/d H:i') }}</small>
                        </td>
                        <td>{{ number_format($batch->total_rows) }}</td>
                        <td>{{ number_format($batch->valid_rows) }}</td>
                        <td>{{ number_format($batch->invalid_rows) }}</td>
                        <td>{{ number_format($batch->product_groups) }}</td>
                        <td>{{ number_format($batch->imported_groups) }}</td>
                        <td>{{ number_format($batch->failed_groups) }}</td>
                        <td>
                            @if($batch->status === 'completed')
                                تکمیل‌شده
                            @elseif($batch->status === 'queued')
                                در صف
                            @elseif($batch->status === 'processing')
                                در حال اجرا
                            @elseif($batch->status === 'ready')
                                آماده ادامه
                            @else
                                آماده بررسی
                            @endif
                        </td>
                        <td><a href="{{ route('admin.nila.imports.show', $batch) }}">مشاهده گزارش</a></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @if($batches->hasPages())
            <div>{{ $batches->links() }}</div>
        @endif
    @else
        <div class="admin-empty">
            <h3 class="admin-empty__title">هنوز فایلی بارگذاری نشده</h3>
            <p class="admin-empty__text">اولین خروجی ماهانه نیلا را از بخش بالا وارد کن.</p>
        </div>
    @endif
</div>
@endsection
