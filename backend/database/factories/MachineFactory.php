<?php

namespace Database\Factories;

use App\Models\Line;
use App\Models\Plant;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\Machine> */
class MachineFactory extends Factory
{
    public function definition(): array { return ['plant_id' => Plant::factory(), 'line_id' => Line::factory(), 'code' => 'M'.$this->faker->unique()->numberBetween(1, 999), 'name' => 'Machine '.$this->faker->unique()->numberBetween(1, 999), 'machine_number' => $this->faker->unique()->numerify('MC-#####'), 'description' => $this->faker->optional()->sentence(), 'is_active' => true]; }
}
