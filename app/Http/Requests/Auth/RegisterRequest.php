<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use App\Support\IranianMobileNumber;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return ! auth()->check();
    }

    protected function prepareForValidation(): void
    {
        $data = [];

        foreach ([
                     'name',
                 ] as $field) {
            if (is_string($this->input($field))) {
                $data[$field] = trim($this->input($field));
            }
        }

        if (is_string($this->input('phone'))) {
            $data['phone'] = IranianMobileNumber::normalize($this->input('phone'));
        }

        if ($data !== []) {
            $this->merge($data);
        }
    }

    public function rules(): array
    {
        return [
            'name' => [
                'bail',
                'required',
                'string',
                'max:120',
            ],

            'phone' => [
                'bail',
                'required',
                'string',
                'regex:/^09\d{9}$/',
                Rule::unique('users', 'phone'),
            ],

            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('users', 'email'),
            ],

            'password' => [
                'bail',
                'required',
                'confirmed',
                Password::min(8),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' =>
                'وارد کردن :attribute الزامی است.',

            'name.string' =>
                ':attribute باید متنی باشد.',

            'name.max' =>
                'مقدار :attribute بیش از حد مجاز است.',

            'phone.required' => 'وارد کردن :attribute الزامی است.',
            'phone.regex' => 'شماره موبایل معتبر وارد کنید.',
            'phone.unique' => 'این شماره موبایل قبلاً ثبت شده است.',
            'email.email' =>
                'فرمت ایمیل نامعتبر است.',

            'email.max' =>
                'مقدار :attribute بیش از حد مجاز است.',

            'email.unique' => 'این ایمیل قبلاً ثبت شده است.',

            'password.required' =>
                'وارد کردن :attribute الزامی است.',

            'password.confirmed' =>
                'تکرار رمز عبور با رمز عبور یکسان نیست.',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'نام',
            'phone' => 'شماره موبایل',
            'email' => 'ایمیل (اختیاری)',
            'password' => 'رمز عبور',
            'password_confirmation' => 'تکرار رمز عبور',
        ];
    }
}
