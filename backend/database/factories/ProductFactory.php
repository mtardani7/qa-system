<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\Product> */
class ProductFactory extends Factory
{
    public function definition(): array { return ['code' => 'SKU-'.$this->faker->unique()->numerify('#####'), 'name' => 'Product '.$this->faker->unique()->numerify('####'), 'mm_number' => 'MM-'.$this->faker->unique()->numerify('#####'), 'description' => $this->faker->optional()->sentence(), 'qty_per_box' => $this->faker->numberBetween(1, 50), 'category' => 'Standard', 'is_active' => true]; }
}
