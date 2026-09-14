<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\InventoryItem;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    /**
     * GET /api/orders
     * Buyers see only their own orders; staff can see everything and filter by status/warehouse.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $orders = Order::query()
            ->with(['buyer:id,name', 'warehouse:id,name', 'items.inventoryItem:id,name,sku'])
            ->when(! $user->hasRole('admin', 'warehouse', 'fleet_manager'), fn ($q) => $q->where('buyer_id', $user->id))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('warehouse_id'), fn ($q) => $q->where('warehouse_id', $request->integer('warehouse_id')))
            ->latest()
            ->paginate($request->integer('per_page', 15));

        return OrderResource::collection($orders);
    }

    /**
     * POST /api/orders
     * Locks the requested inventory rows, checks stock is sufficient, decrements it,
     * and creates the order + line items atomically. Whole request fails together
     * if any single line can't be fulfilled -- no partial orders.
     */
    public function store(StoreOrderRequest $request)
    {
        $order = DB::transaction(function () use ($request) {
            $order = Order::create([
                'buyer_id' => $request->user()->id,
                'warehouse_id' => $request->integer('warehouse_id'),
                'status' => 'pending',
                'notes' => $request->input('notes'),
            ]);

            foreach ($request->input('items') as $line) {
                /** @var InventoryItem $item */
                $item = InventoryItem::where('id', $line['inventory_item_id'])
                    ->where('warehouse_id', $order->warehouse_id)
                    ->lockForUpdate()
                    ->first();

                if (! $item) {
                    throw ValidationException::withMessages([
                        'items' => ["Item #{$line['inventory_item_id']} does not belong to warehouse #{$order->warehouse_id}."],
                    ]);
                }

                $quantity = (float) $line['quantity_kg'];

                if ($item->quantity_kg < $quantity) {
                    throw ValidationException::withMessages([
                        'items' => ["Only {$item->quantity_kg}kg of {$item->name} is available, {$quantity}kg requested."],
                    ]);
                }

                $order->items()->create([
                    'inventory_item_id' => $item->id,
                    'quantity_kg' => $quantity,
                    'unit_price' => $item->unit_price,
                    'subtotal' => round($quantity * $item->unit_price, 2),
                ]);

                $item->decrement('quantity_kg', $quantity);
            }

            $order->update(['status' => 'confirmed']);
            $order->recalculateTotal();

            return $order;
        });

        $order->load(['buyer:id,name', 'warehouse:id,name', 'items.inventoryItem:id,name,sku']);

        return (new OrderResource($order))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, Order $order)
    {
        $this->authorizeView($request, $order);

        $order->load(['buyer:id,name', 'warehouse:id,name', 'items.inventoryItem:id,name,sku']);

        return new OrderResource($order);
    }

    /**
     * PATCH /api/orders/{order}
     * Status transitions only: confirmed -> fulfilled, pending/confirmed -> cancelled.
     * Cancelling restores stock to the warehouse.
     */
    public function update(Request $request, Order $order)
    {
        $data = $request->validate([
            'status' => 'required|in:fulfilled,cancelled',
        ]);

        if (! $request->user()->hasRole('admin', 'warehouse')) {
            return response()->json(['message' => 'Only warehouse staff can update order status.'], 403);
        }

        if (in_array($order->status, ['cancelled', 'fulfilled'], true)) {
            return response()->json(['message' => "Order is already {$order->status} and cannot be changed."], 422);
        }

        DB::transaction(function () use ($order, $data) {
            if ($data['status'] === 'cancelled') {
                foreach ($order->items as $line) {
                    $line->inventoryItem?->increment('quantity_kg', $line->quantity_kg);
                }
                $order->update(['status' => 'cancelled']);
            } else {
                $order->update(['status' => 'fulfilled', 'fulfilled_at' => now()]);
            }
        });

        $order->load(['buyer:id,name', 'warehouse:id,name', 'items.inventoryItem:id,name,sku']);

        return new OrderResource($order);
    }

    /**
     * DELETE /api/orders/{order}
     * Only pending orders can be deleted outright; anything confirmed should be
     * cancelled instead (via update) so stock is restored and a record remains.
     */
    public function destroy(Order $order)
    {
        if ($order->status !== 'pending') {
            return response()->json([
                'message' => 'Only pending orders can be deleted. Cancel confirmed orders instead so stock is restored.',
            ], 422);
        }

        $order->delete();

        return response()->json(['message' => "Order {$order->order_number} deleted."]);
    }

    /**
     * GET /api/orders/{order}/receipt
     * Itemised "struk belanja" for a confirmed/fulfilled order: buyer, warehouse,
     * line items, total, plus a 30-minute signed link to the printable HTML
     * version (open that link in a browser and print/save as PDF -- no
     * frontend or PDF library required).
     */
    public function receipt(Request $request, Order $order)
    {
        $this->authorizeView($request, $order);

        if ($order->status === 'cancelled') {
            return response()->json([
                'message' => 'Cancelled orders have no receipt.',
            ], 422);
        }

        $order->load(['buyer:id,name,email', 'warehouse:id,name,location', 'items.inventoryItem:id,name,sku']);

        return response()->json([
            'receipt_number' => $order->order_number,
            'status' => $order->status,
            'issued_at' => now()->toIso8601String(),
            'buyer' => [
                'name' => $order->buyer->name,
                'email' => $order->buyer->email,
            ],
            'warehouse' => [
                'name' => $order->warehouse->name,
                'location' => $order->warehouse->location,
            ],
            'items' => $order->items->map(fn ($line) => [
                'name' => $line->inventoryItem?->name,
                'sku' => $line->inventoryItem?->sku,
                'quantity_kg' => (float) $line->quantity_kg,
                'unit_price' => (float) $line->unit_price,
                'subtotal' => (float) $line->subtotal,
            ]),
            'total_amount' => (float) $order->total_amount,
            'fulfilled_at' => $order->fulfilled_at,
            'print_url' => URL::temporarySignedRoute(
                'receipts.print',
                now()->addMinutes(30),
                ['order' => $order->id]
            ),
        ]);
    }

    private function authorizeView(Request $request, Order $order): void
    {
        $user = $request->user();

        abort_unless(
            $user->hasRole('admin', 'warehouse', 'fleet_manager') || $order->buyer_id === $user->id,
            403,
            'You do not have access to this order.'
        );
    }
}
