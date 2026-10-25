@extends('layouts.admin')

@section('title', 'Nila / Holoo')
@section('page-title', 'Nila / Holoo')

@section('content')
<div class="admin-page-head">
    <div>
        <h1 class="admin-page-head__title">مرکز کنترل نیلا / Holoo</h1>
        <p class="admin-page-head__text">
            این صفحه وضعیت نگاشت‌های ذخیره‌شده را نشان می‌دهد. دریافت زنده از سرویس Holoo از طریق دستور Artisan انجام می‌شود، نه از این صفحه.
        </p>
    </div>
</div>

<div class="admin-dashboard-stats">
    <div class="admin-stat-card">
        <div class="admin-stat-card__label">محصولات Mapping شده</div>
        <div class="admin-stat-card__value">{{ number_format($products) }}</div>
        <div class="admin-stat-card__meta">Product</div>
    </div>
    <div class="admin-stat-card">
        <div class="admin-stat-card__label">واریانت‌های Mapping شده</div>
        <div class="admin-stat-card__value">{{ number_format($variants) }}</div>
        <div class="admin-stat-card__meta">Product Variant</div>
    </div>
    <div class="admin-stat-card">
        <div class="admin-stat-card__label">آخرین تغییر Mapping</div>
        <div class="admin-stat-card__value" style="font-size:1.15rem;">
            {{ $latestSyncAt?->format('Y/m/d H:i') ?? '—' }}
        </div>
        <div class="admin-stat-card__meta">زمان سرور</div>
    </div>
</div>

<div class="admin-card">
    <div class="admin-card-header">
        <div>
            <h2 class="admin-card-title">مرز مالکیت داده</h2>
            <p class="admin-card-description">
                Holoo منبع کاتالوگ، کد کالا، قیمت و موجودی است؛ Janan مالک محتوای فروشگاهی، وضعیت انتشار و Media است.
            </p>
            <p class="admin-card-description">
                اتصال HTTP و دستور دریافت داده آماده‌اند، اما قبل از اولین import باید NILA_* در فایل env تنظیم و واحد قیمت با داده واقعی Holoo تطبیق داده شود. ابتدا یک پیش‌نمایش بدون نوشتن در دیتابیس اجرا کن.
            </p>
            <p class="admin-card-description"><code>php artisan nila:sync-products --dry-run --limit=10</code></p>
        </div>
    </div>

    <div class="admin-form-grid">
        <div class="admin-card">
            <strong>Nila / Holoo</strong>
            <p class="admin-muted">نام کالا، شناسه ERP، کد/SKU، قیمت انتخابی و موجودی از سرویس خوانده می‌شوند. گروه‌های اصلی و فرعی در زمان import واقعی به دسته‌بندی‌های دارای Mapping تبدیل می‌شوند.</p>
        </div>
        <div class="admin-card">
            <strong>Janan</strong>
            <p class="admin-muted">تصاویر، ویدئو، ترتیب گالری، Alt، توضیحات فروشگاهی و وضعیت انتشار در جانان مدیریت می‌شوند. محصولات تازه‌وارد به‌طور پیش‌فرض غیرفعال هستند تا بعد از بررسی قیمت و بارگذاری تصویر منتشر شوند.</p>
        </div>
    </div>
</div>

<div class="admin-card">
    <div class="admin-card-header">
        <div>
            <h2 class="admin-card-title">آخرین Mappingها</h2>
            <p class="admin-card-description">این جدول وضعیت داده‌های نگاشت‌شده در دیتابیس را نشان می‌دهد، نه وضعیت اتصال زنده به API.</p>
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
            <p class="admin-empty__text">بعد از اجرای import آزمایشی و سپس واردسازی واقعی، نگاشت شناسه‌های Holoo و رکوردهای جانان اینجا نمایش داده می‌شود.</p>
        </div>
    @endif
</div>
@endsection
