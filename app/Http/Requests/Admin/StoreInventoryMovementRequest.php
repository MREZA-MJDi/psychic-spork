<?php

namespace App\Http\Requests\Admin;

use App\Models\InventoryMovement;
use Illuminate\Validation\Rule;

class StoreInventoryMovementRequest extends AdminFormRequest
{
    protected function prepareForValidation(): void
    {
        $this->trimStrings(['note']);
    }

    public function rules(): array
    {
        return [
            'product_variant_id' => [
                'bail',
                'required',
                'integer',
                Rule::exists('product_variants', 'id')->whereNull('deleted_at'),
            ],
            'type' => ['required', Rule::in(InventoryMovement::TYPES)],
            'quantity' => ['bail', 'required', 'integer', 'not_in:0', 'min:-2147483648', 'max:2147483647'],
            'note' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function attributes(): array
    {
        return [
            'product_variant_id' => 'مدل محصول',
            'type' => 'نوع گردش موجودی',
            'quantity' => 'مقدار',
            'note' => 'یادداشت',
        ];
    }
}
