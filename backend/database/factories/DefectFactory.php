<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\Defect> */
class DefectFactory extends Factory
{
    public function definition(): array { return ['code' => 'DEF-'.$this->faker->unique()->numerify('###'), 'name' => 'Surface defect', 'category' => $this->faker->randomElement(['Critical', 'Major', 'Minor']), 'description' => $this->faker->optional()->sentence(), 'is_active' => true]; }
}
