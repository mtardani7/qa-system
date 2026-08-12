<?php

namespace Database\Factories;

use App\Models\Plant;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\Line> */
class LineFactory extends Factory
{
    public function definition(): array { return ['plant_id' => Plant::factory(), 'code' => 'L'.$this->faker->unique()->numberBetween(1, 999), 'name' => 'Line '.$this->faker->unique()->numberBetween(1, 999), 'description' => $this->faker->optional()->sentence(), 'is_active' => true]; }
}
