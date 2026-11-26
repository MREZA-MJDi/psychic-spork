@extends('layouts.admin')

@section('title', 'ورود محصولات از هلو')
@section('page-title', 'نیلا و هلو')

@section('content')
<div class="admin-page-head">
    <div>
        <h1 class="admin-page-head__title">ورود محصولات و موجودی از هلو</h1>
        <p class="admin-page-head__text">فایل خروجی هلو را بفرستید، تعداد مدل‌ها و خطاها را بررسی کنید و بعد قیمت و موجودی را در جانان ثبت کنید.</p>
    </div>
    <a class="admin-btn admin-btn--ghost" href="{{ route('admin.products.index') }}">فهرست محصولات جانان</a>
</div>

<div class="admin-dashboard-stats" aria-label="محصولات هلو در جانان">
    <div class="admin-stat-card">
        <div class="admin-stat-card__label">مدل‌های ثبت‌شده از هلو</div>
        <div class="admin-stat-card__value">{{ \App\Support\PersianNumber::format($products) }}</div>
        <div class="admin-stat-card__meta">کل محصولات متصل، شامل محصولات غیرفعال</div>
    </div>
    <div class="admin-stat-card">
        <div class="admin-stat-card__label">مدل‌های فعال</div>
        <div class="admin-stat-card__value">{{ \App\Support\PersianNumber::format($activeProducts) }}</div>
        <div class="admin-stat-card__meta">{{ \App\Support\PersianNumber::format($products - $activeProducts) }} مدل متصل غیرفعال است</div>
    </div>
    <div class="admin-stat-card">
        <div class="admin-stat-card__label">گزینه‌های دارای کد کالا</div>
        <div class="admin-stat-card__value">{{ \App\Support\PersianNumber::format($variants) }}</div>
        <div class="admin-stat-card__meta">مثل سایزها و رنگ‌های یک مدل</div>
    </div>
</div>

<section class="admin-card nila-panel" aria-labelledby="nila-counts-title">
    <h2 class="admin-card-title" id="nila-counts-title">چرا تعداد ردیف‌های فایل از تعداد محصولات بیشتر است؟</h2>
    <p class="admin-card-description">در هلو، سایزها یا رنگ‌های یک مدل می‌توانند کد جداگانه داشته باشند. جانان ردیف‌هایی را که کد پایهٔ مشترک دارند زیر یک مدل جمع می‌کند. مثلاً سه سایز از یک سوتین می‌تواند سه ردیف در فایل و یک محصول با سه گزینه در جانان باشد.</p>
    @if($latestBatch)
        <p>آخرین فایل، <strong>{{ $latestBatch->original_name }}</strong>، {{ \App\Support\PersianNumber::format($latestBatch->total_rows) }} ردیف و {{ \App\Support\PersianNumber::format($latestBatch->product_groups) }} مدل شناسایی‌شده دارد.</p>
        <a href="{{ route('admin.nila.imports.show', $latestBatch) }}">گزارش آخرین فایل و علت موارد ثبت‌نشده</a>
    @else
        <p>بعد از بررسی اولین فایل، تعداد ردیف‌ها، مدل‌های شناسایی‌شده و خطاها در گزارش همان فایل نمایش داده می‌شود.</p>
    @endif
</section>

