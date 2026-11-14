@extends('layouts.admin')

@section('title', 'گزارش فایل هولو')
@section('page-title', 'گزارش بررسی فایل هولو')

@section('content')
@php
    $pilotProgress = $batch->product_groups > 0
        ? min(100, (int) round(($batch->next_group_offset / $batch->product_groups) * 100))
        : 0;
@endphp
<div class="admin-page-head nila-preview-head">
    <div>
        <span class="nila-step-label">گزارش و کنترل پایلوت</span>
        <h1 class="admin-page-head__title">گزارش بررسی فایل</h1>
        <p class="admin-page-head__text">{{ $batch->original_name }}</p>
    </div>
    <a class="admin-btn admin-btn--ghost" href="{{ route('admin.nila.index') }}">بازگشت به نیلا</a>
</div>

<div class="admin-dashboard-stats">
    <div class="admin-stat-card">
        <div class="admin-stat-card__label">کل ردیف‌ها</div>
        <div class="admin-stat-card__value">{{ number_format($batch->total_rows) }}</div>
    </div>
    <div class="admin-stat-card">
        <div class="admin-stat-card__label">ردیف‌های معتبر</div>
        <div class="admin-stat-card__value">{{ number_format($batch->valid_rows) }}</div>
    </div>
    <div class="admin-stat-card">
        <div class="admin-stat-card__label">ردیف‌های دارای خطا</div>
        <div class="admin-stat-card__value">{{ number_format($batch->invalid_rows) }}</div>
    </div>
    <div class="admin-stat-card">
        <div class="admin-stat-card__label">گروه محصول</div>
        <div class="admin-stat-card__value">{{ number_format($batch->product_groups) }}</div>
    </div>
</div>

<div class="admin-card nila-panel nila-panel--progress">
    <div class="nila-progress-heading">
        <div>
            <span class="nila-step-label">مرحله ۳ · اجرای کنترل‌شده</span>
            <h2 class="admin-card-title">وضعیت اجرای Import</h2>
        </div>
        <strong class="nila-progress-value">{{ number_format($pilotProgress) }}٪</strong>
    </div>
    <div class="nila-progress-track" role="progressbar" aria-label="پیشرفت Import" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $pilotProgress }}">
        <span style="width: {{ $pilotProgress }}%"></span>
    </div>
    <p class="admin-card-description">
        وضعیت: {{ $batch->status === 'completed' ? 'تکمیل‌شده' : ($batch->status === 'processing' ? 'در حال پردازش' : ($batch->status === 'ready' ? 'آماده ادامه' : 'آماده شروع')) }}
        — پیشرفت گروه‌ها: {{ number_format($batch->next_group_offset) }} از {{ number_format($batch->product_groups) }}
        — ثبت موفق: {{ number_format($batch->imported_groups) }}
        — کنار گذاشته‌شده: {{ number_format($batch->failed_groups) }}
    </p>

    @if($batch->status === 'completed')
        <div class="alert success">پردازش این فایل به پایان رسیده است. برای اصلاح داده‌های نامعتبر، فایل اصلاح‌شده را دوباره بارگذاری کنید.</div>
    @elseif($batch->status === 'processing')
        <div class="alert">این فایل در حال پردازش است. از اجرای هم‌زمان درخواست دیگر خودداری کنید.</div>
    @else
        <form method="POST" action="{{ route('admin.nila.imports.import', $batch) }}">
            @csrf
            <div class="admin-form-grid">
                <div class="admin-field">
                    <label for="batch_size">تعداد محصول اصلی در این مرحله</label>
                    <select id="batch_size" name="batch_size">
                        <option value="5" selected>۵ گروه محصول (پایلوت اولیه)</option>
                    </select>
                    <small class="admin-help">در این مرحله فقط ۵ گروه اول وارد می‌شوند. هر گروه شامل تمام تنوع‌های همان کد پایه است. قبل از هر Import بزرگ‌تر باید نتیجه این پایلوت بررسی و تأیید شود.</small>
                </div>
            </div>
            <div class="admin-form-actions nila-pilot-actions">
                <span class="nila-safe-note">محصولات جدید غیرفعال می‌مانند و دسته/برند به‌صورت حدسی ساخته نمی‌شود.</span>
                <button type="submit" class="admin-btn admin-btn--secondary">
                    {{ $batch->next_group_offset > 0 ? 'ادامه Import' : 'شروع Import مرحله‌ای' }}
                </button>
            </div>
        </form>
    @endif
</div>

