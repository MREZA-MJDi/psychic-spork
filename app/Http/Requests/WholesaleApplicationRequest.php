<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WholesaleApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isCustomer();
    }

    protected function prepareForValidation(): void
    {
        foreach ([
            'business_name',
            'business_type',
            'business_phone',
            'business_address',
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
            'business_name' => [
                'required',
                'string',
                'max:160',
            ],
            'business_type' => [
                'nullable',
                'string',
                'max:120',
            ],
            'business_phone' => [
                'nullable',
                'string',
                'max:30',
            ],
            'business_address' => [
                'nullable',
                'string',
                'max:3000',
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'business_name' => 'نام فروشگاه یا مجموعه',
            'business_type' => 'نوع فعالیت',
            'business_phone' => 'شماره تماس کاری',
            'business_address' => 'آدرس کاری',
        ];
    }
}
