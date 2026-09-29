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
        $this->merge([
            'payment_method' => $this->input('payment_method', 'online'),
            'order_type' => $this->input('order_type', 'retail'),
        ]);

        foreach ([
                     'customer_name',
                     'customer_phone',
                     'customer_email',
                     'shipping_address',
                     'shipping_province',
                     'shipping_city',
                     'postal_code',
                     'customer_note',
                     'bank_name',
                     'account_holder',
                     'cheque_number',
                     'sayad_id',
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
            'payment_method' => [
                'required',
                Rule::in(['online', 'cheque']),
            ],

            'order_type' => [
                'required',
                Rule::in(['retail', 'wholesale']),
            ],

            'sayad_id' => [
                'required_if:payment_method,cheque',
                'nullable',
                'digits:16',
            ],

            'cheque_number' => [
                'required_if:payment_method,cheque',
                'nullable',
                'string',
                'max:100',
            ],

            'bank_name' => [
                'required_if:payment_method,cheque',
                'nullable',
                'string',
                'max:120',
            ],

            'account_holder' => [
                'nullable',
                'string',
                'max:160',
            ],

            'due_date' => [
                'required_if:payment_method,cheque',
                'nullable',
                'date',
                'after_or_equal:today',
            ],

            'cheque_image' => [
                'nullable',
                'required_if:payment_method,cheque',
                'file',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],

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

            'payment_method.in' => 'روش پرداخت نامعتبر است.',

            'order_type.in' => 'نوع سفارش نامعتبر است.',

            'sayad_id.digits' => 'شناسه صیادی باید ۱۶ رقم باشد.',

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
            'payment_method' => 'روش پرداخت',
            'order_type' => 'نوع سفارش',
            'sayad_id' => 'شناسه صیادی',
            'cheque_number' => 'شماره چک',
            'bank_name' => 'بانک',
            'account_holder' => 'صاحب حساب',
            'due_date' => 'تاریخ سررسید',
            'cheque_image' => 'تصویر چک',
        ];
    }
}
