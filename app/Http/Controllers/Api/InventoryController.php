<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInventoryItemRequest;
use App\Http\Requests\UpdateInventoryItemRequest;
use App\Http\Resources\InventoryItemResource;
use App\Models\InventoryItem;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    /**
     * GET /api/inventory
     * Filters: warehouse_id, category, low_stock=1, expiring_within=<days>
     */
    public function index(Request $request)
    {
        $items = InventoryItem::query()
            ->with('warehouse:id,name')
            ->when($request->filled('warehouse_id'), fn ($q) => $q->where('warehouse_id', $request->integer('warehouse_id')))
            ->when($request->filled('category'), fn ($q) => $q->where('category', $request->string('category')))
            ->when($request->boolean('low_stock'), fn ($q) => $q->lowStock())
            ->when($request->filled('expiring_within'), fn ($q) => $q->expiringWithin($request->integer('expiring_within')))
            ->orderBy('name')
            ->paginate($request->integer('per_page', 20));

        return InventoryItemResource::collection($items);
    }

    public function store(StoreInventoryItemRequest $request)
    {
        $item = InventoryItem::create($request->validated());
        $item->load('warehouse:id,name');

        return (new InventoryItemResource($item))
            ->response()
            ->setStatusCode(201);
    }

    public function show(InventoryItem $inventory)
    {
        $inventory->load('warehouse:id,name');

        return new InventoryItemResource($inventory);
    }

    public function update(UpdateInventoryItemRequest $request, InventoryItem $inventory)
    {
        $inventory->update($request->validated());
        $inventory->load('warehouse:id,name');

        return new InventoryItemResource($inventory);
    }

    public function destroy(InventoryItem $inventory)
    {
        $inventory->delete();

        return response()->json(['message' => "{$inventory->name} ({$inventory->sku}) removed from inventory."]);
    }
}
