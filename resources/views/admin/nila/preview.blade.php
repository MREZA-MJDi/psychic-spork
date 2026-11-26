@extends('layouts.admin')

@section('title', 'گزارش فایل هولو')
@section('page-title', 'گزارش بررسی فایل هولو')

@section('content')
@php
    $remainingGroups = max(0, $batch->product_groups - $batch->next_group_offset);
    $pilotProgress = $batch->product_groups > 0
        ? min(100, (int) round(($batch->next_group_offset / $batch->product_groups) * 100))
        : 0;
@endphp
<div class="admin-page-head nila-preview-head">
    <div>
        <h1 class="admin-page-head__title">نتیجهٔ بررسی فایل هلو</h1>
        <p class="admin-page-head__text">{{ $batch->original_name }} · واحد قیمت فایل: {{ $priceUnit->value === 'rial' ? 'ریال' : 'تومان' }}</p>
    </div>
    <a class="admin-btn admin-btn--ghost" href="{{ route('admin.nila.index') }}">فایل‌های هلو</a>
</div>

<section class="admin-card nila-panel" aria-labelledby="nila-file-summary">
    <h2 class="admin-card-title" id="nila-file-summary">ردیف‌های این فایل به چند محصول تبدیل می‌شوند؟</h2>
    <p class="admin-card-description">از {{ \App\Support\PersianNumber::format($batch->total_rows) }} ردیف فایل، {{ \App\Support\PersianNumber::format($batch->product_groups) }} مدل با کد پایه شناسایی شد. ردیف‌های سایز یا رنگ یک مدل زیر همان محصول جمع می‌شوند.</p>
    @if($comparison['blocked_groups'] > 0 || $batch->invalid_rows > 0)
        <p>{{ \App\Support\PersianNumber::format($batch->invalid_rows) }} ردیف خطا دارد و {{ \App\Support\PersianNumber::format($comparison['blocked_groups']) }} مدل نیاز به اصلاح دارد. <a href="#nila-file-rows">ردیف‌ها و علت خطا را ببینید</a>.</p>
    @endif
    @if($batch->next_group_offset === 0)
        <p>ثبت اطلاعات این فایل هنوز شروع نشده است.</p>
    @endif
    <a href="#nila-import-progress">وضعیت و مرحله بعد</a>
</section>

<div class="admin-dashboard-stats" aria-label="وضعیت داده‌های فایل">
    <div class="admin-stat-card">
        <div class="admin-stat-card__label">مدل‌های شناسایی‌شده</div>
        <div class="admin-stat-card__value">{{ \App\Support\PersianNumber::format($batch->product_groups) }}</div>
        <div class="admin-stat-card__meta">{{ \App\Support\PersianNumber::format($batch->total_rows) }} ردیف خوانده‌شده از فایل</div>
    </div>
    <div class="admin-stat-card">
        <div class="admin-stat-card__label">مدل‌های قابل ثبت</div>
        <div class="admin-stat-card__value">{{ \App\Support\PersianNumber::format($comparison['valid_groups']) }}</div>
        <div class="admin-stat-card__meta">{{ \App\Support\PersianNumber::format($batch->imported_groups) }} مدل ثبت یا به‌روزرسانی شده</div>
    </div>
    <div class="admin-stat-card">
        <div class="admin-stat-card__label">مدل‌های نیازمند اصلاح فایل</div>
        <div class="admin-stat-card__value">{{ \App\Support\PersianNumber::format($comparison['blocked_groups']) }}</div>
        <div class="admin-stat-card__meta">{{ \App\Support\PersianNumber::format($batch->invalid_rows) }} ردیف دارای خطا</div>
    </div>
    <div class="admin-stat-card">
        <div class="admin-stat-card__label">ردیف‌های دارای قیمت خالی یا نامعتبر</div>
        <div class="admin-stat-card__value">{{ \App\Support\PersianNumber::format($comparison['invalid_price_rows']) }}</div>
        <div class="admin-stat-card__meta">
            {{ \App\Support\PersianNumber::format($comparison['invalid_stock_rows']) }} ردیف با موجودی نامعتبر
            · {{ \App\Support\PersianNumber::format($comparison['name_conflict_groups']) }} گروه با نام ناسازگار
        </div>
    </div>
