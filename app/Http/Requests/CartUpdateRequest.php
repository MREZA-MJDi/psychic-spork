<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CartUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'quantity' => [
                'required',
                'integer',
                'min:0',
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
            'quantity.required' => 'تعداد الزامی است.',
            'quantity.integer' => 'تعداد باید عدد صحیح باشد.',
            'quantity.min' => 'تعداد نمی‌تواند منفی باشد.',
            'quantity.max' => 'تعداد انتخاب‌شده بیش از حد مجاز است.',
        ];
    }
}
