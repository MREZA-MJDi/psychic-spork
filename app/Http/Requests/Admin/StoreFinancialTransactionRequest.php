<?php

namespace App\Http\Requests\Admin;

use App\Models\FinancialTransaction;
use Illuminate\Validation\Rule;

class StoreFinancialTransactionRequest extends AdminFormRequest
{
    protected function prepareForValidation(): void
    {
        $this->trimStrings([
            'category',
            'description',
            'transaction_date',
        ]);
    }

    public function rules(): array
    {
        return [
            'type' => ['bail', 'required', Rule::in(FinancialTransaction::TYPES)],
            'category' => ['bail', 'required', 'string', 'max:80'],
            'amount' => ['bail', 'required', 'numeric', 'gt:0', 'max:999999999999.99'],
            'description' => ['nullable', 'string', 'max:500'],
            'transaction_date' => ['bail', 'required', 'date'],
        ];
    }

    public function attributes(): array
    {
        return [
            'type' => 'نوع تراکنش',
            'category' => 'دسته‌بندی مالی',
            'amount' => 'مبلغ',
            'description' => 'توضیحات',
            'transaction_date' => 'تاریخ تراکنش',
        ];
    }
}
