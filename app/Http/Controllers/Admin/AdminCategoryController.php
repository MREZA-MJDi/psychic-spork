<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Http\Requests\Admin\UpdateCategoryRequest;
use App\Models\Category;
use App\Services\MediaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class AdminCategoryController extends AdminController
{
    public function index(Request $request): View
    {
        $categories = Category::query()
            ->with(['coverMedia', 'parent'])
            ->withCount('products')
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($x) =>
                $x->where('name', 'like', '%' . $request->string('q') . '%')
                    ->orWhere('slug', 'like', '%' . $request->string('q') . '%')
            ))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.categories.index', compact('categories'));
    }

    public function create(): View
    {
        $parentCategories = Category::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.categories.create', [
            'category' => new Category(),
            'parentCategories' => $parentCategories,
        ]);
    }

    public function store(StoreCategoryRequest $request, MediaService $media): RedirectResponse
    {
        try {
            $data = $request->validated();

            $category = DB::transaction(function () use ($data, $request, $media) {
                $category = Category::create([
                    'parent_id' => $data['parent_id'] ?? null,
                    'name' => $data['name'],
                    'slug' => filled($data['slug'] ?? null) ? $data['slug'] : Str::slug($data['name']),
                    'description' => $data['description'] ?? null,
                    'meta_title' => $data['meta_title'] ?? null,
                    'meta_description' => $data['meta_description'] ?? null,
                    'sort_order' => (int) ($data['sort_order'] ?? 0),
                    'is_active' => $request->boolean('is_active'),
                ]);

                if ($request->hasFile('image_file')) {
                    $media->attach(
                        $category,
                        'cover',
                        $request->file('image_file'),
                        'categories',
                        $category->name
                    );
                }

                return $category;
            });

            return redirect()
                ->route('admin.categories.edit', $category)
                ->with('success', 'دسته‌بندی با موفقیت ایجاد شد.');
        } catch (Throwable $e) {
            return $this->failure($e, 'ایجاد دسته‌بندی انجام نشد.');
        }
    }

    public function edit(Category $category): View
    {
        $category->load('coverMedia');

        $parentCategories = Category::query()
            ->whereKeyNot($category->id)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.categories.edit', compact('category', 'parentCategories'));
    }

    public function update(UpdateCategoryRequest $request, Category $category, MediaService $media): RedirectResponse
    {
        try {
            DB::transaction(function () use ($category, $request, $media) {
                $data = $request->validated();

                if (!empty($data['parent_id']) && Category::query()
                    ->whereKey($data['parent_id'])
                    ->where('parent_id', $category->id)
                    ->exists()
                ) {
                    abort(422, 'یک دسته‌بندی نمی‌تواند والد مستقیم دسته‌بندی فعلی باشد.');
                }

                $category->update([
                    'parent_id' => $data['parent_id'] ?? null,
                    'name' => $data['name'],
                    'slug' => filled($data['slug'] ?? null) ? $data['slug'] : Str::slug($data['name']),
                    'description' => $data['description'] ?? null,
                    'meta_title' => $data['meta_title'] ?? null,
                    'meta_description' => $data['meta_description'] ?? null,
                    'sort_order' => (int) ($data['sort_order'] ?? 0),
                    'is_active' => $request->boolean('is_active'),
                ]);

                if ($request->hasFile('image_file')) {
                    $media->replace(
                        $category,
                        'cover',
                        $request->file('image_file'),
                        'categories',
                        $category->name
                    );
                }
            });

            return redirect()
                ->route('admin.categories.index')
                ->with('success', 'دسته‌بندی با موفقیت به‌روزرسانی شد.');
        } catch (Throwable $e) {
            return $this->failure($e, 'به‌روزرسانی دسته‌بندی انجام نشد.');
        }
    }

    public function destroy(Category $category, MediaService $media): RedirectResponse
    {
        try {
            if ($category->products()->exists() || $category->children()->exists()) {
                abort(422, 'این دسته‌بندی دارای محصول یا زیر‌دسته است و قابل حذف نیست.');
            }

            DB::transaction(function () use ($category, $media) {
                $media->removeCollection($category, 'cover');
                $category->delete();
            });

            return back()->with('success', 'دسته‌بندی با موفقیت حذف شد.');
        } catch (Throwable $e) {
            return $this->failure($e, 'حذف دسته‌بندی انجام نشد.');
        }
    }
}
