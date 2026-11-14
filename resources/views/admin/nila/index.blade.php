@extends('layouts.admin')

@section('title', 'Nila / Holoo')
@section('page-title', 'Nila / Holoo')

@section('content')
<div class="admin-page-head">
    <div>
        <h1 class="admin-page-head__title">مرکز کنترل نیلا / Holoo</h1>
        <p class="admin-page-head__text">
            دریافت امن فایل کاتالوگ، بررسی ساختار و آماده‌سازی ورود مرحله‌ای محصولات.
        </p>
    </div>
</div>

<div class="admin-dashboard-stats">
    <div class="admin-stat-card">
        <div class="admin-stat-card__label">محصولات متصل به نیلا</div>
        <div class="admin-stat-card__value">{{ number_format($products) }}</div>
        <div class="admin-stat-card__meta">Product mappings</div>
    </div>
    <div class="admin-stat-card">
        <div class="admin-stat-card__label">تنوع‌های متصل به نیلا</div>
        <div class="admin-stat-card__value">{{ number_format($variants) }}</div>
        <div class="admin-stat-card__meta">Variant mappings</div>
    </div>
    <div class="admin-stat-card">
        <div class="admin-stat-card__label">آخرین تغییر اتصال</div>
        <div class="admin-stat-card__value" style="font-size:1.15rem;">
            {{ $latestSyncAt?->format('Y/m/d H:i') ?? '—' }}
        </div>
        <div class="admin-stat-card__meta">زمان سرور</div>
    </div>
</div>

<div class="admin-card">
    <div class="admin-card-header">
        <div>
            <h2 class="admin-card-title">۱. بررسی فایل اکسل هولو</h2>
            <p class="admin-card-description">
                فایل در فضای خصوصی سرور ذخیره می‌شود. در این مرحله فقط ساختار و داده‌ها خوانده می‌شوند و هیچ محصول، تنوع، دسته یا برندی در کاتالوگ ساخته یا ویرایش نمی‌شود.
            </p>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.nila.imports.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="admin-form-grid">
            <div class="admin-field">
                <label for="nila-import-file">فایل خروجی نیلا / هلو</label>
                <input
                    id="nila-import-file"
                    type="file"
                    name="file"
                    accept=".xlsx,.csv,.txt"
                    required
                >
                <p class="admin-muted">فرمت مجاز: XLSX یا CSV، حداکثر ۱۰ مگابایت. فقط شیت اول فایل XLSX بررسی می‌شود.</p>
                @error('file')
                    <div class="alert error">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="admin-form-actions">
            <button type="submit" class="admin-btn admin-btn--secondary">بارگذاری و بررسی فایل</button>
        </div>
    </form>
</div>

<div class="admin-card">
    <div class="admin-card-header">
        <div>
            <h2 class="admin-card-title">۲. تاریخچه بررسی فایل‌ها</h2>
            <p class="admin-card-description">هر فایل یک شناسه مستقل دارد تا بررسی‌ها قابل پیگیری باشند.</p>
        </div>
    </div>

    @if($batches->count() > 0)
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                <tr>
                    <th>نام فایل</th>
                    <th>ردیف‌ها</th>
                    <th>قابل بررسی</th>
                    <th>دارای خطا</th>
                    <th>گروه محصول</th>
                    <th>ثبت‌شده</th>
                    <th>ناموفق</th>
                    <th>وضعیت</th>
                    <th>تاریخ</th>
                    <th>جزئیات</th>
                </tr>
                </thead>
                <tbody>
                @foreach($batches as $batch)
                    <tr>
                        <td>{{ $batch->original_name }}</td>
                        <td>{{ number_format($batch->total_rows) }}</td>
                        <td>{{ number_format($batch->valid_rows) }}</td>
                        <td>{{ number_format($batch->invalid_rows) }}</td>
                        <td>{{ number_format($batch->product_groups) }}</td>
                        <td>{{ number_format($batch->imported_groups) }}</td>
                        <td>{{ number_format($batch->failed_groups) }}</td>
                        <td>
                            @if($batch->status === 'completed')
                                تکمیل‌شده
                            @elseif($batch->status === 'processing')
                                در حال پردازش
                            @elseif($batch->status === 'ready')
                                آماده ادامه
                            @else
                                آماده شروع
                            @endif
                        </td>
                        <td>{{ $batch->created_at?->format('Y/m/d H:i') }}</td>
                        <td>
                            <a href="{{ route('admin.nila.imports.show', $batch) }}">مشاهده گزارش</a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div>{{ $batches->links() }}</div>
    @else
        <div class="admin-empty">
            <h3 class="admin-empty__title">هنوز فایلی بررسی نشده است</h3>
            <p class="admin-empty__text">بعد از بارگذاری اولین فایل، نتیجه بررسی در همین بخش ثبت می‌شود.</p>
        </div>
    @endif
</div>

<div class="admin-card">
    <div class="admin-card-header">
        <div>
            <h2 class="admin-card-title">مرز مالکیت اطلاعات</h2>
            <p class="admin-card-description">
                اطلاعات کاتالوگ و موجودی از نیلا / هلو می‌آیند؛ تصاویر، محتوای نمایشی، توضیحات تکمیلی و انتشار محصول در جانان مدیریت می‌شوند.
            </p>
        </div>
    </div>
    <div class="admin-form-grid">
        <div class="admin-card">
            <strong>ساختار کاتالوگ</strong>
            <p class="admin-muted">ورود نهایی فقط از جدول‌های فعلی products و product_variants استفاده می‌کند. کد کامل هولو شناسه تنوع و SKU خواهد بود و کد پایه برای گروه‌بندی محصول به کار می‌رود.</p>
        </div>
        <div class="admin-card">
            <strong>اطلاعاتی که از فایل فعلی قابل استخراج نیست</strong>
            <p class="admin-muted">فایل فعلی ستون دسته‌بندی و برند ندارد؛ بنابراین هیچ دسته یا برندی از روی حدس ساخته نمی‌شود. holooCustomerCode نیز فعلاً فقط به‌عنوان داده مرجع نگهداری می‌شود.</p>
        </div>
    </div>
</div>

<div class="admin-card">
    <div class="admin-card-header">
        <div>
            <h2 class="admin-card-title">آخرین Mappingهای ثبت‌شده</h2>
            <p class="admin-card-description">این جدول ارتباط شناسه‌های خارجی با رکوردهای فعلی کاتالوگ را نشان می‌دهد.</p>
        </div>
    </div>

    @if($mappings->isNotEmpty())
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                <tr>
                    <th>نوع</th>
                    <th>ID داخلی</th>
                    <th>ID خارجی</th>
                    <th>SKU خارجی</th>
                    <th>آخرین تغییر</th>
                </tr>
                </thead>
                <tbody>
                @foreach($mappings as $mapping)
                    <tr>
                        <td>{{ class_basename($mapping->entity_type) }}</td>
                        <td>{{ $mapping->entity_id }}</td>
                        <td>{{ $mapping->external_id }}</td>
                        <td>{{ $mapping->external_sku ?: '—' }}</td>
                        <td>{{ $mapping->updated_at?->format('Y/m/d H:i') ?? '—' }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="admin-empty">
            <h3 class="admin-empty__title">هنوز Mapping ثبت نشده</h3>
            <p class="admin-empty__text">پس از اجرای Import تأییدشده، ارتباط محصول و تنوع با شناسه‌های هولو اینجا نمایش داده می‌شود.</p>
        </div>
    @endif
</div>
@endsection