</div>

<div class="admin-card nila-panel nila-panel--comparison">
    <div class="admin-card-header">
        <div>
            <span class="nila-step-label">مقایسه با اطلاعات فعلی جانان</span>
            <h2 class="admin-card-title">این فایل چه چیزی تغییر می‌دهد؟</h2>
            <p class="admin-card-description">شناسه‌های نیلا ملاک تطبیق هستند؛ نام مشابه به‌تنهایی باعث ادغام دو محصول نمی‌شود.</p>
        </div>
    </div>

    <div class="admin-dashboard-stats">
        <div class="admin-stat-card">
            <div class="admin-stat-card__label">محصول جدید</div>
            <div class="admin-stat-card__value">{{ \App\Support\PersianNumber::format($comparison['new_products']) }}</div>
        </div>
        <div class="admin-stat-card">
            <div class="admin-stat-card__label">محصول موجود و شناسایی‌شده</div>
            <div class="admin-stat-card__value">{{ \App\Support\PersianNumber::format($comparison['existing_products']) }}</div>
        </div>
        <div class="admin-stat-card">
            <div class="admin-stat-card__label">گزینهٔ جدیدِ سایز یا رنگ</div>
            <div class="admin-stat-card__value">{{ \App\Support\PersianNumber::format($comparison['new_variants']) }}</div>
        </div>
        <div class="admin-stat-card">
            <div class="admin-stat-card__label">موجودی نیازمند تطبیق</div>
            <div class="admin-stat-card__value">{{ \App\Support\PersianNumber::format($comparison['stock_change_count']) }}</div>
        </div>
    </div>

    <div class="nila-info-grid">
        <div class="nila-info-card">
            <strong>قیمت متفاوت</strong>
            <p class="admin-muted">{{ \App\Support\PersianNumber::format($comparison['price_change_count']) }} تنوع قیمت متفاوت با فایل نیلا دارد.</p>
        </div>
        <div class="nila-info-card">
            <strong>نام محصول تغییر کرده</strong>
            <p class="admin-muted">{{ \App\Support\PersianNumber::format($comparison['renamed_products']) }} نام محصول در فایل با نام فعلی جانان تفاوت دارد.</p>
        </div>
        <div class="nila-info-card">
            <strong>گروه دارای خطا</strong>
            <p class="admin-muted">{{ \App\Support\PersianNumber::format($comparison['blocked_groups']) }} مدل به‌دلیل خطای ردیف‌ها یا ناسازگاری نام ثبت نمی‌شود.</p>
        </div>
        <div class="nila-info-card">
            <strong>کد کالا با تعارض</strong>
            <p class="admin-muted">{{ \App\Support\PersianNumber::format($comparison['sku_conflict_count']) }} کد از قبل در جانان وجود دارد اما اتصال نیلا ندارد.</p>
        </div>
    </div>

    <div class="alert">
        مقدار ستون «موجودی» در فایل، موجودی نهایی همان کد کالا در نیلا فرض می‌شود؛ جانان فقط اختلاف با موجودی فعلی را به‌عنوان گردش اصلاحی ثبت می‌کند. این مقدار به موجودی فعلی اضافه نمی‌شود.
    </div>

    @if($comparison['stock_changes'])
        <h3 class="admin-card-title">نمونه تغییرات موجودی</h3>
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead><tr><th>کد کالا</th><th>موجودی فعلی</th><th>موجودی فایل</th><th>اختلاف</th></tr></thead>
                <tbody>
                @foreach($comparison['stock_changes'] as $change)
                    <tr>
                        <td dir="ltr">{{ $change['sku'] }}</td>
                        <td>{{ \App\Support\PersianNumber::format($change['current']) }}</td>
                        <td>{{ \App\Support\PersianNumber::format($change['incoming']) }}</td>
                        <td>{{ $change['difference'] > 0 ? '+' : '' }}{{ \App\Support\PersianNumber::format($change['difference']) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @if($comparison['stock_change_count'] > count($comparison['stock_changes']))
            <p class="admin-help">فقط ۳۰ مورد اول نمایش داده شده‌اند؛ همه اختلاف‌ها هنگام پردازش بررسی می‌شوند.</p>
        @endif
    @else
        <p class="admin-muted">موجودی هیچ‌کدام از تنوع‌های شناسایی‌شده با فایل تفاوت ندارد.</p>
    @endif

    @if($comparison['sku_conflicts'])
        <div class="alert error">
            <strong>کدهای دارای تعارض</strong>
            <p>این کدها به محصول دیگری در جانان تعلق دارند و اتصال خودکار انجام نمی‌شود:</p>
            @foreach($comparison['sku_conflicts'] as $conflict)
                <span dir="ltr">{{ $conflict['sku'] }}</span>@if(!$loop->last)، @endif
            @endforeach
        </div>
    @endif
</div>

<div class="admin-card nila-panel nila-panel--progress" id="nila-import-progress">
    <div class="nila-progress-heading">
        <div>
            <span class="nila-step-label">مرحله بعد</span>
            <h2 class="admin-card-title">ثبت اطلاعات و پیگیری نتیجه</h2>
        </div>
        <strong class="nila-progress-value">{{ \App\Support\PersianNumber::format($pilotProgress) }}٪</strong>
    </div>
    <div class="nila-progress-track" role="progressbar" aria-label="پیشرفت ثبت اطلاعات" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $pilotProgress }}">
        <span style="width: {{ $pilotProgress }}%"></span>
    </div>
    <p class="admin-card-description">
        وضعیت: {{ $batch->status === 'completed' ? 'تکمیل‌شده' : ($batch->status === 'queued' ? 'در صف اجرا' : ($batch->status === 'processing' ? 'در حال پردازش' : ($batch->status === 'ready' ? 'آماده ادامه' : 'آماده شروع'))) }}
        · ثبت یا به‌روزرسانی‌شده: {{ \App\Support\PersianNumber::format($batch->imported_groups) }}
        · ثبت‌نشده در نوبت‌های انجام‌شده: {{ \App\Support\PersianNumber::format($batch->failed_groups) }}
        · در انتظار بررسی: {{ \App\Support\PersianNumber::format($remainingGroups) }}
    </p>

    @if($batch->status === 'completed')
        <div class="alert success">پردازش این فایل به پایان رسیده است. برای اصلاح داده‌های نامعتبر، فایل اصلاح‌شده را دوباره بارگذاری کنید.</div>
    @elseif($batch->status === 'processing')
        <div class="alert">این فایل در حال پردازش است. از اجرای هم‌زمان درخواست دیگر خودداری کنید.</div>
    @elseif($batch->status === 'queued')
        <div class="alert">این مرحله در صف اجرا قرار دارد؛ از ارسال مجدد درخواست خودداری کنید.</div>
    @else
        <form method="POST" action="{{ route('admin.nila.imports.import', $batch) }}">
            @csrf
            <div class="admin-form-grid">
                <div class="admin-field">
                    <label for="batch_size">تعداد مدل‌ها در هر نوبت</label>
                    <select id="batch_size" name="batch_size">
                        <option value="25" selected>۲۵ مدل (پیشنهادی)</option>
                        <option value="50">۵۰ مدل (حداکثر هر نوبت)</option>
                    </select>
                    <small class="admin-help">هر نوبت همین تعداد مدل را همراه با کدهای سایز و رنگشان بررسی می‌کند. اگر مدلی باقی بماند، دکمهٔ ادامه در همین گزارش نمایش داده می‌شود.</small>
                </div>
            </div>
            <label class="admin-checkbox" style="margin-top:12px;">
                <input type="checkbox" name="confirm_stock_snapshot" value="1" required>
                <span><strong>متوجه‌ام:</strong> موجودی فایل، مقدار نهایی موجودی در نیلاست و اختلاف آن در جانان ثبت می‌شود.</span>
            </label>
            <div class="admin-form-actions nila-pilot-actions">
                <span class="nila-safe-note">مدل‌های جدید ابتدا غیرفعال‌اند. برای نمایششان در فروشگاه، آن‌ها را از فهرست محصولات فعال کنید.</span>
                <button type="submit" class="admin-btn admin-btn--secondary">
                    {{ $batch->next_group_offset > 0 ? 'ادامه ثبت اطلاعات' : 'شروع ثبت اطلاعات' }}
                </button>
            </div>
        </form>
    @endif
