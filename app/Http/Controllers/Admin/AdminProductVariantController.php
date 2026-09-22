<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\StoreProductVariantRequest;
use App\Http\Requests\Admin\UpdateProductVariantRequest;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AdminProductVariantController extends AdminController
{
    public function index(Product $product): View
    {
        $product->load([
            'category',
            'brand',
        ]);

        $variants = $product->variants()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate(20);

        return view('admin.variants.index', [
            'product' => $product,
            'variants' => $variants,
        ]);
    }


    public function create(Product $product): View
    {
        return view('admin.variants.create', [
            'product' => $product,
        ]);
    }


    public function store(
        StoreProductVariantRequest $request,
        Product $product
    ): RedirectResponse {
        $data = $request->validated();

        $data['product_id'] = $product->id;
        $data['is_active'] = $request->boolean('is_active');

        ProductVariant::create($data);

        return redirect()
            ->route('admin.products.variants.index', $product)
            ->with(
                'success',
                'واریانت محصول با موفقیت ایجاد شد.'
            );
    }


    public function edit(
        Product $product,
        ProductVariant $variant
    ): View {
        $this->ensureVariantBelongsToProduct(
            $product,
            $variant
        );

        $product->load([
            'category',
            'brand',
        ]);

        return view('admin.variants.edit', [
            'product' => $product,
            'variant' => $variant,
        ]);
    }


    public function update(
        UpdateProductVariantRequest $request,
        Product $product,
        ProductVariant $variant
    ): RedirectResponse {
        $this->ensureVariantBelongsToProduct(
            $product,
            $variant
        );

        $data = $request->validated();

        /*
         * موجودی از اینجا تغییر نمی‌کند.
         *
         * تغییر stock باید فقط از طریق InventoryMovement /
         * InventoryService انجام شود تا سابقه انبار حفظ شود.
         */
        unset($data['stock']);

        $data['is_active'] = $request->boolean('is_active');

        $variant->update($data);

        return redirect()
            ->route('admin.products.variants.index', $product)
            ->with(
                'success',
                'واریانت محصول با موفقیت به‌روزرسانی شد.'
            );
    }


    public function destroy(
        Product $product,
        ProductVariant $variant
    ): RedirectResponse {
        $this->ensureVariantBelongsToProduct(
            $product,
            $variant
        );

        /*
         * اگر Variant در سفارش استفاده شده باشد،
         * حذف فیزیکی آن باعث آسیب به سابقه سفارش می‌شود.
         *
         * بنابراین فقط غیرفعال می‌شود.
         */
        if ($variant->orderItems()->exists()) {

            $variant->update([
                'is_active' => false,
            ]);

            return redirect()
                ->route('admin.products.variants.index', $product)
                ->with(
                    'success',
                    'این واریانت قبلاً در سفارش استفاده شده بود و به‌جای حذف، غیرفعال شد.'
                );
        }


        /*
         * اگر سابقه گردش انبار داشته باشد نیز حذف فیزیکی
         * منطقی نیست؛ چون InventoryMovement به آن وابسته است.
         *
         * در این حالت فقط غیرفعال می‌کنیم.
         */
        if ($variant->inventoryMovements()->exists()) {

            $variant->update([
                'is_active' => false,
            ]);

            return redirect()
                ->route('admin.products.variants.index', $product)
                ->with(
                    'success',
                    'این واریانت سابقه گردش انبار داشت و به‌جای حذف، غیرفعال شد.'
                );
        }


        $variant->delete();

        return redirect()
            ->route('admin.products.variants.index', $product)
            ->with(
                'success',
                'واریانت محصول حذف شد.'
            );
    }


    private function ensureVariantBelongsToProduct(
        Product $product,
        ProductVariant $variant
    ): void {
        abort_unless(
            (int) $variant->product_id === (int) $product->id,
            404
        );
    }
}
