<section class="admin-card cheque-hub-panel" id="cheque-permission-requests">
    <form method="GET" action="{{ route('admin.cheques.index') }}" class="admin-filter-bar cheque-hub-filter">
        <input type="hidden" name="tab" value="{{ $tab }}">
        <input type="search" name="q" aria-label="جستجوی مشتری" value="{{ request('q') }}" placeholder="نام یا شماره تماس">
        @if($tab === 'customers')
            <select name="access" aria-label="وضعیت مجوز">
                <option value="approved" @selected($access === 'approved')>مشتریان مجاز</option>
                <option value="all" @selected($access === 'all')>همه مشتریان</option>
                <option value="disabled" @selected($access === 'disabled')>رد یا غیرفعال</option>
            </select>
        @endif
        <button type="submit" class="admin-btn admin-btn--secondary">جستجو</button>
        <a class="admin-btn admin-btn--ghost" href="{{ route('admin.cheques.index', ['tab' => $tab]) }}">پاک کردن</a>
    </form>

    <div class="admin-table-wrap">
        <table class="admin-table cheque-customer-table">
            <thead><tr><th>مشتری</th><th>مبلغ درخواستی</th><th>وضعیت / سقف مجاز</th><th>عملیات</th></tr></thead>
            <tbody>
            @forelse($customers as $customer)
                @php($permission = $customer->chequePermission)
                <tr>
                    <td><strong>{{ $customer->name ?: 'بدون نام' }}</strong><small dir="ltr">{{ $customer->phone ?: $customer->email ?: '—' }}</small></td>
                    <td>{{ $permission?->requested_amount !== null ? \App\Support\PersianNumber::format((float) $permission->requested_amount).' تومان' : '—' }}</td>
                    <td>
                        @if($permission?->isApproved())
                            <span class="admin-badge admin-badge--success">فعال</span>
                            <small>{{ $permission->max_order_amount !== null ? \App\Support\PersianNumber::format((float) $permission->max_order_amount).' تومان' : 'بدون سقف' }}</small>
                        @elseif($permission?->isPending())
                            <span class="admin-badge admin-badge--warning">در انتظار بررسی</span>
                        @else
                            <span class="admin-badge admin-badge--neutral">غیرفعال</span>
                        @endif
                    </td>
                    <td>
                        <form method="POST" action="{{ route('admin.customers.cheque.enable', $customer) }}" class="cheque-limit-form">
                            @csrf @method('PATCH')
                            <input type="hidden" name="enabled" value="1">
                            <label for="limit-{{ $customer->id }}">سقف هر سفارش (تومان)</label>
                            <input id="limit-{{ $customer->id }}" name="max_order_amount" inputmode="numeric" type="text" data-money-input required value="{{ $permission?->max_order_amount ?? $permission?->requested_amount }}" placeholder="مبلغ">
                            <button type="submit" class="admin-btn admin-btn--secondary admin-btn--sm">{{ $permission?->isApproved() ? 'تغییر سقف' : 'تأیید مجوز' }}</button>
                        </form>
                        @if($permission?->isPending() || $permission?->isApproved())
                            <form method="POST" action="{{ $permission->isPending() ? route('admin.customers.cheque.reject', $customer) : route('admin.customers.cheque.disable', $customer) }}" class="cheque-secondary-action" onsubmit="return confirm('از تغییر وضعیت مجوز مطمئن هستید؟')">
                                @csrf @method('PATCH')
                                <button type="submit" class="admin-btn admin-btn--ghost admin-btn--sm">{{ $permission->isPending() ? 'رد درخواست' : 'لغو مجوز' }}</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="4">{{ $tab === 'requests' ? 'درخواستی در انتظار بررسی نیست.' : 'مشتری مطابق فیلتر پیدا نشد.' }}</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($customers->hasPages())<div class="admin-pagination">{{ $customers->links() }}</div>@endif
</section>
