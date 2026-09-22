@extends('layouts.admin')

@section('title', 'انبار')

@section('content')

    <div class="admin-page">

        {{-- Header --}}
        <div class="admin-page-header">

            <div>
                <h1 class="admin-page-title">
                    انبار
                </h1>

                <p class="admin-page-description">
                    مشاهده موجودی و ثبت گردش‌های انبار
                </p>
            </div>

        </div>


        {{-- Success --}}
        @if(session('success'))
            <div class="admin-alert admin-alert-success">
                {{ session('success') }}
            </div>
        @endif


        {{-- Error --}}
        @if(session('error'))
            <div class="admin-alert admin-alert-error">
                {{ session('error') }}
            </div>
        @endif


        {{-- Validation --}}
        @if($errors->any())

            <div class="admin-alert admin-alert-error">

                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>

            </div>

        @endif


        {{-- Inventory table --}}
        <div class="admin-card">

            <div class="admin-card-header">

                <div>

                    <h2 class="admin-card-title">
                        موجودی واریانت‌ها
                    </h2>

                    <p class="admin-card-description">
                        موجودی فعلی هر واریانت محصول
                    </p>

                </div>

            </div>


            <div class="admin-table-wrap">

                <table class="admin-table">

                    <thead>

                    <tr>
                        <th>محصول</th>
                        <th>SKU</th>
                        <th>ویژگی</th>
                        <th>موجودی</th>
                        <th>حد هشدار</th>
                        <th>وضعیت</th>
                        <th></th>
                    </tr>

                    </thead>


                    <tbody>

                    @forelse($variants as $variant)

                        @php
                            $stock = (int) $variant->stock;
                            $threshold = (int) $variant->low_stock_threshold;
                            $isLowStock = $stock <= $threshold;
                        @endphp

                        <tr>

                            {{-- Product --}}
                            <td>

                                <div class="admin-table-primary">
                                    {{ $variant->product?->name ?? '—' }}
                                </div>

                            </td>


                            {{-- SKU --}}
                            <td dir="ltr">
                                {{ $variant->sku }}
                            </td>


                            {{-- Variant attributes --}}
                            <td>

                                @if($variant->size)

                                    <div>
                                        سایز:
                                        {{ $variant->size }}
                                    </div>

                                @endif


                                @if($variant->color)

                                    <div>
                                        رنگ:
                                        {{ $variant->color }}
                                    </div>

                                @endif


                                @if(!$variant->size && !$variant->color)
                                    —
                                @endif

                            </td>


                            {{-- Stock --}}
                            <td>

                                <strong>
                                    {{ number_format($stock) }}
                                </strong>

                                @if($isLowStock)

                                    <div class="admin-table-warning">
                                        موجودی کم
                                    </div>

                                @endif

                            </td>


                            {{-- Threshold --}}
                            <td>
                                {{ number_format($threshold) }}
                            </td>


                            {{-- Status --}}
                            <td>

                                @if($variant->is_active)

                                    <span class="admin-badge admin-badge-success">
                                    فعال
                                </span>

                                @else

                                    <span class="admin-badge">
                                    غیرفعال
                                </span>

                                @endif

                            </td>


                            {{-- Actions --}}
                            <td>

                                <a
                                    href="{{ route('admin.products.variants.edit', [$variant->product, $variant]) }}"
                                    class="admin-btn admin-btn-sm admin-btn-light"
                                >
                                    مدیریت
                                </a>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="7">

                                <div class="admin-empty">
                                    واریانتی برای نمایش وجود ندارد.
                                </div>

                            </td>

                        </tr>

                    @endforelse

                    </tbody>

                </table>

            </div>


            {{-- Pagination --}}
            @if($variants->hasPages())

                <div class="admin-pagination">
                    {{ $variants->links() }}
                </div>

            @endif

        </div>



        {{-- Create inventory movement --}}
        <div class="admin-card admin-form-section">

            <div class="admin-card-header">

                <div>

                    <h2 class="admin-card-title">
                        ثبت گردش انبار
                    </h2>

                    <p class="admin-card-description">
                        هر تغییر موجودی را به‌صورت یک گردش مستقل ثبت کنید.
                    </p>

                </div>

            </div>


            <form
                method="POST"
                action="{{ route('admin.inventory.store') }}"
            >

                @csrf


                <div class="admin-form-grid">


                    {{-- Variant --}}
                    <div class="admin-field">

                        <label for="product_variant_id">
                            واریانت *
                        </label>

                        <select
                            id="product_variant_id"
                            name="product_variant_id"
                            required
                        >

                            <option value="">
                                انتخاب واریانت
                            </option>

                            @foreach($variantOptions as $variant)

                                <option
                                    value="{{ $variant->id }}"
                                    @selected(old('product_variant_id') == $variant->id)
                                >
                                {{ $variant->product?->name ?? 'محصول' }}
                                —
                                {{ $variant->sku }}
                                —
                                موجودی فعلی:
                                {{ $variant->stock }}
                                </option>

                            @endforeach

                        </select>


                        @error('product_variant_id')

                        <small class="admin-error">
                            {{ $message }}
                        </small>

                        @enderror

                    </div>



                    {{-- Movement type --}}
                    <div class="admin-field">

                        <label for="type">
                            نوع گردش *
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
                                value="purchase"
                                @selected(old('type') === 'purchase')
                            >
                            ورود خرید
                            </option>

                            <option
                                value="sale"
                                @selected(old('type') === 'sale')
                            >
                            خروج فروش
                            </option>

                            <option
                                value="adjustment"
                                @selected(old('type') === 'adjustment')
                            >
                            اصلاح موجودی
                            </option>

                            <option
                                value="damage"
                                @selected(old('type') === 'damage')
                            >
                            ضایعات / خرابی
                            </option>

                            <option
                                value="return"
                                @selected(old('type') === 'return')
                            >
                            برگشت کالا
                            </option>

                        </select>


                        @error('type')

                        <small class="admin-error">
                            {{ $message }}
                        </small>

                        @enderror

                    </div>



                    {{-- Quantity --}}
                    <div class="admin-field">

                        <label for="quantity">
                            تعداد *
                        </label>

                        <input
                            id="quantity"
                            type="number"
                            name="quantity"
                            value="{{ old('quantity') }}"
                            min="1"
                            step="1"
                            inputmode="numeric"
                            required
                        >


                        <small class="admin-help">
                            تعداد تغییر موجودی را وارد کنید.
                        </small>


                        @error('quantity')

                        <small class="admin-error">
                            {{ $message }}
                        </small>

                        @enderror

                    </div>



                    {{-- Note --}}
                    <div class="admin-field admin-field-full">

                        <label for="note">
                            توضیحات
                        </label>

                        <textarea
                            id="note"
                            name="note"
                            rows="4"
                            maxlength="1000"
                            placeholder="مثلاً خرید از تأمین‌کننده، اصلاح شمارش انبار، کالای آسیب‌دیده..."
                        >{{ old('note') }}</textarea>


                        @error('note')

                        <small class="admin-error">
                            {{ $message }}
                        </small>

                        @enderror

                    </div>

                </div>


                <div class="admin-form-actions">

                    <button
                        type="submit"
                        class="admin-btn admin-btn-primary"
                    >
                        ثبت گردش انبار
                    </button>

                </div>

            </form>

        </div>

    </div>

@endsection
