<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInventoryItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('warehouse');
    }

    public function rules(): array
    {
        return [
            'warehouse_id' => 'required|exists:warehouses,id',
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:100|unique:inventory_items,sku',
            'category' => 'required|in:frozen,chilled,live,dry',
            'species' => 'nullable|string|max:255',
            'quantity_kg' => 'required|numeric|min:0',
            'reorder_threshold_kg' => 'nullable|numeric|min:0',
            'unit_price' => 'required|numeric|min:0',
            'caught_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after_or_equal:caught_at',
        ];
    }
}
