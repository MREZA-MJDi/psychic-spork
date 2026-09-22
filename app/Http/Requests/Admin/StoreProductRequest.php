<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && (bool) auth()->user()->is_admin;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => $this->trimValue($this->input('name')),
            'slug' => $this->trimValue($this->input('slug')),
            'short_description' => $this->trimValue($this->input('short_description')),
            'description' => $this->trimValue($this->input('description')),
            'sku' => $this->trimValue($this->input('sku')),
            'size' => $this->trimValue($this->input('size')),
            'color' => $this->trimValue($this->input('color')),
            'color_code' => $this->trimValue($this->input('color_code')),
        ]);
    }

    public function rules(): array
    {
        return [
            'category_id' => [
                'nullable',
                'integer',
                Rule::exists('categories', 'id')
                    ->whereNull('deleted_at'),
            ],

            'brand_id' => [
                'nullable',
                'integer',
                Rule::exists('brands', 'id')
                    ->whereNull('deleted_at'),
            ],

            'name' => [
                'bail',
                'required',
                'string',
                'max:180',
            ],

            'slug' => [
                'bail',
                'nullable',
                'string',
                'max:200',
                Rule::unique('products', 'slug'),
            ],

            'short_description' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'description' => [
                'nullable',
                'string',
                'max:10000',
            ],

            'attributes_json' => [
                'nullable',
                'json',
            ],

            'is_active' => [
                'nullable',
                'boolean',
            ],

            'is_featured' => [
                'nullable',
                'boolean',
            ],

            'sort_order' => [
                'nullable',
                'integer',
                'min:0',
                'max:9999',
            ],

            'sku' => [
                'bail',
                'required',
                'string',
                'max:120',
                Rule::unique('product_variants', 'sku'),
            ],

            'size' => [
                'nullable',
                'string',
                'max:80',
            ],

            'color' => [
                'nullable',
                'string',
                'max:80',
            ],

            'color_code' => [
                'nullable',
                'string',
                'max:20',
                'regex:/^#?[0-9A-Fa-f]{3}(?:[0-9A-Fa-f]{3})?$/',
            ],

            'price' => [
                'bail',
                'required',
                'numeric',
                'min:0',
                'max:999999999999.99',
            ],

            'sale_price' => [
                'nullable',
                'numeric',
                'min:0',
                'lte:price',
                'max:999999999999.99',
            ],

            'stock' => [
                'required',
                'integer',
                'min:0',
                'max:2147483647',
            ],

            'low_stock_threshold' => [
                'required',
                'integer',
                'min:0',
                'max:2147483647',
            ],

            'image_file' => [
                'nullable',
                'image',
                'mimes:jpeg,jpg,png,webp',
                'max:2048',
                'dimensions:max_width=1600,max_height=2000',
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'category_id' => 'دسته‌بندی',
            'brand_id' => 'برند',
            'name' => 'نام محصول',
            'slug' => 'اسلاگ محصول',
            'short_description' => 'توضیح کوتاه',
            'description' => 'توضیحات محصول',
            'attributes_json' => 'ویژگی‌های محصول',
            'is_active' => 'وضعیت فعال بودن',
            'is_featured' => 'محصول ویژه',
            'sort_order' => 'ترتیب نمایش',
            'sku' => 'SKU',
            'size' => 'سایز',
            'color' => 'رنگ',
            'color_code' => 'کد رنگ',
            'price' => 'قیمت',
            'sale_price' => 'قیمت فروش ویژه',
            'stock' => 'موجودی',
            'low_stock_threshold' => 'حد هشدار موجودی',
            'image_file' => 'تصویر محصول',
        ];
    }

    private function trimValue(mixed $value): mixed
    {
        return is_string($value)
            ? trim($value)
            : $value;
    }
}
