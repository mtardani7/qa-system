<?php

namespace Database\Factories;

use App\Models\Defect;
use App\Models\Machine;
use App\Models\Line;
use App\Models\Plant;
use App\Models\Product;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\DailyReport> */
class DailyReportFactory extends Factory
{
    public function definition(): array
    {
        $boxes = $this->faker->numberBetween(1, 100);
        $quantityPerBox = $this->faker->numberBetween(1, 50);
        return ['plant_id' => Plant::factory(), 'line_id' => Line::factory(), 'machine_id' => Machine::factory(), 'shift_id' => Shift::factory(), 'product_id' => Product::factory(['qty_per_box' => $quantityPerBox]), 'mm_number' => 'MM-'.$this->faker->unique()->numerify('#####'), 'po_number' => 'PO-'.$this->faker->numerify('#####'), 'output_box' => $boxes, 'qty_per_box' => $quantityPerBox, 'output_pcs' => $boxes * $quantityPerBox, 'defect_id' => null, 'quantity_defect' => 0, 'qa_checker_id' => User::factory(), 'production_date' => $this->faker->date()];
    }

    public function withDefect(): static { return $this->state(fn (): array => ['defect_id' => Defect::factory(), 'quantity_defect' => $this->faker->numberBetween(1, 10)]); }
}
