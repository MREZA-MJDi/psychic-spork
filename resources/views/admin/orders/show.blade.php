@extends('layouts.admin')

@section('title', 'جزئیات سفارش')

@section('content')

    <div class="admin-page">

        <div class="admin-page-header">

            <div>

                <div class="admin-breadcrumb">
                    <a href="{{ route('admin.orders.index') }}">
                        سفارش‌ها
                    </a>

                    <span>/</span>

                    <span>
                    سفارش #{{ $order->id }}
                </span>
                </div>

                <h1 class="admin-page-title">
                    سفارش #{{ $order->id }}
                </h1>

                <p class="admin-page-description">
                    جزئیات سفارش، اقلام و وضعیت پرداخت
                </p>

            </div>

            <a
                href="{{ route('admin.orders.index') }}"
                class="admin-btn admin-btn-light"
            >
                بازگشت به سفارش‌ها
            </a>

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

        @if($errors->any())
            <div class="admin-alert admin-alert-error">
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif


        {{-- Summary --}}
        <div class="admin-stats-grid">

            <div class="admin-stat-card">

                <div class="admin-stat-label">
                    مبلغ سفارش
                </div>

                <div class="admin-stat-value">
                    {{ number_format((float) $order->total) }}
                    <span>تومان</span>
                </div>

            </div>

            <div class="admin-stat-card">

                <div class="admin-stat-label">
                    وضعیت سفارش
                </div>

                <div class="admin-stat-value admin-stat-value-small">
                    {{ $statusNames[$order->status] ?? $order->status }}
                </div>

            </div>

            <div class="admin-stat-card">

                <div class="admin-stat-label">
                    وضعیت پرداخت
                </div>

                <div class="admin-stat-value admin-stat-value-small">
                    {{ $paymentStatusNames[$order->payment_status] ?? $order->payment_status }}
                </div>

            </div>

            <div class="admin-stat-card">

                <div class="admin-stat-label">
                    تاریخ ثبت
                </div>

                <div class="admin-stat-value admin-stat-value-small">
                    {{ optional($order->placed_at ?? $order->created_at)->format('Y/m/d H:i') }}
                </div>

            </div>

        </div>


        <div class="admin-two-column">


            {{-- Order items --}}
            <div class="admin-card">

                <div class="admin-card-header">

                    <div>
                        <h2 class="admin-card-title">
                            اقلام سفارش
                        </h2>

                        <p class="admin-card-description">
                            {{ $order->items->count() }} قلم
                        </p>
                    </div>

                </div>

                <div class="admin-table-wrap">

                    <table class="admin-table">

                        <thead>
                        <tr>
                            <th>محصول</th>
                            <th>واریانت</th>
                            <th>تعداد</th>
                            <th>قیمت واحد</th>
                            <th>مبلغ</th>
                        </tr>
                        </thead>

                        <tbody>

                        @forelse($order->items as $item)

                            <tr>

                                <td>
                                    <div class="admin-table-primary">
                                        {{ $item->product_name ?? $item->product?->name ?? '—' }}
                                    </div>
                                </td>

                                <td>
                                    {{ $item->variant_name ?? $item->variant?->sku ?? '—' }}
                                </td>

                                <td>
                                    {{ number_format((int) $item->quantity) }}
                                </td>

                                <td>
                                    {{ number_format((float) $item->unit_price) }}
                                    تومان
                                </td>

                                <td>
                                    {{ number_format((float) $item->total) }}
                                    تومان
                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="5">
                                    <div class="admin-empty">
                                        این سفارش آیتمی ندارد.
                                    </div>
                                </td>
                            </tr>

                        @endforelse

                        </tbody>

                    </table>

                </div>

            </div>


            {{-- Customer + status --}}
            <div>

                <div class="admin-card">

                    <div class="admin-card-header">

                        <div>
                            <h2 class="admin-card-title">
                                مشتری
                            </h2>
                        </div>

                    </div>

                    <div class="admin-detail-list">

                        <div class="admin-detail-row">
                            <span>نام</span>
                            <strong>
                                {{ $order->user?->name ?? 'مهمان' }}
                            </strong>
                        </div>

                        @if($order->user?->email)

                            <div class="admin-detail-row">
                                <span>ایمیل</span>
                                <strong dir="ltr">
                                    {{ $order->user->email }}
                                </strong>
                            </div>

                        @endif

                        @if($order->user?->phone)

                            <div class="admin-detail-row">
                                <span>شماره تماس</span>
                                <strong dir="ltr">
                                    {{ $order->user->phone }}
                                </strong>
                            </div>

                        @endif

                    </div>

                </div>


                <div class="admin-card admin-form-section">

                    <div class="admin-card-header">

                        <div>
                            <h2 class="admin-card-title">
                                مدیریت سفارش
                            </h2>

                            <p class="admin-card-description">
                                فقط وضعیت‌های مجاز سفارش را تغییر دهید.
                            </p>
                        </div>

                    </div>

                    <form
                        method="POST"
                        action="{{ route('admin.orders.update', $order) }}"
                    >

                        @csrf
                        @method('PUT')

                        <div class="admin-field">

                            <label for="status">
                                وضعیت سفارش
                            </label>

                            <select
                                id="status"
                                name="status"
                                required
                            >

                                @foreach($statusNames as $status => $label)

                                    <option
                                        value="{{ $status }}"
                                        @selected(old('status', $order->status) === $status)
                                    >
                                    {{ $label }}
                                    </option>

                                @endforeach

                            </select>

                        </div>

                        <div class="admin-field">

                            <label for="payment_status">
                                وضعیت پرداخت
                            </label>

                            <select
                                id="payment_status"
                                name="payment_status"
                                required
                            >

                                @foreach($paymentStatusNames as $paymentStatus => $label)

                                    <option
                                        value="{{ $paymentStatus }}"
                                        @selected(old('payment_status', $order->payment_status) === $paymentStatus)
                                    >
                                    {{ $label }}
                                    </option>

                                @endforeach

                            </select>

                        </div>

                        <div class="admin-form-actions">

                            <button
                                type="submit"
                                class="admin-btn admin-btn-primary"
                            >
                                ذخیره وضعیت
                            </button>

                        </div>

                    </form>

                </div>

            </div>

        </div>


        {{-- Address --}}
        @if($order->shipping_address)

            <div class="admin-card admin-form-section">

                <div class="admin-card-header">

                    <div>
                        <h2 class="admin-card-title">
                            آدرس ارسال
                        </h2>
                    </div>

                </div>

                <div class="admin-address-box">
                    {!! nl2br(e($order->shipping_address)) !!}
                </div>

            </div>

        @endif

    </div>

@endsection
