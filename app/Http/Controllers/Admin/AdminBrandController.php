<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\StoreBrandRequest;
use App\Http\Requests\Admin\UpdateBrandRequest;
use App\Models\Brand;
use App\Services\MediaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class AdminBrandController extends AdminController
{
    public function index(Request $request): View
    {
        $brands = Brand::query()
            ->with('logoMedia')
            ->withCount('products')
            ->when(
                $request->filled('q'),
                fn ($query) => $query->where(function ($query) use ($request) {
                    $search = $request->string('q')->toString();

                    $query
                        ->where('name', 'like', '%' . $search . '%')
                        ->orWhere('slug', 'like', '%' . $search . '%');
                })
            )
            ->when(
                $request->has('active') && $request->input('active') !== '',
                fn ($query) => $query->where(
                    'is_active',
                    $request->boolean('active')
                )
            )
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.brands.index', compact('brands'));
    }

    public function create(): View
    {
        return view('admin.brands.create', [
            'brand' => new Brand(),
        ]);
    }

    public function store(
        StoreBrandRequest $request,
        MediaService $media
    ): RedirectResponse {
        try {
            $data = $request->validated();

            $brand = DB::transaction(function () use ($data, $request, $media) {
                $brand = Brand::create([
                    'name' => $data['name'],
                    'slug' => filled($data['slug'] ?? null)
                        ? $data['slug']
                        : Str::slug($data['name']),
                    'description' => $data['description'] ?? null,
                    'is_active' => $request->boolean('is_active'),
                ]);

                if ($request->hasFile('logo_file')) {
                    $media->attach(
                        $brand,
                        'logo',
                        $request->file('logo_file'),
                        'brands',
                        $brand->name
                    );
                }

                return $brand;
            });

            return redirect()
                ->route('admin.brands.edit', $brand)
                ->with('success', 'برند با موفقیت ایجاد شد.');
        } catch (Throwable $e) {
            return $this->failure(
                $e,
                'ایجاد برند انجام نشد.'
            );
        }
    }

    public function edit(Brand $brand): View
    {
        $brand->load('logoMedia');

        return view('admin.brands.edit', compact('brand'));
    }

    public function update(
        UpdateBrandRequest $request,
        Brand $brand,
        MediaService $media
    ): RedirectResponse {
        try {
            $data = $request->validated();

            DB::transaction(function () use ($brand, $data, $request, $media) {
                $brand->update([
                    'name' => $data['name'],
                    'slug' => filled($data['slug'] ?? null)
                        ? $data['slug']
                        : Str::slug($data['name']),
                    'description' => $data['description'] ?? null,
                    'is_active' => $request->boolean('is_active'),
                ]);

                if ($request->hasFile('logo_file')) {
                    $media->replace(
                        $brand,
                        'logo',
                        $request->file('logo_file'),
                        'brands',
                        $brand->name
                    );
                }
            });

            return redirect()
                ->route('admin.brands.index')
                ->with('success', 'برند با موفقیت به‌روزرسانی شد.');
        } catch (Throwable $e) {
            return $this->failure(
                $e,
                'به‌روزرسانی برند انجام نشد.'
            );
        }
    }

    public function destroy(
        Brand $brand,
        MediaService $media
    ): RedirectResponse {
        try {
            if ($brand->products()->exists()) {
                abort(
                    422,
                    'این برند هنوز محصول دارد و قابل حذف نیست.'
                );
            }

            DB::transaction(function () use ($brand, $media) {
                $media->removeCollection($brand, 'logo');

                $brand->delete();
            });

            return redirect()
                ->route('admin.brands.index')
                ->with('success', 'برند با موفقیت حذف شد.');
        } catch (Throwable $e) {
            return $this->failure(
                $e,
                'حذف برند انجام نشد.'
            );
        }
    }
}
