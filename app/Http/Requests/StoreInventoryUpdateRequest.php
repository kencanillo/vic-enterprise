<?php

namespace App\Http\Requests;

use App\Enums\InventoryMovementType;
use App\Enums\InventoryQuantityType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInventoryUpdateRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()->can('create', \App\Models\InventoryTransaction::class); }
    public function rules(): array
    {
        return [
            'warehouse_id' => ['required','exists:warehouses,id'],
            'product_id' => ['required','exists:products,id'],
            'quantity_type' => ['required', Rule::in(InventoryQuantityType::all())],
            'movement_type' => ['required', Rule::in(InventoryMovementType::all())],
            'quantity' => ['required','numeric'],
            'remarks' => ['nullable','string'],
        ];
    }
}