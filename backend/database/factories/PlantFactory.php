<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\Plant> */
class PlantFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => 'P'.$this->faker->unique()->numberBetween(1, 99),
            'name' => 'Plant '.$this->faker->unique()->numberBetween(1, 99),
            'description' => $this->faker->optional()->sentence(8),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => ['is_active' => false]);
    }
}