</div>

@if(!empty($failedProducts))
<div class="admin-card">
    <h2 class="admin-card-title">علت ثبت‌نشدن مدل‌ها</h2>
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

<div class="admin-card nila-panel nila-panel--groups">
    <span class="nila-step-label">مرحله ۲ · کنترل گروه‌ها</span>
    <h2 class="admin-card-title">گروه‌بندی محصولات</h2>
    <p class="admin-card-description">بخش قبل از ستاره، کد مدل است. هر ردیف با کد کامل خودش زیر همان مدل نگهداری می‌شود.</p>
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
                    <td>{{ \App\Support\PersianNumber::format($group['rows']) }}</td>
                    <td>{{ \App\Support\PersianNumber::format($group['valid_rows']) }}</td>
                    <td>{{ \App\Support\PersianNumber::format($group['invalid_rows']) }}</td>
                    <td>{{ $group['name_conflict'] ? 'تعارض نام؛ نیازمند بررسی' : 'یکسان' }}</td>
                </tr>
            @empty
                <tr><td colspan="6">ردیفی برای نمایش وجود ندارد.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($groups->hasPages())
        <div class="admin-pagination">{{ $groups->links() }}</div>
    @endif
</div>

<div class="admin-card nila-panel nila-panel--rows" id="nila-file-rows">
    <span class="nila-step-label">جزئیات داده‌های ورودی</span>
    <h2 class="admin-card-title">ردیف‌های فایل و خطاها</h2>
    <p class="admin-card-description">ردیف‌ها به‌صورت صفحه‌بندی‌شده نمایش داده می‌شوند. قیمت خالی خطا محسوب می‌شود و هرگز به صفر تبدیل نمی‌شود.</p>
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
                <th>قیمت نهایی (تومان)</th>
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
                    <td>{{ $row['price'] === null ? 'خالی / نامعتبر' : \App\Support\PersianNumber::format($row['price'], floor($row['price']) === (float) $row['price'] ? 0 : 2) }}</td>
                    <td>{{ $row['stock'] === null ? 'نامعتبر' : \App\Support\PersianNumber::format($row['stock']) }}</td>
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
    @if($rows->hasPages())
        <div class="admin-pagination">{{ $rows->links() }}</div>
    @endif
</div>

<div class="admin-card nila-panel nila-panel--rules">
    <h2 class="admin-card-title">قواعد ثبت اطلاعات فایل</h2>
    <ul>
        <li>ثبت تغییرات قیمت و موجودی پس از تأیید شما شروع می‌شود.</li>
        <li>کد هلو تعیین می‌کند که کالا جدید است یا اطلاعات کالای موجود باید به‌روزرسانی شود.</li>
        <li>دسته‌بندی و برند از فایل فعلی قابل تعیین نیستند و باید با داده‌های تأییدشده جانان تطبیق داده شوند.</li>
        <li>برای ثبت کالا، قیمت هر ردیف را در فایل وارد کنید.</li>
    </ul>


</div>
@endsection
