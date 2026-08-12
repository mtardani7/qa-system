<?php

namespace Database\Seeders;

use App\Models\DailyReport;
use App\Models\Defect;
use App\Models\Line;
use App\Models\Machine;
use App\Models\Plant;
use App\Models\Product;
use App\Models\QaChecker;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class Plant3SampleSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()->where('email', 'superadmin@example.com')->first()
            ?? User::query()->where('email', 'test@example.com')->firstOrFail();
        $plant = Plant::query()->firstOrCreate(
            ['code' => 'P3'],
            ['name' => 'Plant 3', 'description' => 'Dummy data from Daily QA Report workbook', 'is_active' => true],
        );
        $line = Line::query()->firstOrCreate(
            ['plant_id' => $plant->id, 'code' => 'L01'],
            ['name' => 'Imported Production Line', 'is_active' => true],
        );
        $shift = Shift::query()->firstOrCreate(
            ['code' => 'S1'],
            ['plant_id' => $plant->id, 'name' => 'Shift 1', 'start_time' => '00:00', 'end_time' => '23:59', 'is_active' => true],
        );
        $checker = QaChecker::query()->firstOrCreate(
            ['employee_number' => 'QA-FERI'],
            ['plant_id' => $plant->id, 'name' => 'FERI', 'position' => 'QA Checker', 'is_active' => true],
        );
        $defect = Defect::query()->firstOrCreate(
            ['code' => 'DEF-SCRATCH'],
            ['name' => 'SCRATCH', 'category' => 'Major', 'is_active' => true],
        );

        $samples = [
            ['machine' => 'BREYER 1', 'mm' => '90024093', 'name' => 'T. RMSTR KAHF BLUE Ø50 3L SLV', 'po' => '43047838', 'qty' => 264, 'boxes' => 155],
            ['machine' => 'AISA 61', 'mm' => '90023648', 'name' => 'T. SECRET CLEAN UNDERARM 30ML HDR', 'po' => '43047847', 'qty' => 1122, 'boxes' => 18],
            ['machine' => 'AISA 62', 'mm' => '90022352', 'name' => 'T. PURBASARI WHITE Ø50 3L HDR SBR', 'po' => '43047826', 'qty' => 176, 'boxes' => 30],
            ['machine' => 'AISA 63', 'mm' => '90024473', 'name' => 'T. IMPLORA SNSCREEN SPF 40 25ML HDR', 'po' => '43047855', 'qty' => 798, 'boxes' => 19],
            ['machine' => 'AISA 64', 'mm' => '90024095', 'name' => 'T. RMSTR KAHF PCEB FW 100 HDR', 'po' => '43047856', 'qty' => 264, 'boxes' => 76],
        ];

        DB::transaction(function () use ($samples, $plant, $line, $shift, $checker, $defect, $user): void {
            foreach ($samples as $sample) {
                $machine = Machine::query()->firstOrCreate(
                    ['plant_id' => $plant->id, 'line_id' => $line->id, 'code' => 'M-'.str_replace(' ', '-', $sample['machine'])],
                    ['name' => $sample['machine'], 'is_active' => true],
                );
                $product = Product::query()->withTrashed()->where('mm_number', $sample['mm'])->first()
                    ?? Product::query()->withTrashed()->where('name', $sample['name'])->first()
                    ?? new Product(['code' => 'MM-'.$sample['mm'], 'name' => $sample['name'], 'mm_number' => $sample['mm']]);
                $product->fill(['description' => $sample['name'], 'qty_per_box' => $sample['qty'], 'is_active' => true])->save();
                if ($product->trashed()) {
                    $product->restore();
                }

                if (DailyReport::query()->where('plant_id', $plant->id)->where('po_number', $sample['po'])->exists()) {
                    continue;
                }

                DailyReport::query()->create([
                    'plant_id' => $plant->id,
                    'line_id' => $line->id,
                    'machine_id' => $machine->id,
                    'shift_id' => $shift->id,
                    'product_id' => $product->id,
                    'mm_number' => $sample['mm'],
                    'checker_id' => $checker->id,
                    'production_date' => '2025-12-01',
                    'po_number' => $sample['po'],
                    'output_box' => $sample['boxes'],
                    'qty_per_box' => $sample['qty'],
                    'output_pcs' => $sample['boxes'] * $sample['qty'],
                    'quantity_defect' => 0,
                    'status' => 'draft',
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ]);
            }
        });

        $this->command?->info('Plant 3 sample seed completed: 5 daily reports from the workbook sample.');
    }
}
