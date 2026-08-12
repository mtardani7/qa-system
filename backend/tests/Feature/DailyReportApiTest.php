<?php

namespace Tests\Feature;

use App\Models\DailyReport;
use App\Models\Defect;
use App\Models\Machine;
use App\Models\Line;
use App\Models\Plant;
use App\Models\Product;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DailyReportApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_daily_report_calculates_output_and_resolves_defect_category(): void
    {
        $user = $this->userWithPermissions(['daily-reports.view', 'daily-reports.create']);
        $plant = Plant::factory()->create();
        $line = Line::factory()->create(['plant_id' => $plant->id]);
        $machine = Machine::factory()->create(['plant_id' => $plant->id, 'line_id' => $line->id]);
        $shift = Shift::factory()->create(['plant_id' => $plant->id]);
        $product = Product::factory()->create(['mm_number' => 'MM-MASTER', 'qty_per_box' => 12]);
        $defect = Defect::factory()->create(['category' => 'Function']);

        $response = $this->actingAs($user)->postJson('/api/v1/daily-reports', [
            'plant_id' => $plant->id, 'line_id' => $line->id, 'machine_id' => $machine->id, 'shift_id' => $shift->id, 'product_id' => $product->id,
            'mm_number' => 'MM-CLIENT-OVERRIDE', 'po_number' => 'PO-100', 'output_box' => 7, 'qty_per_box' => 999,
            'defect_id' => $defect->id, 'quantity_defect' => 2, 'qa_checker_id' => $user->id, 'production_date' => '2026-08-07',
        ]);

        $response->assertCreated()->assertJsonPath('data.output_pcs', 84)->assertJsonPath('data.mm_number', 'MM-MASTER')->assertJsonPath('data.qty_per_box', 12)->assertJsonPath('data.defect.category', 'Function');
        $this->assertDatabaseHas('daily_reports', ['output_pcs' => 84, 'mm_number' => 'MM-MASTER', 'qty_per_box' => 12, 'quantity_defect' => 2]);
    }

    public function test_machine_from_another_plant_is_rejected(): void
    {
        $user = $this->userWithPermissions(['daily-reports.create']);
        $plant = Plant::factory()->create();
        $line = Line::factory()->create(['plant_id' => $plant->id]);
        $otherMachine = Machine::factory()->create();
        $shift = Shift::factory()->create(['plant_id' => $plant->id]);
        $product = Product::factory()->create();

        $this->actingAs($user)->postJson('/api/v1/daily-reports', [
            'plant_id' => $plant->id, 'line_id' => $line->id, 'machine_id' => $otherMachine->id, 'shift_id' => $shift->id, 'product_id' => $product->id,
            'po_number' => 'PO-101', 'output_box' => 1, 'qa_checker_id' => $user->id, 'production_date' => '2026-08-07',
        ])->assertUnprocessable()->assertJsonValidationErrors(['machine_id']);
    }

    public function test_daily_reports_support_search_and_pagination(): void
    {
        $user = $this->userWithPermissions(['daily-reports.view']);
        $plant = Plant::factory()->create();
        DailyReport::factory()->count(3)->create(['plant_id' => $plant->id, 'qa_checker_id' => $user->id, 'mm_number' => 'MM-SEARCH']);

        $this->actingAs($user)->getJson('/api/v1/daily-reports?plant_id='.$plant->id.'&search=MM-SEARCH&per_page=2')
            ->assertOk()->assertJsonPath('success', true)->assertJsonCount(2, 'data');
    }

    private function userWithPermissions(array $permissions): User
    {
        $user = User::factory()->create();
        $role = Role::create(['name' => 'Daily Report Tester '.uniqid(), 'guard_name' => 'web']);
        foreach ($permissions as $permission) $role->givePermissionTo(Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']));
        $user->assignRole($role);
        return $user;
    }
}
