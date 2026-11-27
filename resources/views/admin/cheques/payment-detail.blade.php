<section class="cheque-hub-panel cheque-detail" id="cheque-detail" aria-labelledby="cheque-detail-title">
    <header class="cheque-section-head">
        <div><h2 id="cheque-detail-title">جزئیات چک <bdi>{{ $cheque->cheque_number ?: $cheque->id }}</bdi></h2><p>{{ $cheque->order?->user?->name ?: 'مشتری نامشخص' }}</p></div>
        <div class="cheque-inline-actions">@include('admin.cheques.status', ['status' => $cheque->status])<a class="admin-btn admin-btn--ghost admin-btn--sm" href="{{ $listUrl }}">بستن</a></div>
    </header>
    <dl class="cheque-facts">
        <div><dt>مبلغ چک</dt><dd>{{ $number((float) $cheque->amount) }} تومان</dd></div>
        <div><dt>سررسید</dt><dd>@if($cheque->due_date)<time datetime="{{ $cheque->due_date->toDateString() }}" data-admin-date="{{ $cheque->due_date->toDateString() }}" data-admin-date-format="day">{{ $cheque->due_date->format('Y/m/d') }}</time>@else — @endif</dd></div>
        <div><dt>شناسه صیادی</dt><dd><bdi>{{ $cheque->sayad_id }}</bdi></dd></div>
        <div><dt>بانک</dt><dd>{{ $cheque->bank_name ?: '—' }}</dd></div>
        <div><dt>صاحب حساب</dt><dd>{{ $cheque->account_holder ?: '—' }}</dd></div>
        <div><dt>شماره تماس مشتری</dt><dd><bdi>{{ $cheque->order?->user?->phone ?: '—' }}</bdi></dd></div>
        @if($cheque->order)<div><dt>سفارش</dt><dd><a class="cheque-detail-link" href="{{ route('admin.orders.show', $cheque->order) }}"><bdi>{{ $cheque->order->order_number }}</bdi></a></dd></div>@endif
        @if($cheque->original_received_at)<div><dt>دریافت اصل چک</dt><dd><time data-admin-date="{{ $cheque->original_received_at->toIso8601String() }}">{{ $cheque->original_received_at->format('Y/m/d H:i') }}</time></dd></div>@endif
    </dl>
    <div class="cheque-detail-body">
        @if($cheque->image_path)
            <a class="cheque-detail-link" href="{{ route('admin.cheques.image', $cheque) }}" target="_blank" rel="noopener">مشاهده تصویر چک (پنجره جدید)</a>
        @else
            <p class="cheque-note">تصویری برای این چک ثبت نشده است.</p>
        @endif
        @if($cheque->review_note)<div class="cheque-note"><strong>یادداشت / دستور ثبت‌شده</strong><p>{{ $cheque->review_note }}</p></div>@endif
        <h3>اقدام بعدی</h3>
        @switch($cheque->status)
            @case('submitted')
                <p>چک را بررسی کنید تا گزینه‌های تأیید و درخواست اصل چک فعال شوند.</p>
                <form method="POST" action="{{ route('admin.cheques.review', $cheque) }}">@csrf @method('PATCH')<button class="admin-btn admin-btn--secondary" type="submit">شروع بررسی</button></form>
                @break
            @case('under_review')
            @case('original_received')
                <p>تأیید چک سفارش را آزاد می‌کند، اما به معنی تسویه پرداخت نیست.</p>
                <form method="POST" action="{{ route('admin.cheques.accept', $cheque) }}" onsubmit="return confirm('چک تأیید و سفارش آزاد شود؟')">@csrf @method('PATCH')<button class="admin-btn admin-btn--secondary" type="submit">تأیید و آزادسازی سفارش</button></form>
                @if($cheque->status === 'under_review')
                    <details class="cheque-option"><summary>درخواست ارسال اصل چک</summary>
                        <form class="cheque-action-form" method="POST" action="{{ route('admin.cheques.request-original', $cheque) }}">
                            @csrf @method('PATCH')
                            <div class="admin-field"><label for="original-note">آدرس یا دستور ارسال اصل چک</label><textarea id="original-note" name="note" rows="3" minlength="5" maxlength="2000" required></textarea></div>
                            <button class="admin-btn admin-btn--secondary" type="submit">ثبت درخواست ارسال</button>
                        </form>
                    </details>
                @endif
                <details class="cheque-option"><summary>رد چک</summary>
                    <form class="cheque-action-form" method="POST" action="{{ route('admin.cheques.reject', $cheque) }}" onsubmit="return confirm('چک رد شود؟ ممکن است سفارش لغو شود.')">
                        @csrf @method('PATCH')
                        <div class="admin-field"><label for="reject-note">دلیل رد (اختیاری)</label><textarea id="reject-note" name="note" rows="2" maxlength="2000"></textarea></div>
                        <button class="admin-btn admin-btn--danger" type="submit">رد چک</button>
                    </form>
                </details>
                @break
            @case('awaiting_original')
                <p>فقط پس از دریافت فیزیکی چک، دریافت اصل را ثبت کنید.</p>
                <form class="cheque-action-form" method="POST" action="{{ route('admin.cheques.original-received', $cheque) }}">
                    @csrf @method('PATCH')
                    <div class="admin-field"><label for="received-note">یادداشت دریافت اصل چک (اختیاری)</label><textarea id="received-note" name="note" rows="2" maxlength="2000"></textarea></div>
                    <button class="admin-btn admin-btn--secondary" type="submit">اصل چک دریافت شد</button>
                </form>
                @break
            @case('accepted')
                <p>چک تأیید شده است؛ پس از واگذاری به بانک، واریز را ثبت کنید.</p>
                <form method="POST" action="{{ route('admin.cheques.deposit', $cheque) }}" onsubmit="return confirm('واگذاری چک به بانک ثبت شود؟')">@csrf @method('PATCH')<button class="admin-btn admin-btn--secondary" type="submit">ثبت واگذاری به بانک</button></form>
                @break
            @case('deposited')
                <p>تنها پس از تأیید وصول توسط بانک، پرداخت را تسویه کنید.</p>
                <form method="POST" action="{{ route('admin.cheques.clear', $cheque) }}" onsubmit="return confirm('وصول چک تأیید و پرداخت نهایی شود؟')">@csrf @method('PATCH')<button class="admin-btn admin-btn--secondary" type="submit">ثبت وصول و تسویه</button></form>
                <details class="cheque-option"><summary>ثبت برگشت چک</summary>
                    <form class="cheque-action-form" method="POST" action="{{ route('admin.cheques.bounce', $cheque) }}" onsubmit="return confirm('برگشت چک ثبت شود؟ ممکن است سفارش لغو شود.')">
                        @csrf @method('PATCH')
                        <div class="admin-field"><label for="bounce-note">علت برگشت (اختیاری)</label><textarea id="bounce-note" name="note" rows="2" maxlength="2000"></textarea></div>
                        <button class="admin-btn admin-btn--danger" type="submit">ثبت برگشت</button>
                    </form>
                </details>
                @break
            @default
                <p class="cheque-note">برای وضعیت فعلی اقدام دیگری در این بخش وجود ندارد.</p>
        @endswitch
    </div>
</section>
