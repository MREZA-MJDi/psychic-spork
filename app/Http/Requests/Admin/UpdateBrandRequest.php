<?php

namespace App\Http\Requests\Admin;

use App\Models\Brand;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBrandRequest extends FormRequest
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
        $brand = $this->route('brand');

        $brandId = $brand instanceof Brand
            ? $brand->getKey()
            : $brand;

        return [
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
                Rule::unique('brands', 'slug')
                    ->ignore($brandId),
            ],

            'description' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'logo_file' => [
                'nullable',
                'image',
                'mimes:jpeg,jpg,png,webp',
                'max:1024',
                'dimensions:max_width=800,max_height=800',
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
            'name' => 'نام برند',
            'slug' => 'اسلاگ برند',
            'description' => 'توضیحات',
            'logo_file' => 'لوگوی برند',
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
