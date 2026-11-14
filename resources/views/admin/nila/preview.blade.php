@extends('layouts.admin')

@section('title', 'گزارش فایل هولو')
@section('page-title', 'گزارش بررسی فایل هولو')

@section('content')
<div class="admin-page-head">
    <div>
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

<div class="admin-card">
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

<div class="admin-card">
    <h2 class="admin-card-title">نمونه ردیف‌های فایل</h2>
    <p class="admin-card-description">حداکثر ۱۰۰ ردیف اول نمایش داده می‌شود. قیمت خالی خطا محسوب می‌شود و هرگز به صفر تبدیل نمی‌شود.</p>
    <p class="admin-muted">وضعیت فعلی: فقط پیش‌نمایش؛ هنوز هیچ محصولی وارد دیتابیس نشده است.</p>
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

<div class="admin-card">
    <h2 class="admin-card-title">محدودیت‌های این مرحله</h2>
    <ul>
        <li>این گزارش فقط پیش‌نمایش است و هیچ تغییری در محصولات، تنوع‌ها، موجودی یا قیمت‌ها ایجاد نکرده است.</li>
        <li>رنگ فقط به‌عنوان پیشنهاد استخراج‌شده از بخش بعد از آخرین خط تیره نمایش داده می‌شود و هنوز ذخیره نمی‌شود.</li>
        <li>دسته‌بندی و برند از فایل فعلی قابل تعیین نیستند و باید با داده‌های تأییدشده جانان تطبیق داده شوند.</li>
        <li>ردیف بدون قیمت قابل Import نیست؛ مقدار خالی به قیمت صفر تبدیل نمی‌شود.</li>
    </ul>
</div>
@endsection
