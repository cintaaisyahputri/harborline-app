<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\WarehouseResource;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
    public function index(Request $request)
    {
        $warehouses = Warehouse::query()
            ->with('manager:id,name')
            ->withSum('inventoryItems as stock_kg', 'quantity_kg')
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->string('type')))
            ->orderBy('name')
            ->paginate($request->integer('per_page', 20));

        return WarehouseResource::collection($warehouses);
    }

    public function store(Request $request)
    {
        if (! $request->user()->hasRole('admin')) {
            return response()->json(['message' => 'Only administrators can register new warehouses.'], 403);
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'type' => 'required|in:cold_storage,dry_storage,processing',
            'capacity_tons' => 'nullable|integer|min:0',
            'manager_id' => 'nullable|exists:users,id',
        ]);

        $warehouse = Warehouse::create($data);
        $warehouse->load('manager:id,name');

        return (new WarehouseResource($warehouse))->response()->setStatusCode(201);
    }

    public function show(Warehouse $warehouse)
    {
        $warehouse->load(['manager:id,name', 'inventoryItems']);

        return new WarehouseResource($warehouse);
    }

    public function update(Request $request, Warehouse $warehouse)
    {
        if (! $request->user()->hasRole('admin')) {
            return response()->json(['message' => 'Only administrators can edit warehouses.'], 403);
        }

        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'location' => 'sometimes|string|max:255',
            'type' => 'sometimes|in:cold_storage,dry_storage,processing',
            'capacity_tons' => 'nullable|integer|min:0',
            'manager_id' => 'nullable|exists:users,id',
        ]);

        $warehouse->update($data);
        $warehouse->load('manager:id,name');

        return new WarehouseResource($warehouse);
    }

    public function destroy(Request $request, Warehouse $warehouse)
    {
        if (! $request->user()->hasRole('admin')) {
            return response()->json(['message' => 'Only administrators can remove warehouses.'], 403);
        }

        if ($warehouse->inventoryItems()->exists()) {
            return response()->json(['message' => 'Cannot remove a warehouse that still holds inventory.'], 422);
        }

        $warehouse->delete();

        return response()->json(['message' => "{$warehouse->name} removed."]);
    }
}