<div class="admin-card nila-panel nila-panel--groups">
    <span class="nila-step-label">مرحله ۲ · کنترل گروه‌ها</span>
    <h2 class="admin-card-title">گروه‌بندی محصولات</h2>
    <p class="admin-card-description">کد قبل از ستاره، گروه محصول را مشخص می‌کند. کد کامل هر ردیف برای SKU تنوع نگهداری می‌شود.</p>
    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
            <tr>
                <th>کد پایه</th>
                <th>نام پیشنهادی محصول</th>
                <th>تعداد تنوع</th>
                <th>ردیف معتبر</th>
                <th>ردیف دارای خطا</th>
                <th>کنترل نام</th>
            </tr>
            </thead>
            <tbody>
            @forelse($groups as $group)
                <tr>
                    <td>{{ $group['product_code'] }}</td>
                    <td>{{ $group['product_name_candidate'] }}</td>
                    <td>{{ number_format($group['rows']) }}</td>
                    <td>{{ number_format($group['valid_rows']) }}</td>
                    <td>{{ number_format($group['invalid_rows']) }}</td>
                    <td>{{ $group['name_conflict'] ? 'تعارض نام؛ نیازمند بررسی' : 'یکسان' }}</td>
                </tr>
            @empty
                <tr><td colspan="6">ردیفی برای نمایش وجود ندارد.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="admin-card nila-panel nila-panel--rows">
    <span class="nila-step-label">جزئیات داده‌های ورودی</span>
    <h2 class="admin-card-title">نمونه ردیف‌های فایل</h2>
    <p class="admin-card-description">حداکثر ۱۰۰ ردیف اول نمایش داده می‌شود. قیمت خالی خطا محسوب می‌شود و هرگز به صفر تبدیل نمی‌شود.</p>
    @if($batch->imported_groups > 0)
        <p class="admin-muted">نتیجه Import در این گزارش نگهداری می‌شود و گروه‌های موفق در جدول‌های اصلی کاتالوگ ثبت شده‌اند.</p>
    @elseif($batch->failed_groups > 0)
        <p class="admin-muted">گروه‌ها بررسی شدند اما هیچ محصولی ثبت نشد؛ ردیف‌های دارای خطا را اصلاح و فایل را دوباره بارگذاری کنید.</p>
    @else
        <p class="admin-muted">وضعیت فعلی: فقط پیش‌نمایش؛ هنوز هیچ محصولی وارد دیتابیس نشده است.</p>
    @endif
    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
            <tr>
                <th>ردیف اکسل</th>
                <th>کد کامل هولو / SKU</th>
                <th>نام اصلی در فایل</th>
                <th>رنگ احتمالی</th>
                <th>قیمت</th>
                <th>موجودی</th>
                <th>نتیجه بررسی</th>
            </tr>
            </thead>
            <tbody>
            @forelse($rows as $row)
                <tr>
                    <td>{{ $row['row_number'] }}</td>
                    <td>{{ $row['holoo_code'] }}</td>
                    <td>{{ $row['holoo_name'] }}</td>
                    <td>{{ $row['color_candidate'] ?: '—' }}</td>
                    <td>{{ $row['price'] === null ? 'خالی / نامعتبر' : number_format($row['price']) }}</td>
                    <td>{{ $row['stock'] === null ? 'نامعتبر' : number_format($row['stock']) }}</td>
                    <td>
                        @if($row['is_valid'])
                            معتبر
                        @else
                            <ul>
                                @foreach($row['errors'] as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="7">ردیفی برای نمایش وجود ندارد.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($hasMoreRows)
        <p class="admin-muted">برای جلوگیری از بارگذاری سنگین، فقط ۱۰۰ ردیف اول در این گزارش نمایش داده شده است.</p>
    @endif
</div>

<div class="admin-card nila-panel nila-panel--rules">
    <h2 class="admin-card-title">محدودیت‌های این مرحله</h2>
    <ul>
        <li>این گزارش فقط پیش‌نمایش است و هیچ تغییری در محصولات، تنوع‌ها، موجودی یا قیمت‌ها ایجاد نکرده است.</li>
        <li>رنگ فقط به‌عنوان پیشنهاد استخراج‌شده از بخش بعد از آخرین خط تیره نمایش داده می‌شود و هنوز ذخیره نمی‌شود.</li>
        <li>دسته‌بندی و برند از فایل فعلی قابل تعیین نیستند و باید با داده‌های تأییدشده جانان تطبیق داده شوند.</li>
        <li>ردیف بدون قیمت قابل Import نیست؛ مقدار خالی به قیمت صفر تبدیل نمی‌شود.</li>
    </ul>
@if(!empty($failedProducts))
<div class="admin-card">
    <h2 class="admin-card-title">گروه‌های ناموفق در Import</h2>
    <p class="admin-card-description">برای جلوگیری از ثبت ناقص، هر گروهی که یکی از تنوع‌هایش خطا داشته باشد به‌صورت کامل کنار گذاشته می‌شود.</p>
    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead><tr><th>کد پایه هولو</th><th>دلیل</th></tr></thead>
            <tbody>
            @foreach($failedProducts as $failure)
                <tr>
                    <td>{{ $failure['product_code'] ?? '—' }}</td>
                    <td>{{ $failure['reason'] ?? 'نیازمند بررسی' }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

</div>
@endsection
