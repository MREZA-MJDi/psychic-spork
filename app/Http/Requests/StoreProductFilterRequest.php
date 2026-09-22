<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        foreach ([
                     'q',
                     'category',
                     'brand',
                 ] as $field) {
            if (is_string($this->input($field))) {
                $this->merge([
                    $field => trim($this->input($field)),
                ]);
            }
        }
    }

    public function rules(): array
    {
        return [
            'q' => [
                'nullable',
                'string',
                'max:120',
            ],

            'category' => [
                'nullable',
                'string',
                'max:160',
            ],

            'brand' => [
                'nullable',
                'string',
                'max:160',
            ],

            'page' => [
                'nullable',
                'integer',
                'min:1',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'q.string' =>
                'مقدار :attribute نامعتبر است.',

            'category.string' =>
                'مقدار :attribute نامعتبر است.',

            'brand.string' =>
                'مقدار :attribute نامعتبر است.',

            'q.max' =>
                'مقدار :attribute بیش از حد مجاز است.',

            'category.max' =>
                'مقدار :attribute بیش از حد مجاز است.',

            'brand.max' =>
                'مقدار :attribute بیش از حد مجاز است.',

            'page.integer' =>
                'صفحه باید عدد صحیح باشد.',

            'page.min' =>
                'شماره صفحه نامعتبر است.',
        ];
    }

    public function attributes(): array
    {
        return [
            'q' => 'جستجو',
            'category' => 'دسته‌بندی',
            'brand' => 'برند',
            'page' => 'صفحه',
        ];
    }
}
