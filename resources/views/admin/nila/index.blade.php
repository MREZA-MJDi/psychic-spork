@extends('layouts.admin')

@section('title', 'اتصال نیلا')
@section('page-title', 'نیلا')

@section('content')
    <div class="admin-page-head">
        <div>
            <h1 class="admin-page-head__title">مرکز کنترل نیلا</h1>
            <p class="admin-page-head__text">
                وضعیت mapping و مرز مالکیت داده‌ها را از اینجا بررسی کن؛ هیچ همگام‌سازی API بدون قرارداد واقعی اجرا نمی‌شود.
            </p>
        </div>
    </div>

    <div class="admin-dashboard-stats">
        <div class="admin-stat-card">
            <div class="admin-stat-card__label">محصول‌های متصل</div>
            <div class="admin-stat-card__value">{{ number_format($productMappings) }}</div>
            <div class="admin-stat-card__meta">mapping نیلا</div>
        </div>

        <div class="admin-stat-card">
            <div class="admin-stat-card__label">واریانت‌های متصل</div>
            <div class="admin-stat-card__value">{{ number_format($variantMappings) }}</div>
            <div class="admin-stat-card__meta">mapping نیلا</div>
        </div>

        <div class="admin-stat-card admin-stat-card--success">
            <div class="admin-stat-card__label">آخرین تغییر mapping</div>
            <div class="admin-stat-card__value admin-stat-card__value--small">
                <span data-admin-date="{{ optional($lastMapping?->updated_at)->toIso8601String() }}" data-admin-date-format="day">
                    {{ optional($lastMapping?->updated_at)->format('Y/m/d') ?: '—' }}
                </span>
            </div>
            <div class="admin-stat-card__meta">تاریخ شمسی</div>
        </div>
    </div>

    <div class="admin-card" style="margin-bottom:18px;">
        <div class="admin-card-header">
            <div>
                <h2 class="admin-card-title">مرز مالکیت داده</h2>
                <p class="admin-card-description">این بخش عمداً وضعیت واقعی پروژه را شفاف نگه می‌دارد.</p>
            </div>
            <span class="admin-badge admin-badge--info">Foundation</span>
        </div>

        <div class="admin-card-body">
            <div class="admin-control-grid">
                <div class="admin-control-card">
                    <strong>نیلا / هلو</strong>
                    <span>نام محصول، slug، توضیحات، mapping دسته/برند و داده‌های واریانت مثل SKU، قیمت و موجودی.</span>
                </div>

                <div class="admin-control-card">
                    <strong>جانان / مدیر</strong>
                    <span>گالری تصاویر، تصویر اصلی، ترتیب تصاویر، alt و فایل‌های رسانه‌ای.</span>
                </div>

                <div class="admin-control-card">
                    <strong>فعلاً غیرفعال</strong>
                    <span>احراز هویت، endpoint، webhook، invoice و sync دوطرفه بدون قرارداد تأییدشده اجرا نمی‌شوند.</span>
                </div>
            </div>
        </div>
    </div>

    <div class="admin-card">
        <div class="admin-card-header">
            <div>
                <h2 class="admin-card-title">آخرین mappingها</h2>
                <p class="admin-card-description">آخرین تغییرات ثبت‌شده در مرز integration.</p>
            </div>
        </div>

        @if($recentMappings->isNotEmpty())
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                    <tr>
                        <th>نوع</th>
                        <th>شناسه داخلی</th>
                        <th>شناسه نیلا</th>
                        <th>SKU خارجی</th>
                        <th>آخرین تغییر</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($recentMappings as $mapping)
                        <tr>
                            <td>{{ $mapping->entity_type === \App\Models\Product::class ? 'محصول' : 'واریانت' }}</td>
                            <td>{{ $mapping->entity_id }}</td>
                            <td dir="ltr">{{ $mapping->external_id }}</td>
                            <td dir="ltr">{{ $mapping->external_sku ?: '—' }}</td>
                            <td>
                                <span data-admin-date="{{ optional($mapping->updated_at)->toIso8601String() }}" data-admin-date-format="day">
                                    {{ optional($mapping->updated_at)->format('Y/m/d') }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="admin-empty">
                <div class="admin-empty__icon">—</div>
                <h3 class="admin-empty__title">هنوز mappingی ثبت نشده</h3>
                <p class="admin-empty__text">این وضعیت به معنی نبودن قرارداد API نیست؛ فقط داده mapping فعلی را نشان می‌دهد.</p>
            </div>
        @endif
    </div>
@endsection
