<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class WarehouseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->company() . ' Cold Storage',
            'location' => $this->faker->randomElement(['Muara Baru, Jakarta', 'Benoa, Bali', 'Bitung, Sulawesi Utara', 'Belawan, Medan']),
            'type' => $this->faker->randomElement(['cold_storage', 'dry_storage', 'processing']),
            'capacity_tons' => $this->faker->numberBetween(200, 2000),
            'manager_id' => User::factory()->state(['role' => 'warehouse']),
        ];
    }
}
