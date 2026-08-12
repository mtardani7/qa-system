<?php

namespace Database\Factories;

use App\Models\Plant;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\QaChecker> */
class QaCheckerFactory extends Factory
{
    public function definition(): array { return ['employee_number' => 'QA-'.$this->faker->unique()->numerify('#####'), 'name' => $this->faker->name(), 'position' => 'QA Checker', 'plant_id' => Plant::factory(), 'is_active' => true]; }
}
