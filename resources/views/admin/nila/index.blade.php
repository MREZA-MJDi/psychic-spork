@extends('layouts.admin')

@section('title', 'Nila / Holoo')
@section('page-title', 'Nila / Holoo')

@section('content')
<div class="admin-page-head">
    <div>
        <h1 class="admin-page-head__title">مرکز کنترل نیلا / Holoo</h1>
        <p class="admin-page-head__text">
            مرکز کنترل Mapping و ورود داده؛ اتصال واقعی API فقط بعد از دریافت قرارداد رسمی Nila فعال می‌شود.
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
                Nila منبع داده‌های کاتالوگ است؛ Janan مالک Media و نحوه نمایش Store است.
            </p>
            <p class="admin-card-description">همگام‌سازی API بدون قرارداد واقعی اجرا نمی‌شود.</p>
        </div>
    </div>

    <div class="admin-form-grid">
        <div class="admin-card">
            <strong>Nila / Holoo</strong>
            <p class="admin-muted">نام، SKU، قیمت و موجودی فقط در محدوده‌ای که قرارداد واقعی Integration تأیید کند.</p>
        </div>
        <div class="admin-card">
            <strong>Janan</strong>
            <p class="admin-muted">تصاویر، ویدئو، ترتیب گالری، Alt، ارائه Store و تنظیمات تجربه کاربری.</p>
        </div>
    </div>
</div>

<div class="admin-card">
    <div class="admin-card-header">
        <div>
            <h2 class="admin-card-title">آخرین Mappingها</h2>
            <p class="admin-card-description">این جدول وضعیت Catalog داخلی را نشان می‌دهد، نه اتصال زنده به API.</p>
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
            <p class="admin-empty__text">Importer بعد از دریافت داده نرمال‌شده از Adapter، Mapping را ایجاد می‌کند.</p>
        </div>
    @endif
</div>
@endsection
