<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventoryItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'warehouse_id' => $this->warehouse_id,
            'warehouse_name' => $this->whenLoaded('warehouse', fn () => $this->warehouse->name),
            'name' => $this->name,
            'sku' => $this->sku,
            'category' => $this->category,
            'species' => $this->species,
            'quantity_kg' => (float) $this->quantity_kg,
            'reorder_threshold_kg' => (float) $this->reorder_threshold_kg,
            'low_stock' => $this->isLowStock(),
            'unit_price' => (float) $this->unit_price,
            'caught_at' => $this->caught_at,
            'expires_at' => $this->expires_at,
            'created_at' => $this->created_at,
        ];
    }
}
