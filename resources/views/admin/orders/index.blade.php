@extends('layouts.admin')

@section('title', 'سفارش‌ها')

@section('content')

    <div class="admin-page">

        <div class="admin-page-header">

            <div>
                <h1 class="admin-page-title">
                    سفارش‌ها
                </h1>

                <p class="admin-page-description">
                    مشاهده و پیگیری سفارش‌های ثبت‌شده
                </p>
            </div>

        </div>

        @if(session('success'))
            <div class="admin-alert admin-alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="admin-alert admin-alert-error">
                {{ session('error') }}
            </div>
        @endif

        <div class="admin-card">

            <div class="admin-card-header">

                <div>
                    <h2 class="admin-card-title">
                        فهرست سفارش‌ها
                    </h2>

                    <p class="admin-card-description">
                        {{ $orders->total() }} سفارش
                    </p>
                </div>

            </div>

            <div class="admin-table-wrap">

                <table class="admin-table">

                    <thead>
                    <tr>
                        <th>شماره سفارش</th>
                        <th>مشتری</th>
                        <th>مبلغ</th>
                        <th>وضعیت سفارش</th>
                        <th>پرداخت</th>
                        <th>تاریخ</th>
                        <th></th>
                    </tr>
                    </thead>

                    <tbody>

                    @forelse($orders as $order)

                        <tr>

                            <td>
                                <div class="admin-table-primary">
                                    #{{ $order->id }}
                                </div>
                            </td>

                            <td>
                                <div class="admin-table-primary">
                                    {{ $order->user?->name ?? 'مهمان' }}
                                </div>

                                @if($order->user?->email)
                                    <div class="admin-table-secondary" dir="ltr">
                                        {{ $order->user->email }}
                                    </div>
                                @endif
                            </td>

                            <td>
                                {{ number_format((float) $order->total) }}
                                تومان
                            </td>

                            <td>

                                @php
                                    $statusNames = [
                                        'pending' => 'در انتظار',
                                        'confirmed' => 'تأیید شده',
                                        'preparing' => 'در حال آماده‌سازی',
                                        'shipped' => 'ارسال شده',
                                        'delivered' => 'تحویل شده',
                                        'cancelled' => 'لغو شده',
                                        'returned' => 'مرجوعی',
                                    ];
                                @endphp

                                <span class="admin-badge">
                                    {{ $statusNames[$order->status] ?? $order->status }}
                                </span>

                            </td>

                            <td>

                                @php
                                    $paymentStatusNames = [
                                        'pending' => 'در انتظار',
                                        'paid' => 'پرداخت شده',
                                        'failed' => 'ناموفق',
                                        'refunded' => 'بازپرداخت شده',
                                    ];
                                @endphp

                                <span class="admin-badge">
                                    {{ $paymentStatusNames[$order->payment_status] ?? $order->payment_status }}
                                </span>

                            </td>

                            <td>
                                {{ optional($order->placed_at ?? $order->created_at)->format('Y/m/d H:i') }}
                            </td>

                            <td>
                                <a
                                    href="{{ route('admin.orders.show', $order) }}"
                                    class="admin-btn admin-btn-sm admin-btn-light"
                                >
                                    مشاهده
                                </a>
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="7">

                                <div class="admin-empty">
                                    سفارشی ثبت نشده است.
                                </div>

                            </td>
                        </tr>

                    @endforelse

                    </tbody>

                </table>

            </div>

            @if($orders->hasPages())
                <div class="admin-pagination">
                    {{ $orders->links() }}
                </div>
            @endif

        </div>

    </div>

@endsection
