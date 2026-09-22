<?php

namespace App\Http\Requests\Admin;

use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'customer_note' => $this->trimValue($this->input('customer_note')),
            'tracking_code' => $this->trimValue($this->input('tracking_code')),
        ]);
    }

    public function rules(): array
    {
        return [
            'status' => [
                'bail',
                'required',
                Rule::in(Order::STATUSES),
            ],

            'payment_status' => [
                'required',
                Rule::in(Order::PAYMENT_STATUSES),
            ],

            'customer_note' => [
                'nullable',
                'string',
                'max:2000',
            ],

            'tracking_code' => [
                'nullable',
                'string',
                'max:255',
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'status' => 'وضعیت سفارش',
            'payment_status' => 'وضعیت پرداخت',
            'customer_note' => 'یادداشت مشتری',
            'tracking_code' => 'کد رهگیری',
        ];
    }

    private function trimValue(mixed $value): mixed
    {
        return is_string($value)
            ? trim($value)
            : $value;
    }
}
