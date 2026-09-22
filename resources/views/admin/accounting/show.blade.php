@extends('layouts.admin')

@section('title', 'جزئیات تراکنش')

@section('content')

    <div class="admin-page">

        <div class="admin-page-header">

            <div>

                <div class="admin-breadcrumb">

                    <a href="{{ route('admin.accounting.index') }}">
                        حسابداری
                    </a>

                    <span>/</span>

                    <span>
                    تراکنش #{{ $transaction->id }}
                </span>

                </div>

                <h1 class="admin-page-title">
                    جزئیات تراکنش
                </h1>

                <p class="admin-page-description">
                    اطلاعات کامل تراکنش مالی
                </p>

            </div>


            <a
                href="{{ route('admin.accounting.index') }}"
                class="admin-btn admin-btn-light"
            >
                بازگشت
            </a>

        </div>


        <div class="admin-card">

            <div class="admin-card-header">

                <div>

                    <h2 class="admin-card-title">
                        تراکنش #{{ $transaction->id }}
                    </h2>

                    <p class="admin-card-description">
                        ثبت‌شده در
                        {{ optional($transaction->created_at)->format('Y/m/d H:i') }}
                    </p>

                </div>

            </div>


            <div class="admin-detail-list">


                {{-- Type --}}

                <div class="admin-detail-row">

                <span>
                    نوع
                </span>

                    <strong>

                        @if($transaction->type === 'income')

                            <span class="admin-badge admin-badge-success">
                            درآمد
                        </span>

                        @else

                            <span class="admin-badge">
                            هزینه
                        </span>

                        @endif

                    </strong>

                </div>


                {{-- Category --}}

                <div class="admin-detail-row">

                <span>
                    دسته‌بندی
                </span>

                    <strong>
                        {{ $transaction->category ?: '—' }}
                    </strong>

                </div>


                {{-- Amount --}}

                <div class="admin-detail-row">

                <span>
                    مبلغ
                </span>

                    <strong>
                        {{ number_format((float) $transaction->amount) }}
                        تومان
                    </strong>

                </div>


                {{-- Transaction date --}}

                <div class="admin-detail-row">

                <span>
                    تاریخ تراکنش
                </span>

                    <strong>
                        {{ optional($transaction->transaction_date)->format('Y/m/d') }}
                    </strong>

                </div>


                {{-- Created by --}}

                <div class="admin-detail-row">

                <span>
                    ثبت‌کننده
                </span>

                    <strong>
                        {{ $transaction->createdBy?->name ?? '—' }}
                    </strong>

                </div>


                {{-- Reference --}}

                @if($transaction->reference_type && $transaction->reference_id)

                    <div class="admin-detail-row">

                    <span>
                        مرجع
                    </span>

                        <strong dir="ltr">
                            {{ class_basename($transaction->reference_type) }}
                            #{{ $transaction->reference_id }}
                        </strong>

                    </div>

                @endif


                {{-- Description --}}

                <div class="admin-detail-row admin-detail-row-stack">

                <span>
                    شرح
                </span>

                    <strong>
                        {{ $transaction->description ?: 'بدون شرح' }}
                    </strong>

                </div>


            </div>

        </div>

    </div>

@endsection
