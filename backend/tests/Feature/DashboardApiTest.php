<?php

namespace Tests\Feature;

use App\Models\DailyReport;
use App\Models\Defect;
use App\Models\Line;
use App\Models\Machine;
use App\Models\Plant;
use App\Models\Product;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DashboardApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_returns_aggregated_kpis_rankings_trend_and_pareto(): void
    {
        $user = $this->userWithDashboardPermission();
        $plant = Plant::factory()->create();
        $line = Line::factory()->create(['plant_id' => $plant->id]);
        $machine = Machine::factory()->create(['plant_id' => $plant->id, 'line_id' => $line->id]);
        $shift = Shift::factory()->create(['plant_id' => $plant->id]);
        $product = Product::factory()->create();
        $defect = Defect::factory()->create(['category' => 'Appearance']);
        DailyReport::factory()->create(['plant_id' => $plant->id, 'line_id' => $line->id, 'machine_id' => $machine->id, 'shift_id' => $shift->id, 'product_id' => $product->id, 'defect_id' => $defect->id, 'output_pcs' => 100, 'quantity_defect' => 5, 'production_date' => '2026-08-07']);

        $response = $this->actingAs($user)->getJson('/api/v1/dashboard?date=2026-08-07&plant_id='.$plant->id.'&shift_id='.$shift->id.'&machine_id='.$machine->id);

        $response->assertOk()->assertJsonPath('success', true)->assertJsonPath('data.summary.production_pcs', 100)->assertJsonPath('data.summary.defect_qty', 5)->assertJsonPath('data.summary.defect_rate', 5)->assertJsonPath('data.summary.yield', 95)->assertJsonPath('data.top_defects.0.quantity', 5)->assertJsonPath('data.pareto.0.cumulative_percentage', 100);
    }

    public function test_dashboard_requires_permission(): void
    {
        $this->actingAs(User::factory()->create())->getJson('/api/v1/dashboard')->assertForbidden()->assertJsonPath('success', false);
    }

    private function userWithDashboardPermission(): User
    {
        $user = User::factory()->create();
        $role = Role::create(['name' => 'Dashboard Tester '.uniqid(), 'guard_name' => 'web']);
        $role->givePermissionTo(Permission::firstOrCreate(['name' => 'dashboard.view', 'guard_name' => 'web']));
        $user->assignRole($role);
        return $user;
    }
}
