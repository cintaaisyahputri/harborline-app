<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class VesselFactory extends Factory
{
    public function definition(): array
    {
        $names = ['Sekar Laut', 'Cakalang Jaya', 'Nusantara Bahari', 'Tuna Perkasa', 'Camar Selatan', 'Ombak Timur'];

        return [
            'name' => $this->faker->unique()->randomElement($names) . ' ' . $this->faker->randomNumber(2),
            'registration_number' => strtoupper('HP-' . $this->faker->unique()->bothify('##??###')),
            'home_port' => $this->faker->randomElement(['Tanjung Priok', 'Belawan', 'Bitung', 'Makassar', 'Muara Baru']),
            'captain_name' => $this->faker->name(),
            'capacity_tons' => $this->faker->numberBetween(50, 400),
            'status' => $this->faker->randomElement(['at_sea', 'docked', 'maintenance']),
            'current_lat' => $this->faker->latitude(-8, 5),
            'current_lng' => $this->faker->longitude(95, 140),
            'estimated_arrival' => $this->faker->dateTimeBetween('now', '+10 days'),
            'last_position_at' => now(),
        ];
    }
}
