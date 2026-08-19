<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Defect;
use App\Models\Line;
use App\Models\Machine;
use App\Models\Plant;
use App\Models\Product;
use App\Models\QaChecker;
use App\Models\Shift;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedRoles();

        $company = Company::firstOrCreate(
            ['code' => 'DEFAULT'],
            ['name' => 'QA Management Company', 'timezone' => config('app.timezone', 'UTC'), 'is_active' => true],
        );

        $plant = Plant::updateOrCreate(
            ['code' => 'RX03'],
            ['company_id' => $company->id, 'name' => 'Plant 3', 'description' => null, 'is_active' => true],
        );

        $line = Line::updateOrCreate(
            ['plant_id' => $plant->id, 'code' => 'L01'],
            ['name' => 'Assembly Line 01', 'is_active' => true],
        );

        foreach ([
            'BREYER 1', 'BREYER 2', 'AISA 61', 'AISA 62', 'AISA 63', 'AISA 64', 'AISA 80', 'SIDEN-1', 'SENBAR 1', 'SENBAR 2',
            'WUTUNG 2', 'WUTUNG 3', 'ISIMAT', 'POLYTYPE', 'GCM 1', 'GCM 2', 'GCM 3', 'GCM 4', 'TECHNO -1', 'TECHNO -2',
            'MANUAL', 'MADAG 2', 'MADAG 3', 'SEALING', 'CAPREX', 'SIDEN-2', 'SIDEN-CAPPING', 'SIDEN LABEL', 'SIDEN OFFSET',
        ] as $index => $machineName) {
            $number = $index + 1;
            Machine::updateOrCreate(
                ['plant_id' => $plant->id, 'code' => 'M'.str_pad((string) $number, 2, '0', STR_PAD_LEFT)],
                ['line_id' => $line->id, 'machine_number' => (string) $number, 'name' => $machineName, 'is_active' => true],
            );
        }

        Shift::updateOrCreate(
            ['code' => 'S1'],
            ['plant_id' => $plant->id, 'name' => 'Day Shift', 'start_time' => '08:00', 'end_time' => '20:00', 'is_active' => true],
        );

        Product::updateOrCreate(
            ['mm_number' => 'MM-10001'],
            ['code' => 'SKU-10001', 'name' => 'Standard Product', 'qty_per_box' => 20, 'is_active' => true],
        );

        $this->call(DefectMasterSeeder::class);

        foreach (['DERBY', 'MONA', 'YONO', 'ICA', 'FERI', 'NITA', 'IRFAN'] as $index => $checkerName) {
            QaChecker::updateOrCreate(
                ['employee_number' => 'QA-'.str_pad((string) ($index + 1), 5, '0', STR_PAD_LEFT)],
                ['plant_id' => $plant->id, 'name' => $checkerName, 'position' => 'QA Checker', 'is_active' => true],
            );
        }
    }

    private function seedRoles(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = ['dashboard.view', 'plants.view', 'plants.create', 'plants.update', 'plants.delete', 'lines.view', 'lines.create', 'lines.update', 'lines.delete', 'machines.view', 'machines.create', 'machines.update', 'machines.delete', 'shifts.view', 'shifts.create', 'shifts.update', 'shifts.delete', 'products.view', 'products.create', 'products.update', 'products.delete', 'defects.view', 'defects.create', 'defects.update', 'defects.delete', 'qa-checkers.view', 'qa-checkers.create', 'qa-checkers.update', 'qa-checkers.delete', 'daily-reports.view', 'daily-reports.create', 'daily-reports.update', 'daily-reports.delete', 'daily-reports.submit', 'daily-reports.review', 'daily-reports.approve', 'daily-reports.reject', 'daily-reports.lock', 'daily-reports.export', 'daily-reports.import', 'enterprise.view', 'enterprise.manage', 'audit.view', 'users.view', 'users.create', 'users.update'];
        foreach ($permissions as $permission) Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);

        $allPermissions = Permission::where('guard_name', 'web')->get();
        $rolePermissions = [
            'Administrator' => $permissions,
            'Super Admin' => $permissions,
            'QA Manager' => ['dashboard.view', 'daily-reports.view', 'daily-reports.review', 'daily-reports.approve', 'daily-reports.reject', 'daily-reports.lock', 'daily-reports.export', 'audit.view'],
            'QA Supervisor' => ['dashboard.view', 'daily-reports.view', 'daily-reports.review', 'daily-reports.approve', 'daily-reports.reject', 'daily-reports.lock', 'daily-reports.export'],
            'QA Staff' => ['dashboard.view', 'daily-reports.view', 'daily-reports.create', 'daily-reports.update', 'daily-reports.delete', 'daily-reports.submit', 'daily-reports.export', 'daily-reports.import'],
            'Management' => array_values(array_filter($permissions, fn (string $permission): bool => !str_starts_with($permission, 'users.'))),
            'Production' => ['dashboard.view', 'daily-reports.view'],
        ];

        foreach ($rolePermissions as $name => $names) {
            $role = Role::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
            $role->syncPermissions($allPermissions->whereIn('name', $names));
        }
    }
}
