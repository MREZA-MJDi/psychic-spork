<?php

namespace App\Http\Requests\Admin;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => $this->trimValue($this->input('name')),
            'slug' => $this->trimValue($this->input('slug')),
            'description' => $this->trimValue($this->input('description')),
        ]);
    }

    public function rules(): array
    {
        $category = $this->route('category');

        $categoryId = $category instanceof Category
            ? $category->getKey()
            : $category;

        return [
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('categories', 'id')
                    ->whereNull('deleted_at')
                    ->where('id', '!=', $categoryId),
            ],

            'name' => [
                'bail',
                'required',
                'string',
                'max:120',
            ],

            'slug' => [
                'bail',
                'nullable',
                'string',
                'max:160',
                Rule::unique('categories', 'slug')
                    ->ignore($categoryId),
            ],

            'description' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'image_file' => [
                'nullable',
                'image',
                'mimes:jpeg,jpg,png,webp',
                'max:2048',
                'dimensions:max_width=1600,max_height=1200',
            ],

            'sort_order' => [
                'nullable',
                'integer',
                'min:0',
                'max:9999',
            ],

            'is_active' => [
                'nullable',
                'boolean',
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'parent_id' => 'دسته‌بندی والد',
            'name' => 'نام دسته‌بندی',
            'slug' => 'اسلاگ دسته‌بندی',
            'description' => 'توضیحات',
            'image_file' => 'تصویر دسته‌بندی',
            'sort_order' => 'ترتیب نمایش',
            'is_active' => 'وضعیت فعال بودن',
        ];
    }

    private function trimValue(mixed $value): mixed
    {
        return is_string($value)
            ? trim($value)
            : $value;
    }
}
