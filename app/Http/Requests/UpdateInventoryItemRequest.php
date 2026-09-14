<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateInventoryItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('warehouse');
    }

    public function rules(): array
    {
        $item = $this->route('inventory');

        return [
            'warehouse_id' => 'sometimes|exists:warehouses,id',
            'name' => 'sometimes|string|max:255',
            'sku' => [
                'sometimes', 'string', 'max:100',
                Rule::unique('inventory_items', 'sku')->ignore($item),
            ],
            'category' => 'sometimes|in:frozen,chilled,live,dry',
            'species' => 'nullable|string|max:255',
            'quantity_kg' => 'sometimes|numeric|min:0',
            'reorder_threshold_kg' => 'nullable|numeric|min:0',
            'unit_price' => 'sometimes|numeric|min:0',
            'caught_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after_or_equal:caught_at',
        ];
    }
}