<div class="admin-card nila-panel nila-panel--upload">
    <div class="admin-card-header">
        <div>
            <span class="nila-step-label">مرحله ۱</span>
            <h2 class="admin-card-title">بررسی فایل خروجی هلو</h2>
            <p class="admin-card-description">
                ابتدا گزارش تعداد مدل‌ها، خطاها و تغییرات قیمت و موجودی را ببینید. ثبت اطلاعات نیاز به تأیید شما در صفحهٔ گزارش دارد.
            </p>
        </div>
    </div>

    <form class="nila-upload-form" method="POST" action="{{ route('admin.nila.imports.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="admin-form-grid">
            <div class="admin-field nila-file-field">
                <label for="nila-import-file">فایل خروجی هلو</label>
                <div class="nila-file-help">فایل ماهانه یا خروجی جدید هلو را انتخاب کنید.</div>
                <input
                    id="nila-import-file"
                    type="file"
                    name="file"
                    accept=".xls,.xlsx,.csv,.txt"
                    required
                >
                <p class="admin-muted nila-file-hint">فرمت مجاز: XLS، XLSX یا CSV · حداکثر ۱۰ مگابایت · فقط شیت اول اکسل بررسی می‌شود.</p>
                @error('file')
                    <div class="alert error">{{ $message }}</div>
                @enderror
            </div>
            <div class="admin-field">
                <label for="nila-price-unit">واحد قیمت فایل</label>
                <select id="nila-price-unit" name="price_unit" required>
                    <option value="rial" @selected(old('price_unit') === 'rial')>ریال</option>
                    <option value="toman" @selected(old('price_unit', 'toman') === 'toman')>تومان</option>
                </select>
                <p class="admin-help">واحد ستون قیمت را از فایل هلو انتخاب کنید. هر ۱۰ ریال در جانان به ۱ تومان تبدیل می‌شود.</p>
                @error('price_unit')<div class="alert error">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="admin-form-actions nila-upload-actions">
            <button type="submit" class="admin-btn admin-btn--primary">بررسی فایل</button>
        </div>
    </form>
</div>

<div class="admin-card nila-panel nila-panel--history">
    <div class="admin-card-header">
        <div>
            <h2 class="admin-card-title">گزارش فایل‌های قبلی</h2>
            <p class="admin-card-description">برای دیدن خطاها یا ادامه ثبت اطلاعات، گزارش همان فایل را باز کنید.</p>
        </div>
    </div>

    @if($batches->count() > 0)
        <div class="admin-table-wrap">
            <table class="admin-table nila-table">
                <thead>
                <tr>
                    <th>نام فایل</th>
                    <th>ردیف‌ها</th>
                    <th>ردیف معتبر</th>
                    <th>دارای خطا</th>
                    <th>مدل شناسایی‌شده</th>
                    <th>مدل ثبت یا به‌روزشده</th>
                    <th>مدل ثبت‌نشده</th>
                    <th>وضعیت</th>
                    <th>تاریخ</th>
                    <th>جزئیات</th>
                </tr>
                </thead>
                <tbody>
                @foreach($batches as $batch)
                    <tr>
                        <td>{{ $batch->original_name }}</td>
                        <td>{{ \App\Support\PersianNumber::format($batch->total_rows) }}</td>
                        <td>{{ \App\Support\PersianNumber::format($batch->valid_rows) }}</td>
                        <td>{{ \App\Support\PersianNumber::format($batch->invalid_rows) }}</td>
                        <td>{{ \App\Support\PersianNumber::format($batch->product_groups) }}</td>
                        <td>{{ \App\Support\PersianNumber::format($batch->imported_groups) }}</td>
                        <td>{{ \App\Support\PersianNumber::format($batch->failed_groups) }}</td>
                        <td>
                            @if($batch->status === 'completed')
                                تکمیل‌شده
                            @elseif($batch->status === 'queued')
                                در صف اجرا
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

<details class="admin-card nila-panel nila-panel--mappings">
    <summary>جزئیات فنی اتصال کالاها</summary>
    <p class="admin-card-description">آخرین اتصال‌های ثبت‌شده بین کدهای هلو و جانان، برای بررسی مغایرت کد کالا.</p>

    @if($mappings->isNotEmpty())
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                <tr>
                    <th>نوع</th>
                    <th>شناسه جانان</th>
                    <th>کد هلو</th>
                    <th>کد کامل کالا</th>
                    <th>آخرین تغییر</th>
                </tr>
                </thead>
                <tbody>
                @foreach($mappings as $mapping)
                    <tr>
                        <td>{{ class_basename($mapping->entity_type) === 'Product' ? 'مدل' : 'گزینهٔ کالا' }}</td>
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
            <h3 class="admin-empty__title">هنوز اتصال کالایی ثبت نشده</h3>
            <p class="admin-empty__text">پس از ثبت اطلاعات، کدهای متصل در این بخش نمایش داده می‌شوند.</p>
        </div>
    @endif
</details>
@endsection
