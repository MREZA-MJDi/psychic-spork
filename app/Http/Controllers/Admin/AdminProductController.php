<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\MediaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class AdminProductController extends AdminController
{
    public function index(Request $request): View
    {
        $products = Product::query()
            ->with([
                'category',
                'brand',
                'variants',
                'galleryMedia',
            ])
            ->when(
                $request->filled('q'),
                function ($query) use ($request): void {
                    $search = $request->string('q')->toString();

                    $query->where(function ($query) use ($search): void {
                        $query
                            ->where(
                                'name',
                                'like',
                                '%' . $search . '%'
                            )
                            ->orWhereHas(
                                'variants',
                                fn ($variant) => $variant->where(
                                    'sku',
                                    'like',
                                    '%' . $search . '%'
                                )
                            );
                    });
                }
            )
            ->when(
                $request->filled('category_id'),
                fn ($query) => $query->where(
                    'category_id',
                    $request->integer('category_id')
                )
            )
            ->when(
                $request->filled('brand_id'),
                fn ($query) => $query->where(
                    'brand_id',
                    $request->integer('brand_id')
                )
            )
            ->when(
                $request->input('stock') === 'low',
                fn ($query) => $query->lowStock()
            )
            ->when(
                $request->filled('status'),
                function ($query) use ($request): void {
                    match ($request->input('status')) {
                        'active' => $query->where('is_active', true),
                        'inactive' => $query->where('is_active', false),
                        'featured' => $query->where('is_featured', true),
                        default => null,
                    };
                }
            )
            ->latest('updated_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.products.index', [
            'products' => $products,
            'categories' => $this->categories(),
            'brands' => $this->brands(),
        ]);
    }

    public function create(): View
    {
        return view('admin.products.create', [
            'product' => new Product(),
            'variant' => new ProductVariant(),
            'categories' => $this->categories(),
            'brands' => $this->brands(),
        ]);
    }

    public function store(
        StoreProductRequest $request,
        MediaService $media
    ): RedirectResponse {
        try {
            $data = $request->validated();

            $product = DB::transaction(
                function () use ($data, $request, $media): Product {
                    $product = Product::create(
                        $this->productData($data, $request)
                    );

                    $product->variants()->create([
                        'sku' => $data['sku'],
                        'size' => $data['size'] ?? null,
                        'color' => $data['color'] ?? null,
                        'color_code' => $data['color_code'] ?? null,
                        'price' => $data['price'],
                        'sale_price' => $data['sale_price'] ?? null,
                        'stock' => $data['stock'],
                        'low_stock_threshold' => $data['low_stock_threshold'],
                        'is_active' => $request->boolean('is_active'),
                        'sort_order' => 0,
                    ]);

                    if ($request->hasFile('image_file')) {
                        $media->attach(
                            $product,
                            'gallery',
                            $request->file('image_file'),
                            'products',
                            $product->name
                        );
                    }

                    return $product;
                }
            );

            return redirect()
                ->route('admin.products.edit', $product)
                ->with(
                    'success',
                    'محصول با موفقیت ایجاد شد.'
                );
        } catch (Throwable $e) {
            return $this->failure(
                $e,
                'ایجاد محصول انجام نشد.'
            );
        }
    }

    public function edit(Product $product): View
    {
        $product->load([
            'variants',
            'galleryMedia',
        ]);

        return view('admin.products.edit', [
            'product' => $product,
            'variant' => $product->variants->first()
                ?? new ProductVariant([
                    'product_id' => $product->id,
                ]),
            'categories' => $this->categories(),
            'brands' => $this->brands(),
        ]);
    }

    public function update(
        UpdateProductRequest $request,
        Product $product,
        MediaService $media
    ): RedirectResponse {
        try {
            $data = $request->validated();

            DB::transaction(function () use (
                $product,
                $request,
                $data,
                $media
            ): void {
                $product->update(
                    $this->productData($data, $request)
                );

                $variant = $product->variants()
                    ->lockForUpdate()
                    ->orderBy('sort_order')
                    ->orderBy('id')
                    ->first();

                if (! $variant) {
                    $variant = $product->variants()->create([
                        'sku' => $data['sku'],
                        'size' => $data['size'] ?? null,
                        'color' => $data['color'] ?? null,
                        'color_code' => $data['color_code'] ?? null,
                        'price' => $data['price'],
                        'sale_price' => $data['sale_price'] ?? null,
                        'stock' => 0,
                        'low_stock_threshold' => $data['low_stock_threshold'],
                        'is_active' => $request->boolean('is_active'),
                        'sort_order' => 0,
                    ]);
                }

                $oldStock = (int) $variant->stock;
                $newStock = (int) $data['stock'];

                $variant->update([
                    'sku' => $data['sku'],
                    'size' => $data['size'] ?? null,
                    'color' => $data['color'] ?? null,
                    'color_code' => $data['color_code'] ?? null,
                    'price' => $data['price'],
                    'sale_price' => $data['sale_price'] ?? null,
                    'stock' => $newStock,
                    'low_stock_threshold' => $data['low_stock_threshold'],
                    'is_active' => $request->boolean('is_active'),
                ]);

                if ($oldStock !== $newStock) {
                    $variant->inventoryMovements()->create([
                        'type' => 'adjustment',
                        'quantity' => $newStock - $oldStock,
                        'stock_after' => $newStock,
                        'note' => 'اصلاح موجودی از فرم محصول',
                        'created_by' => auth()->id(),
                    ]);
                }

                if ($request->hasFile('image_file')) {
                    $media->replace(
                        $product,
                        'gallery',
                        $request->file('image_file'),
                        'products',
                        $product->name
                    );
                }
            });

            return redirect()
                ->route('admin.products.index')
                ->with(
                    'success',
                    'محصول با موفقیت به‌روزرسانی شد.'
                );
        } catch (Throwable $e) {
            return $this->failure(
                $e,
                'به‌روزرسانی محصول انجام نشد.'
            );
        }
    }

    public function destroy(
        Product $product,
        MediaService $media
    ): RedirectResponse {
        try {
            DB::transaction(function () use (
                $product,
                $media
            ): void {
                $media->removeCollection(
                    $product,
                    'gallery'
                );

                $product->update([
                    'is_active' => false,
                ]);

                $product->variants()->update([
                    'is_active' => false,
                ]);

                $product->delete();
            });

            return back()->with(
                'success',
                'محصول با موفقیت حذف شد.'
            );
        } catch (Throwable $e) {
            return $this->failure(
                $e,
                'حذف محصول انجام نشد.'
            );
        }
    }

    private function productData(
        array $data,
        StoreProductRequest|UpdateProductRequest $request
    ): array {
        $attributes = null;

        if (filled($data['attributes_json'] ?? null)) {
            $attributes = json_decode(
                $data['attributes_json'],
                true
            );

            if (json_last_error() !== JSON_ERROR_NONE) {
                abort(
                    422,
                    'ویژگی‌های محصول JSON معتبر ندارند.'
                );
            }
        }

        return [
            'category_id' => $data['category_id'] ?? null,
            'brand_id' => $data['brand_id'] ?? null,
            'name' => $data['name'],
            'slug' => filled($data['slug'] ?? null)
                ? $data['slug']
                : Str::slug($data['name']),
            'short_description' => $data['short_description'] ?? null,
            'description' => $data['description'] ?? null,
            'attributes' => $attributes,
            'is_active' => $request->boolean('is_active'),
            'is_featured' => $request->boolean('is_featured'),
            'sort_order' => (int) (
                $data['sort_order'] ?? 0
            ),
        ];
    }

    private function categories()
    {
        return Category::query()
            ->active()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get([
                'id',
                'name',
            ]);
    }

    private function brands()
    {
        return Brand::query()
            ->active()
            ->orderBy('name')
            ->get([
                'id',
                'name',
            ]);
    }
}
