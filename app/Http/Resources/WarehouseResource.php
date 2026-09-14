<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WarehouseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'location' => $this->location,
            'type' => $this->type,
            'capacity_tons' => $this->capacity_tons,
            'manager' => $this->whenLoaded('manager', fn () => [
                'id' => $this->manager->id,
                'name' => $this->manager->name,
            ]),
            'current_stock_kg' => isset($this->stock_kg)
                ? (float) $this->stock_kg
                : $this->whenLoaded('inventoryItems', fn () => (float) $this->inventoryItems->sum('quantity_kg')),
            'created_at' => $this->created_at,
        ];
    }
}
