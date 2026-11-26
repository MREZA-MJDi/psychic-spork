<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductVariant;
use App\Models\WholesalePack;
use App\Support\NumericInput;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminWholesalePackController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', 'in:active,inactive'],
        ]);

        $packs = WholesalePack::query()
            ->select(['id','name','slug','pack_quantity','pack_price','sort_order','is_active'])
            ->when(filled($filters['q'] ?? null), function ($query) use ($filters): void {
                $term = '%' . trim($filters['q']) . '%';
                $query->where(fn ($packs) => $packs->where('name', 'like', $term)
                    ->orWhereHas('items.variant.product', fn ($product) => $product->where('name', 'like', $term)));
            })
            ->when(isset($filters['status']), fn ($query) => $query
                ->where('is_active', $filters['status'] === 'active'))
            ->with([
                'items' => fn ($query) => $query->select([
                    'id','wholesale_pack_id','product_variant_id','quantity',
                ]),
                'items.variant' => fn ($query) => $query->select([
                    'id','product_id','sku','size','color','sort_order',
                ]),
                'items.variant.product:id,name,brand_id',
                'items.variant.product.brand:id,name',
            ])
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->paginate(20)->appends($filters);

        return view('admin.wholesale-packs.index', compact('packs', 'filters'));
    }

    public function create(Request $request): View
    {
        return view('admin.wholesale-packs.form', [
            'pack' => new WholesalePack(),
            'variants' => $this->selectedVariants($request, new WholesalePack()),
        ]);
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $data = $this->validated($request);
        $image = $data['image'] ?? null;
        unset($data['image'], $data['remove_image']);
        if ($image) {
            $data['image_path'] = $image->store('wholesale-packs', 'public');
        }

        $pack = DB::transaction(function () use ($data): WholesalePack {
            $items = $data['items'];
            unset($data['items']);

            $pack = WholesalePack::create($data);
            $pack->items()->createMany($items);
            $pack->update(['pack_quantity' => $pack->items()->sum('quantity')]);

            return $pack;
        });

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'پک عمده ذخیره شد.',
                'edit_url' => route('admin.wholesale-packs.edit', $pack),
            ], 201);
        }

        return redirect()
            ->route('admin.wholesale-packs.edit', $pack)
            ->with('success', 'پک عمده با موفقیت ساخته شد.');
    }

    public function edit(Request $request, WholesalePack $wholesalePack): View
    {
        $wholesalePack->load('items.variant.product.brand');

        return view('admin.wholesale-packs.form', [
            'pack' => $wholesalePack,
            'variants' => $this->selectedVariants($request, $wholesalePack),
        ]);
    }

    public function update(Request $request, WholesalePack $wholesalePack): RedirectResponse
    {
        $data = $this->validated($request);
        $image = $data['image'] ?? null;
        $removeImage = $request->boolean('remove_image');
        unset($data['image'], $data['remove_image']);
        if ($image) $data['image_path'] = $image->store('wholesale-packs', 'public');
        elseif ($removeImage) $data['image_path'] = null;

        DB::transaction(function () use ($data, $wholesalePack): void {
            $items = $data['items'];
            unset($data['items']);
            $wholesalePack->update($data);
            $wholesalePack->items()->delete();
            $wholesalePack->items()->createMany($items);
            $wholesalePack->update(['pack_quantity' => $wholesalePack->items()->sum('quantity')]);
        });

        return back()->with('success', 'پک عمده به‌روزرسانی شد.');
    }

    public function destroy(WholesalePack $wholesalePack): RedirectResponse
    {
        $wholesalePack->delete();
        return back()->with('success', 'پک عمده حذف شد.');
    }

    private function selectedVariants(Request $request, WholesalePack $pack): Collection
    {
        if (!$request->session()->hasOldInput('items')) {
            return $pack->exists ? $pack->items->pluck('variant')->filter()->values() : collect();
        }

        $ids = collect($request->old('items'))->take(60)->pluck('variant_id')
            ->filter(fn ($id) => is_scalar($id) && ctype_digit((string) $id))->unique();

        return ProductVariant::query()->with('product.brand')->whereIn('id', $ids)->get();
    }

    private function validated(Request $request): array
    {
        $routePack = $request->route('wholesale_pack') ?? $request->route('wholesalePack');
        $ignorePackId = $routePack instanceof WholesalePack ? $routePack->getKey() : $routePack;

        $request->merge([
            'pack_price' => NumericInput::normalize($request->input('pack_price')),
        ]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:180'],
            'slug' => ['nullable', 'string', 'max:180', Rule::unique('wholesale_packs', 'slug')->ignore($ignorePackId)],
            'description' => ['nullable', 'string', 'max:5000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'remove_image' => ['nullable', 'boolean'],
            'pack_price' => [$request->isMethod('post') ? 'nullable' : 'required', 'numeric', 'min:1'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'items' => ['required', 'array', 'min:1', 'max:60'],
            'items.*.variant_id' => ['required', 'integer', 'distinct', 'exists:product_variants,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:100000'],
        ]);

        $variantIds = collect($data['items'])->pluck('variant_id');

        $validCount = ProductVariant::query()
            ->whereIn('id', $variantIds)
            ->where('is_active', true)
            ->whereHas('product', fn ($query) => $query->where('is_active', true))
            ->count();

        if ($validCount !== $variantIds->count()) {
            throw ValidationException::withMessages(['items' => 'یکی از مدل‌های انتخاب‌شده غیرفعال است. آن را از پک بردار یا در صفحهٔ محصول فعال کن.']);
        }

        $missingWholesalePrice = ProductVariant::query()
            ->whereIn('id', $variantIds)
            ->whereNull('wholesale_price')
            ->exists();

        if ($missingWholesalePrice) {
            throw ValidationException::withMessages(['items' => 'برای همهٔ مدل‌های این پک باید قیمت عمده ثبت شده باشد.']);
        }

        $variantPrices = ProductVariant::query()
            ->whereIn('id', $variantIds)
            ->get(['id', 'wholesale_price'])
            ->keyBy('id');

        $packBasePrice = collect($data['items'])->sum(
            fn (array $item): float => (float) $variantPrices->get((int) $item['variant_id'])->wholesale_price
                * (int) $item['quantity']
        );

        $packBasePrice = round($packBasePrice, 2);
        $data['pack_price'] = $data['pack_price'] ?? $packBasePrice;

        if ((float) $data['pack_price'] < 1 || (float) $data['pack_price'] > $packBasePrice) {
            throw ValidationException::withMessages(['pack_price' => 'قیمت پک باید مثبت و حداکثر برابر جمع قیمت عمدهٔ اقلام باشد.']);
        }

        $data['slug'] = filled($data['slug'] ?? null)
            ? Str::slug($data['slug'])
            : Str::slug($data['name']) . '-' . Str::lower(Str::random(5));

        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);
        $data['is_active'] = $request->boolean('is_active', true);
        $data['items'] = collect($data['items'])
            ->map(fn (array $item) => [
                'product_variant_id' => (int) $item['variant_id'],
                'quantity' => (int) $item['quantity'],
            ])
            ->values()
            ->all();

        return $data;
    }
}
