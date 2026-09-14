<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'inventory_item_id' => $this->inventory_item_id,
            'item_name' => $this->whenLoaded('inventoryItem', fn () => $this->inventoryItem->name),
            'sku' => $this->whenLoaded('inventoryItem', fn () => $this->inventoryItem->sku),
            'quantity_kg' => (float) $this->quantity_kg,
            'unit_price' => (float) $this->unit_price,
            'subtotal' => (float) $this->subtotal,
        ];
    }
}
