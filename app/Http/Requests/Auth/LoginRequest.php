<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return ! auth()->check();
    }

    protected function prepareForValidation(): void
    {
        $data = [];

        if (is_string($this->input('email'))) {
            $data['email'] = trim($this->input('email'));
        }

        if ($this->has('remember')) {
            $data['remember'] = $this->boolean('remember');
        }

        if ($data !== []) {
            $this->merge($data);
        }
    }

    public function rules(): array
    {
        return [
            'email' => [
                'bail',
                'required',
                'email',
                'max:255',
            ],

            'password' => [
                'bail',
                'required',
                'string',
            ],

            'remember' => [
                'nullable',
                'boolean',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' =>
                'وارد کردن :attribute الزامی است.',

            'email.email' =>
                'فرمت ایمیل نامعتبر است.',

            'email.max' =>
                'مقدار :attribute بیش از حد مجاز است.',

            'password.required' =>
                'وارد کردن :attribute الزامی است.',

            'password.string' =>
                ':attribute نامعتبر است.',
        ];
    }

    public function attributes(): array
    {
        return [
            'email' => 'ایمیل',
            'password' => 'رمز عبور',
            'remember' => 'مرا به خاطر بسپار',
        ];
    }
}
