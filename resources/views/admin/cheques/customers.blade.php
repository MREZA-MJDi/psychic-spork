@if($selectedCustomer)
    @php($permission = $selectedCustomer->chequePermission)
    <section class="cheque-hub-panel cheque-detail" id="cheque-customer-detail" aria-labelledby="cheque-customer-title">
        <header class="cheque-section-head">
            <div><h2 id="cheque-customer-title">مجوز خرید چکی {{ $selectedCustomer->name }}</h2><p><bdi>{{ $selectedCustomer->phone ?: $selectedCustomer->email }}</bdi></p></div>
            <a class="admin-btn admin-btn--ghost admin-btn--sm" href="{{ $listUrl }}">بستن</a>
        </header>
        <div class="cheque-detail-body">
            <p>مبلغ درخواستی: <strong>{{ $permission?->requested_amount !== null ? $number((float) $permission->requested_amount).' تومان' : '—' }}</strong></p>
            <form method="POST" action="{{ route('admin.customers.cheque.enable', $selectedCustomer) }}" class="cheque-action-form">
                @csrf @method('PATCH')
                <input type="hidden" name="enabled" value="1">
                <div class="admin-field"><label for="cheque-limit">سقف هر سفارش (تومان)</label>
                    <input id="cheque-limit" name="max_order_amount" inputmode="numeric" type="text" data-money-input required value="{{ old('max_order_amount', $permission?->max_order_amount ?? $permission?->requested_amount) }}" placeholder="مبلغ مجاز">
                </div>
                <button type="submit" class="admin-btn admin-btn--secondary">{{ $permission?->isApproved() ? 'تغییر سقف' : 'تأیید مجوز' }}</button>
            </form>
            @if($permission?->isPending() || $permission?->isApproved())
                <form method="POST" action="{{ $permission->isPending() ? route('admin.customers.cheque.reject', $selectedCustomer) : route('admin.customers.cheque.disable', $selectedCustomer) }}" onsubmit="return confirm('از تغییر وضعیت مجوز مطمئن هستید؟')">
                    @csrf @method('PATCH')
                    <button type="submit" class="admin-btn admin-btn--danger admin-btn--sm">{{ $permission->isPending() ? 'رد درخواست' : 'لغو مجوز' }}</button>
                </form>
            @endif
        </div>
    </section>
@endif
<section class="cheque-hub-panel" id="cheque-permission-requests" aria-labelledby="cheque-customer-list-title">
    <header class="cheque-section-head"><div><h2 id="cheque-customer-list-title">{{ $tab === 'requests' ? 'درخواست‌های اعتبار' : 'مجوز مشتریان' }}</h2><p>{{ $number($customers->total()) }} مشتری مطابق فیلتر</p></div></header>
    <form method="GET" action="{{ route('admin.cheques.index') }}" class="cheque-hub-filter">
        <input type="hidden" name="tab" value="{{ $tab }}">
        <div class="admin-field"><label for="customer-q">جستجوی مشتری</label><input id="customer-q" type="search" name="q" maxlength="120" value="{{ request('q') }}" placeholder="نام یا شماره تماس"></div>
        @if($tab === 'customers')
            <div class="admin-field"><label for="customer-access">وضعیت مجوز</label><select id="customer-access" name="access">
                <option value="approved" @selected($access === 'approved')>مشتریان مجاز</option>
                <option value="all" @selected($access === 'all')>همه مشتریان</option>
                <option value="disabled" @selected($access === 'disabled')>رد یا غیرفعال</option>
            </select></div>
        @else
            <span class="cheque-filter-spacer"></span>
        @endif
        <button type="submit" class="admin-btn admin-btn--secondary">جستجو</button>
        <a class="admin-btn admin-btn--ghost" href="{{ route('admin.cheques.index', ['tab' => $tab]) }}">پاک کردن</a>
    </form>
    <div class="cheque-table-wrap">
        <table class="cheque-table cheque-customer-table">
            <thead><tr><th scope="col">مشتری</th><th scope="col">مبلغ درخواستی</th><th scope="col">سقف مجاز</th><th scope="col">وضعیت</th><th scope="col">عملیات</th></tr></thead>
            <tbody>
            @forelse($customers as $customer)
                @php($permission = $customer->chequePermission)
                <tr @class(['cheque-selected' => $selectedCustomer?->id === $customer->id])>
                    <td data-label="مشتری"><strong>{{ $customer->name ?: 'بدون نام' }}</strong><small><bdi>{{ $customer->phone ?: $customer->email ?: '—' }}</bdi></small></td>
                    <td data-label="مبلغ درخواستی">{{ $permission?->requested_amount !== null ? $number((float) $permission->requested_amount).' تومان' : '—' }}</td>
                    <td data-label="سقف مجاز">{{ $permission?->isApproved() ? ($permission->max_order_amount !== null ? $number((float) $permission->max_order_amount).' تومان' : 'بدون سقف') : '—' }}</td>
                    <td data-label="وضعیت">
                        @if($permission?->isApproved())<span class="cheque-status cheque-status--success">فعال</span>
                        @elseif($permission?->isPending())<span class="cheque-status cheque-status--warning">در انتظار بررسی</span>
                        @else<span class="cheque-status cheque-status--neutral">غیرفعال</span>@endif
                    </td>
                    <td data-label="عملیات"><a class="admin-btn admin-btn--ghost admin-btn--sm" href="{{ route('admin.cheques.index', array_merge($listParams, ['edit_customer' => $customer->id])) }}#cheque-customer-detail">مدیریت مجوز</a></td>
                </tr>
            @empty
                <tr class="cheque-empty"><td colspan="5">{{ $tab === 'requests' ? 'درخواستی در انتظار بررسی نیست.' : 'مشتری مطابق فیلتر پیدا نشد.' }}</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($customers->hasPages())<div class="admin-pagination">{{ $customers->links() }}</div>@endif
</section>
