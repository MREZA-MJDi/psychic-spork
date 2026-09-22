@extends('layouts.admin')

@section('title', 'حسابداری')

@section('content')

    <div class="admin-page">

        <div class="admin-page-header">

            <div>
                <h1 class="admin-page-title">
                    حسابداری
                </h1>

                <p class="admin-page-description">
                    ثبت و مشاهده تراکنش‌های مالی فروشگاه
                </p>
            </div>

        </div>


        {{-- Flash messages --}}

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


        {{-- Validation errors --}}

        @if($errors->any())
            <div class="admin-alert admin-alert-error">

                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>

            </div>
        @endif


        {{-- Financial summary --}}

        <div class="admin-stats-grid">

            <div class="admin-stat-card">

                <div class="admin-stat-label">
                    مجموع درآمد
                </div>

                <div class="admin-stat-value">
                    {{ number_format((float) ($income ?? 0)) }}
                    <span>تومان</span>
                </div>

            </div>


            <div class="admin-stat-card">

                <div class="admin-stat-label">
                    مجموع هزینه
                </div>

                <div class="admin-stat-value">
                    {{ number_format((float) ($expense ?? 0)) }}
                    <span>تومان</span>
                </div>

            </div>


            <div class="admin-stat-card">

                <div class="admin-stat-label">
                    خالص
                </div>

                <div class="admin-stat-value">

                    {{ number_format((float) (($income ?? 0) - ($expense ?? 0))) }}

                    <span>تومان</span>

                </div>

            </div>

        </div>


        <div class="admin-two-column">


            {{-- Transactions --}}

            <div class="admin-card">

                <div class="admin-card-header">

                    <div>

                        <h2 class="admin-card-title">
                            تراکنش‌ها
                        </h2>

                        <p class="admin-card-description">
                            {{ $transactions->total() }} تراکنش
                        </p>

                    </div>

                </div>


                <div class="admin-table-wrap">

                    <table class="admin-table">

                        <thead>

                        <tr>
                            <th>تاریخ</th>
                            <th>نوع</th>
                            <th>دسته‌بندی</th>
                            <th>مبلغ</th>
                            <th>شرح</th>
                            <th>ثبت‌کننده</th>
                            <th></th>
                        </tr>

                        </thead>


                        <tbody>

                        @forelse($transactions as $transaction)

                            <tr>

                                {{-- Date --}}

                                <td>
                                    {{ optional($transaction->transaction_date)->format('Y/m/d') }}
                                </td>


                                {{-- Type --}}

                                <td>

                                    @if($transaction->type === 'income')

                                        <span class="admin-badge admin-badge-success">
                                        درآمد
                                    </span>

                                    @else

                                        <span class="admin-badge">
                                        هزینه
                                    </span>

                                    @endif

                                </td>


                                {{-- Category --}}

                                <td>
                                    {{ $transaction->category ?: '—' }}
                                </td>


                                {{-- Amount --}}

                                <td>
                                    {{ number_format((float) $transaction->amount) }}
                                    تومان
                                </td>


                                {{-- Description --}}

                                <td>
                                    {{ $transaction->description ?: '—' }}
                                </td>


                                {{-- Created by --}}

                                <td>
                                    {{ $transaction->createdBy?->name ?? '—' }}
                                </td>


                                {{-- Details --}}

                                <td>

                                    <a
                                        href="{{ route('admin.accounting.show', $transaction) }}"
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
                                        تراکنش مالی ثبت نشده است.
                                    </div>

                                </td>

                            </tr>

                        @endforelse

                        </tbody>

                    </table>

                </div>


                @if($transactions->hasPages())

                    <div class="admin-pagination">
                        {{ $transactions->links() }}
                    </div>

                @endif

            </div>



            {{-- Create transaction --}}

            <div class="admin-card">

                <div class="admin-card-header">

                    <div>

                        <h2 class="admin-card-title">
                            ثبت تراکنش
                        </h2>

                        <p class="admin-card-description">
                            ثبت درآمد یا هزینه جدید
                        </p>

                    </div>

                </div>


                <form
                    method="POST"
                    action="{{ route('admin.accounting.store') }}"
                >

                    @csrf


                    {{-- Type --}}

                    <div class="admin-field">

                        <label for="type">
                            نوع *
                        </label>

                        <select
                            id="type"
                            name="type"
                            required
                        >

                            <option value="">
                                انتخاب کنید
                            </option>

                            <option
                                value="income"
                                @selected(old('type') === 'income')
                            >
                            درآمد
                            </option>

                            <option
                                value="expense"
                                @selected(old('type') === 'expense')
                            >
                            هزینه
                            </option>

                        </select>

                        @error('type')
                        <small class="admin-error">
                            {{ $message }}
                        </small>
                        @enderror

                    </div>


                    {{-- Category --}}

                    <div class="admin-field">

                        <label for="category">
                            دسته‌بندی
                        </label>

                        <input
                            id="category"
                            type="text"
                            name="category"
                            value="{{ old('category') }}"
                            maxlength="100"
                            autocomplete="off"
                            placeholder="مثلاً فروش، خرید، تبلیغات..."
                        >

                        @error('category')
                        <small class="admin-error">
                            {{ $message }}
                        </small>
                        @enderror

                    </div>


                    {{-- Amount --}}

                    <div class="admin-field">

                        <label for="amount">
                            مبلغ *
                        </label>

                        <div class="admin-input-suffix">

                            <input
                                id="amount"
                                type="number"
                                name="amount"
                                value="{{ old('amount') }}"
                                min="1"
                                step="0.01"
                                inputmode="decimal"
                                required
                            >

                            <span>
                            تومان
                        </span>

                        </div>

                        @error('amount')
                        <small class="admin-error">
                            {{ $message }}
                        </small>
                        @enderror

                    </div>


                    {{-- Transaction date --}}

                    <div class="admin-field">

                        <label for="transaction_date">
                            تاریخ *
                        </label>

                        <input
                            id="transaction_date"
                            type="date"
                            name="transaction_date"
                            value="{{ old('transaction_date', now()->toDateString()) }}"
                            required
                        >

                        @error('transaction_date')
                        <small class="admin-error">
                            {{ $message }}
                        </small>
                        @enderror

                    </div>


                    {{-- Description --}}

                    <div class="admin-field">

                        <label for="description">
                            شرح
                        </label>

                        <textarea
                            id="description"
                            name="description"
                            rows="4"
                            maxlength="1000"
                        >{{ old('description') }}</textarea>

                        @error('description')
                        <small class="admin-error">
                            {{ $message }}
                        </small>
                        @enderror

                    </div>


                    {{-- Actions --}}

                    <div class="admin-form-actions">

                        <button
                            type="submit"
                            class="admin-btn admin-btn-primary"
                        >
                            ثبت تراکنش
                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

@endsection
