<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        foreach ([
                     'customer_name',
                     'customer_phone',
                     'customer_email',
                     'shipping_address',
                     'shipping_province',
                     'shipping_city',
                     'postal_code',
                     'customer_note',
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
        $addressRule = Rule::exists('addresses', 'id')
            ->where(function ($query): void {
                $query->where(
                    'user_id',
                    auth()->id()
                );
            });

        return [
            'address_id' => [
                'nullable',
                'integer',
                $addressRule,
            ],

            'customer_name' => [
                'bail',
                'required',
                'string',
                'max:120',
            ],

            'customer_phone' => [
                'bail',
                'required',
                'string',
                'max:30',
            ],

            'customer_email' => [
                'nullable',
                'email',
                'max:255',
            ],

            'shipping_address' => [
                'bail',
                'required',
                'string',
                'max:5000',
            ],

            'shipping_province' => [
                'nullable',
                'string',
                'max:100',
            ],

            'shipping_city' => [
                'nullable',
                'string',
                'max:100',
            ],

            'postal_code' => [
                'nullable',
                'string',
                'max:20',
            ],

            'customer_note' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'address_id.exists' =>
                'آدرس انتخاب‌شده معتبر نیست.',

            'customer_name.required' =>
                'وارد کردن :attribute الزامی است.',

            'customer_name.string' =>
                ':attribute باید متنی باشد.',

            'customer_phone.required' =>
                'وارد کردن :attribute الزامی است.',

            'customer_phone.string' =>
                ':attribute باید متنی باشد.',

            'customer_email.email' =>
                'فرمت ایمیل نامعتبر است.',

            'shipping_address.required' =>
                'وارد کردن :attribute الزامی است.',

            '*.max' =>
                'مقدار :attribute بیشتر از حد مجاز است.',
        ];
    }

    public function attributes(): array
    {
        return [
            'address_id' => 'آدرس',
            'customer_name' => 'نام و نام خانوادگی',
            'customer_phone' => 'شماره تماس',
            'customer_email' => 'ایمیل',
            'shipping_address' => 'آدرس',
            'shipping_province' => 'استان',
            'shipping_city' => 'شهر',
            'postal_code' => 'کد پستی',
            'customer_note' => 'یادداشت سفارش',
        ];
    }
}
