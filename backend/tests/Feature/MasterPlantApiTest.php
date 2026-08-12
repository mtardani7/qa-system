<?php

namespace Tests\Feature;

use App\Models\Plant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MasterPlantApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_authorized_user_can_create_update_and_delete_a_plant(): void
    {
        $user = $this->userWithPermissions(['plants.view', 'plants.create', 'plants.update', 'plants.delete']);

        $created = $this->actingAs($user)->postJson('/api/v1/plants', ['code' => 'P5', 'name' => 'Plant 5', 'description' => 'Assembly site'])
            ->assertCreated()->assertJsonPath('success', true)->json('data');

        $plant = Plant::findOrFail($created['id']);
        $this->actingAs($user)->putJson('/api/v1/plants/'.$plant->id, ['code' => 'P5A', 'name' => 'Plant 5 Updated', 'is_active' => false])
            ->assertOk()->assertJsonPath('data.code', 'P5A')->assertJsonPath('data.is_active', false);

        $this->actingAs($user)->deleteJson('/api/v1/plants/'.$plant->id)->assertOk()->assertJsonPath('success', true);
        $this->assertDatabaseMissing('plants', ['id' => $plant->id]);
    }

    public function test_duplicate_code_is_rejected(): void
    {
        $user = $this->userWithPermissions(['plants.create']);
        Plant::factory()->create(['code' => 'P3']);

        $this->actingAs($user)->postJson('/api/v1/plants', ['code' => 'P3', 'name' => 'Duplicate'])
            ->assertUnprocessable()->assertJsonPath('success', false)->assertJsonValidationErrors(['code']);
    }

    private function userWithPermissions(array $permissions): User
    {
        $user = User::factory()->create();
        $role = Role::create(['name' => 'Plant Tester '.uniqid(), 'guard_name' => 'web']);
        foreach ($permissions as $permission) $role->givePermissionTo(Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']));
        $user->assignRole($role);
        return $user;
    }
}
