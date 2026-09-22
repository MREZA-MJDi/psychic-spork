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

        if (is_string($this->input('identifier'))) {
            $data['identifier'] = trim($this->input('identifier'));
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
            'identifier' => [
                'bail',
                'required',
                'string',
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
            'identifier.required' => 'وارد کردن :attribute الزامی است.',
            'identifier.max' => 'مقدار :attribute بیش از حد مجاز است.',
            'password.required' => 'وارد کردن :attribute الزامی است.',
            'password.string' => ':attribute نامعتبر است.',
        ];
    }

    public function attributes(): array
    {
        return [
            'identifier' => 'ایمیل یا نام کاربری',
            'password' => 'رمز عبور',
            'remember' => 'مرا به خاطر بسپار',
        ];
    }
}
