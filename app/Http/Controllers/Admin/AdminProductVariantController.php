<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductVariantRequest;
use App\Http\Requests\Admin\UpdateProductVariantRequest;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Support\NumericInput;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Services\InventoryService;
use Illuminate\View\View;

class AdminProductVariantController extends Controller
{
    public function lookup(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'in_stock' => ['sometimes', 'boolean'],
        ]);
        $term = trim($filters['q'] ?? '');
        $normalizedSku = NumericInput::normalize($term);
        $terms = preg_split('/[\s\x{200c}]+/u', $term, 8, PREG_SPLIT_NO_EMPTY);


        $variants = ProductVariant::query()
            ->with(['product:id,name,slug,brand_id', 'product.brand:id,name'])
            ->where('is_active', true)
            ->whereHas('product', fn ($query) => $query->where('is_active', true))
            ->when($request->boolean('wholesale'), fn ($query) => $query->whereNotNull('wholesale_price'))
            ->when($request->boolean('in_stock'), fn ($query) => $query->where('stock', '>', 0))
            ->when($terms, function ($query) use ($terms): void {
                foreach ($terms as $word) {
                    $spellings = array_unique([
                        $word,
                        strtr($word, ['ي' => 'ی', 'ك' => 'ک']),
                        strtr($word, ['ی' => 'ي', 'ک' => 'ك']),
                        NumericInput::normalize($word),
                    ]);

                    $query->where(function ($query) use ($spellings): void {
                        foreach ($spellings as $spelling) {
                            $match = "%{$spelling}%";
                            $query->orWhere('sku', 'like', $match)
                                ->orWhere('color', 'like', $match)
                                ->orWhere('size', 'like', $match)
                                ->orWhereHas('product', fn ($product) => $product
                                    ->where('name', 'like', $match)
                                    ->orWhere('slug', 'like', $match)
                                    ->orWhereHas('brand', fn ($brand) => $brand->where('name', 'like', $match)));
                        }
                    });
                }
            })
            ->when($term !== '', fn ($query) => $query->orderByRaw('CASE WHEN sku = ? THEN 0 ELSE 1 END', [$normalizedSku]))
            ->orderBy('product_id')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->simplePaginate(20)
            ->withQueryString();

        return response()->json([
            'data' => $variants->getCollection()->map(fn (ProductVariant $variant): array => [
                'id' => $variant->id,
                'name' => $variant->product?->name ?? 'محصول',
                'brand' => $variant->product?->brand?->name ?? 'بدون برند',
                'sku' => $variant->sku,
                'size' => $variant->size,
                'color' => $variant->color,
                'display_name' => $variant->display_name,
                'stock' => (int) $variant->stock,
                'wholesale_price' => $variant->wholesale_price,
            ])->values(),
            'next_page_url' => $variants->nextPageUrl(),
            'current_page' => $variants->currentPage(),
        ]);
    }

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
        Product $product,
        InventoryService $inventory
    ): RedirectResponse {
        $data = $request->validated();

        $data['product_id'] = $product->getKey();

        $data['sku'] = filled($data['sku'] ?? null)
            ? $data['sku']
            : $this->generateUniqueSku();

        $data['low_stock_threshold'] = (int) (
            $data['low_stock_threshold'] ?? 5
        );

        $data['sort_order'] = (int) (
            $data['sort_order'] ?? 0
        );

        $data['is_active'] = $request->boolean(
            'is_active',
            true
        );

        $initialStock = (int) ($data['stock'] ?? 0);
        $data['stock'] = 0;

        DB::transaction(function () use ($data, $initialStock, $inventory): void {
            $variant = ProductVariant::create($data);
            $inventory->setStock(
                $variant,
                $initialStock,
                'ثبت موجودی اولیه هنگام ایجاد تنوع',
                auth()->id()
            );
        }, 3);

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
         * موجودی مستقیم از این فرم تغییر نمی‌کند.
         * تغییر stock باید از مسیر InventoryMovement / InventoryService
         * انجام شود تا سابقه گردش موجودی حفظ شود.
         */
        unset($data['stock']);

        /*
         * اگر SKU خالی باشد، خودکار ساخته می‌شود.
         */
        $data['sku'] = filled($data['sku'] ?? null)
            ? $data['sku']
            : $this->generateUniqueSku(
                $variant->getKey()
            );

        $data['low_stock_threshold'] = (int) (
            $data['low_stock_threshold'] ?? 5
        );

        $data['sort_order'] = (int) (
            $data['sort_order'] ?? 0
        );

        $data['is_active'] = $request->boolean(
            'is_active'
        );

        /*
         * محصول از Route مشخص است و نباید از فرم تغییر کند.
         */
        unset($data['product_id']);

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
         * اگر در سفارش استفاده شده باشد،
         * به‌جای حذف فیزیکی غیرفعال می‌شود.
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
         * اگر سابقه گردش انبار داشته باشد،
         * حذف فیزیکی سابقه انبار را مخدوش می‌کند.
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

    private function generateUniqueSku(
        ?int $ignoreId = null
    ): string {
        do {
            $sku = 'JAN-' . strtoupper(
                    Str::random(8)
                );

            $exists = ProductVariant::query()
                ->where('sku', $sku)
                ->when(
                    $ignoreId !== null,
                    fn ($query) => $query->whereKeyNot($ignoreId)
                )
                ->exists();

        } while ($exists);

        return $sku;
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
