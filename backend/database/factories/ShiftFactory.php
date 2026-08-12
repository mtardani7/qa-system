<?php

namespace Database\Factories;

use App\Models\Plant;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\Shift> */
class ShiftFactory extends Factory
{
    public function definition(): array { return ['plant_id' => null, 'code' => 'S'.$this->faker->numberBetween(1, 9999), 'name' => 'Shift '.$this->faker->numberBetween(1, 9999), 'start_time' => '08:00', 'end_time' => '20:00', 'is_active' => true]; }
}
