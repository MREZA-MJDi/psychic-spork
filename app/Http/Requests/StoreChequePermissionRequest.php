<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreChequePermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isCustomer() ?? false;
    }

    protected function prepareForValidation(): void
    {
        $value = $this->input('requested_amount');
        if (! is_string($value) && ! is_numeric($value)) {
            return;
        }

        $digits = strtr((string) $value, [
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
            '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
        ]);

        $this->merge(['requested_amount' => preg_replace('/[^0-9.\-]/', '', $digits)]);
    }

    public function rules(): array
    {
        return ['requested_amount' => ['required', 'numeric', 'min:1', 'max:999999999999.99']];
    }

    public function attributes(): array
    {
        return ['requested_amount' => 'مبلغ اعتبار درخواستی'];
    }
}
