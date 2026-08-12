<?php

namespace Tests\Feature;

use App\Models\Plant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PlantApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_authorized_user_can_list_plants_with_search(): void
    {
        $user = User::factory()->create();
        $role = Role::create(['name' => 'QA Manager', 'guard_name' => 'web']);
        $role->givePermissionTo(Permission::create(['name' => 'plants.view', 'guard_name' => 'web']));
        $user->assignRole($role);
        Plant::create(['code' => 'P3', 'name' => 'Plant 3', 'is_active' => true]);
        Plant::create(['code' => 'P4', 'name' => 'Plant 4', 'is_active' => true]);

        $this->actingAs($user)->getJson('/api/v1/plants?search=Plant%203')
            ->assertOk()->assertJsonPath('success', true)->assertJsonCount(1, 'data');
    }

    public function test_unauthorized_user_cannot_create_a_plant(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->postJson('/api/v1/plants', ['code' => 'P3', 'name' => 'Plant 3'])
            ->assertForbidden()->assertJsonPath('success', false);
    }
}
