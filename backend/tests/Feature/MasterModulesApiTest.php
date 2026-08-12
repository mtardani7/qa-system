<?php

namespace Tests\Feature;

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

class MasterModulesApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_master_modules_are_registered_as_api_resources(): void
    {
        $user = $this->userWithPermissions(['plants.view', 'lines.view', 'machines.view', 'shifts.view', 'products.view', 'defects.view']);
        foreach (['plants', 'lines', 'machines', 'shifts', 'products', 'defects'] as $resource) {
            $this->actingAs($user)->getJson('/api/v1/'.$resource)->assertOk()->assertJsonPath('success', true);
        }
    }

    public function test_line_requires_a_valid_plant_foreign_key(): void
    {
        $user = $this->userWithPermissions(['lines.create']);
        $this->actingAs($user)->postJson('/api/v1/lines', ['plant_id' => 99999, 'code' => 'L99', 'name' => 'Invalid Line'])
            ->assertUnprocessable()->assertJsonValidationErrors(['plant_id']);
    }

    public function test_product_name_is_unique(): void
    {
        $user = $this->userWithPermissions(['products.create']);
        Product::factory()->create(['name' => 'Unique Product']);
        $this->actingAs($user)->postJson('/api/v1/products', ['code' => 'SKU-NEW', 'name' => 'Unique Product'])
            ->assertUnprocessable()->assertJsonValidationErrors(['name']);
    }

    public function test_master_records_are_soft_deleted(): void
    {
        $user = $this->userWithPermissions(['defects.create', 'defects.delete']);
        $defect = Defect::factory()->create();
        $this->actingAs($user)->deleteJson('/api/v1/defects/'.$defect->id)->assertOk();
        $this->assertSoftDeleted('defects', ['id' => $defect->id]);
        $this->actingAs($user)->getJson('/api/v1/defects/'.$defect->id)->assertNotFound();
    }

    public function test_master_filters_search_pagination_and_sorting_are_applied(): void
    {
        $user = $this->userWithPermissions(['plants.view']);
        Plant::factory()->create(['code' => 'P-SEARCH', 'name' => 'Searchable Plant', 'is_active' => true]);
        Plant::factory()->create(['code' => 'P-INACTIVE', 'name' => 'Hidden Plant', 'is_active' => false]);
        $this->actingAs($user)->getJson('/api/v1/plants?search=Searchable&is_active=1&sort=code&direction=asc&per_page=1')
            ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.code', 'P-SEARCH');
    }

    private function userWithPermissions(array $permissions): User
    {
        $user = User::factory()->create();
        $role = Role::create(['name' => 'Master Tester '.uniqid(), 'guard_name' => 'web']);
        foreach ($permissions as $permission) $role->givePermissionTo(Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']));
        $user->assignRole($role);
        return $user;
    }
}
