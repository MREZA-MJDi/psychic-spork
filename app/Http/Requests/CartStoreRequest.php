<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CartStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'quantity' => [
                'nullable',
                'integer',
                'min:1',
                'max:99',
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'quantity' => 'تعداد',
        ];
    }

    public function messages(): array
    {
        return [
            'quantity.integer' => 'تعداد باید عدد صحیح باشد.',
            'quantity.min' => 'تعداد باید حداقل یک عدد باشد.',
            'quantity.max' => 'تعداد انتخاب‌شده بیش از حد مجاز است.',
        ];
    }
}
