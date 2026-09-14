<?php

namespace Database\Seeders;

use App\Models\ComplianceCertificate;
use App\Models\InventoryItem;
use App\Models\Order;
use App\Models\User;
use App\Models\Vessel;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // One login per role, fixed credentials, for demoing/Postman.
        $admin = User::factory()->create([
            'name' => 'Admin Harborline',
            'email' => 'admin@harborline.test',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        $fleetManager = User::factory()->create([
            'name' => 'Budi Santoso',
            'email' => 'fleet@harborline.test',
            'password' => Hash::make('password'),
            'role' => 'fleet_manager',
        ]);

        $warehouseStaff = User::factory()->create([
            'name' => 'Siti Rahayu',
            'email' => 'warehouse@harborline.test',
            'password' => Hash::make('password'),
            'role' => 'warehouse',
        ]);

        $buyer = User::factory()->create([
            'name' => 'Restu Prasetyo',
            'email' => 'buyer@harborline.test',
            'password' => Hash::make('password'),
            'role' => 'buyer',
        ]);

        // Fleet
        $vessels = Vessel::factory()->count(6)->create();

        // Warehouses, each with its own inventory
        $warehouses = Warehouse::factory()
            ->count(3)
            ->create(['manager_id' => $warehouseStaff->id]);

        $warehouses->each(function (Warehouse $warehouse) {
            InventoryItem::factory()->count(rand(5, 9))->create([
                'warehouse_id' => $warehouse->id,
            ]);
        });

        // A couple of intentionally low-stock items so /inventory?low_stock=1 has results
        InventoryItem::factory()->count(2)->create([
            'warehouse_id' => $warehouses->first()->id,
            'quantity_kg' => 8,
            'reorder_threshold_kg' => 50,
        ]);

        // Compliance certificates -- mix of valid, expiring soon, and expired
        foreach ($vessels->take(4) as $vessel) {
            ComplianceCertificate::factory()->create(['vessel_id' => $vessel->id]);
        }
        foreach ($warehouses as $warehouse) {
            ComplianceCertificate::factory()->create(['warehouse_id' => $warehouse->id]);
        }
        // One expired certificate, to exercise the "cleared_to_sail" flag
        ComplianceCertificate::factory()->create([
            'vessel_id' => $vessels->last()->id,
            'issued_at' => now()->subYears(2),
            'expires_at' => now()->subDays(10),
        ]);
        // One expiring-soon certificate
        ComplianceCertificate::factory()->create([
            'vessel_id' => $vessels->first()->id,
            'issued_at' => now()->subMonths(11),
            'expires_at' => now()->addDays(12),
        ]);

        // A sample order, placed the same way the API would (decrements stock)
        $warehouse = $warehouses->first();
        $item = $warehouse->inventoryItems()->first();

        if ($item) {
            $order = Order::create([
                'buyer_id' => $buyer->id,
                'warehouse_id' => $warehouse->id,
                'status' => 'confirmed',
                'notes' => 'Weekly restock for Pasar Ikan Modern stall #12.',
            ]);

            $quantity = min(20, (float) $item->quantity_kg);

            $order->items()->create([
                'inventory_item_id' => $item->id,
                'quantity_kg' => $quantity,
                'unit_price' => $item->unit_price,
                'subtotal' => round($quantity * $item->unit_price, 2),
            ]);

            $item->decrement('quantity_kg', $quantity);
            $order->recalculateTotal();
        }

        $this->command?->info('Seeded: admin@harborline.test / fleet@harborline.test / warehouse@harborline.test / buyer@harborline.test (all password: "password")');
    }
}
