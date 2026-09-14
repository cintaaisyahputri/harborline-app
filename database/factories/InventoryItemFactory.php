<?php

namespace Database\Factories;

use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

class InventoryItemFactory extends Factory
{
    public function definition(): array
    {
        $species = $this->faker->randomElement([
            'Skipjack Tuna', 'Yellowfin Tuna', 'Grouper', 'Snapper', 'Milkfish', 'Squid', 'Shrimp', 'Mackerel',
        ]);

        $quantity = $this->faker->randomFloat(2, 20, 5000);

        return [
            'warehouse_id' => Warehouse::factory(),
            'name' => "Frozen {$species}",
            'sku' => strtoupper($this->faker->unique()->bothify('SKU-##??##')),
            'category' => $this->faker->randomElement(['frozen', 'chilled', 'live', 'dry']),
            'species' => $species,
            'quantity_kg' => $quantity,
            'reorder_threshold_kg' => round($quantity * 0.15, 2),
            'unit_price' => $this->faker->randomFloat(2, 3, 25),
            'caught_at' => $this->faker->dateTimeBetween('-30 days', '-1 days'),
            'expires_at' => $this->faker->dateTimeBetween('+1 days', '+90 days'),
        ];
    }
}
