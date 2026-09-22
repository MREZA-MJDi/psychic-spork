<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

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
                     'email',
                 ] as $field) {
            if (is_string($this->input($field))) {
                $data[$field] = trim($this->input($field));
            }
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

            'email' => [
                'bail',
                'required',
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

            'email.required' =>
                'وارد کردن :attribute الزامی است.',

            'email.email' =>
                'فرمت ایمیل نامعتبر است.',

            'email.max' =>
                'مقدار :attribute بیش از حد مجاز است.',

            'email.unique' =>
                'این ایمیل قبلاً ثبت شده است.',

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
            'email' => 'ایمیل',
            'password' => 'رمز عبور',
            'password_confirmation' => 'تکرار رمز عبور',
        ];
    }
}
