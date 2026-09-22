<?php

namespace App\Http\Requests\Admin;

use App\Models\ProductVariant;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductVariantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'sku' => $this->trimValue($this->input('sku')),
            'size' => $this->trimValue($this->input('size')),
            'color' => $this->trimValue($this->input('color')),
            'color_code' => $this->trimValue($this->input('color_code')),
        ]);
    }

    public function rules(): array
    {
        $variant = $this->route('variant')
            ?? $this->route('productVariant');

        $variantId = $variant instanceof ProductVariant
            ? $variant->getKey()
            : $variant;

        return [
            'product_id' => [
                'bail',
                'required',
                'integer',
                Rule::exists('products', 'id')
                    ->whereNull('deleted_at'),
            ],

            'sku' => [
                'bail',
                'required',
                'string',
                'max:120',
                Rule::unique('product_variants', 'sku')
                    ->ignore($variantId),
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

            'is_active' => [
                'nullable',
                'boolean',
            ],

            'sort_order' => [
                'nullable',
                'integer',
                'min:0',
                'max:9999',
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'product_id' => 'محصول',
            'sku' => 'SKU',
            'size' => 'سایز',
            'color' => 'رنگ',
            'color_code' => 'کد رنگ',
            'price' => 'قیمت',
            'sale_price' => 'قیمت فروش ویژه',
            'stock' => 'موجودی',
            'low_stock_threshold' => 'حد هشدار موجودی',
            'is_active' => 'وضعیت فعال بودن',
            'sort_order' => 'ترتیب نمایش',
        ];
    }

    private function trimValue(mixed $value): mixed
    {
        return is_string($value)
            ? trim($value)
            : $value;
    }
}
