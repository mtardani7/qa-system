<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use App\Models\Plant;
use App\Models\Machine;
use App\Models\Shift;
use App\Models\Product;
use App\Models\Defect;
use App\Models\Line;
use App\Models\QaChecker;
use App\Models\Company;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = ['dashboard.view', 'plants.view', 'plants.create', 'plants.update', 'plants.delete', 'lines.view', 'lines.create', 'lines.update', 'lines.delete', 'machines.view', 'machines.create', 'machines.update', 'machines.delete', 'shifts.view', 'shifts.create', 'shifts.update', 'shifts.delete', 'products.view', 'products.create', 'products.update', 'products.delete', 'defects.view', 'defects.create', 'defects.update', 'defects.delete', 'qa-checkers.view', 'qa-checkers.create', 'qa-checkers.update', 'qa-checkers.delete', 'daily-reports.view', 'daily-reports.create', 'daily-reports.update', 'daily-reports.delete', 'daily-reports.submit', 'daily-reports.review', 'daily-reports.approve', 'daily-reports.reject', 'daily-reports.lock', 'daily-reports.export', 'daily-reports.import', 'enterprise.view', 'enterprise.manage', 'audit.view', 'users.view', 'users.create', 'users.update'];
        foreach ($permissions as $permission) Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        $administrator = Role::firstOrCreate(['name' => 'Administrator', 'guard_name' => 'web']);
        $administrator->syncPermissions($permissions);
        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions($permissions);
        $qaStaff = Role::firstOrCreate(['name' => 'QA Staff', 'guard_name' => 'web']);
        $qaStaff->syncPermissions(['dashboard.view', 'daily-reports.view', 'daily-reports.create', 'daily-reports.update', 'daily-reports.delete', 'daily-reports.export', 'daily-reports.import']);
        $qaSupervisor = Role::firstOrCreate(['name' => 'QA Supervisor', 'guard_name' => 'web']);
        $qaSupervisor->syncPermissions(['dashboard.view', 'daily-reports.view', 'daily-reports.lock', 'daily-reports.export']);
        $qaManager = Role::firstOrCreate(['name' => 'QA Manager', 'guard_name' => 'web']);
        $qaManager->syncPermissions(['dashboard.view', 'daily-reports.view', 'daily-reports.lock', 'daily-reports.export', 'audit.view']);
        $management = Role::firstOrCreate(['name' => 'Management', 'guard_name' => 'web']);
        $management->syncPermissions(array_values(array_filter($permissions, fn (string $permission): bool => !str_starts_with($permission, 'users.'))));
        $production = Role::firstOrCreate(['name' => 'Production', 'guard_name' => 'web']);
        $production->syncPermissions(['dashboard.view', 'daily-reports.view']);
        $seedUsers = [
            ['name' => 'Super Admin', 'email' => 'superadmin@example.com', 'role' => $superAdmin],
        ];

        foreach ($seedUsers as $seedUser) {
            $user = User::updateOrCreate(
                ['email' => $seedUser['email']],
                [
                    'name' => $seedUser['name'],
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ],
            );

            $user->syncRoles([$seedUser['role']]);
        }
        $company = Company::firstOrCreate(['code' => 'DEFAULT'], ['name' => 'QA Management Company', 'timezone' => config('app.timezone', 'UTC'), 'is_active' => true]);
        $plant = Plant::firstOrCreate(['code' => 'RX03'], ['company_id' => $company->id, 'name' => 'Plant 3', 'description' => null, 'is_active' => true]);
        $plant->update(['company_id' => $company->id, 'name' => 'Plant 3', 'description' => null, 'is_active' => true]);
        $line = Line::firstOrCreate(['plant_id' => $plant->id, 'code' => 'L01'], ['name' => 'Assembly Line 01', 'is_active' => true]);
        $machines = [
            'BREYER 1', 'BREYER 2', 'AISA 61', 'AISA 62', 'AISA 63', 'AISA 64', 'AISA 80', 'SIDEN-1', 'SENBAR 1', 'SENBAR 2',
            'WUTUNG 2', 'WUTUNG 3', 'ISIMAT', 'POLYTYPE', 'GCM 1', 'GCM 2', 'GCM 3', 'GCM 4', 'TECHNO -1', 'TECHNO -2',
            'MANUAL', 'MADAG 2', 'MADAG 3', 'SEALING', 'CAPREX', 'SIDEN-2', 'SIDEN-CAPPING', 'SIDEN LABEL', 'SIDEN OFFSET',
        ];
        foreach ($machines as $index => $machineName) {
            $number = $index + 1;
            Machine::updateOrCreate(
                ['plant_id' => $plant->id, 'code' => 'M'.str_pad((string) $number, 2, '0', STR_PAD_LEFT)],
                ['line_id' => $line->id, 'machine_number' => (string) $number, 'name' => $machineName, 'is_active' => true],
            );
        }
        $shift = Shift::firstOrCreate(['code' => 'S1'], ['plant_id' => $plant->id, 'name' => 'Day Shift', 'start_time' => '08:00', 'end_time' => '20:00', 'is_active' => true]);
        $product = Product::firstOrCreate(['mm_number' => 'MM-10001'], ['code' => 'SKU-10001', 'name' => 'Standard Product', 'qty_per_box' => 20, 'is_active' => true]);
        $this->call(DefectMasterSeeder::class);
        foreach (['DERBY', 'MONA', 'YONO', 'ICA', 'FERI', 'NITA', 'IRFAN'] as $index => $checkerName) {
            QaChecker::updateOrCreate(
                ['employee_number' => 'QA-'.str_pad((string) ($index + 1), 5, '0', STR_PAD_LEFT)],
                ['plant_id' => $plant->id, 'name' => $checkerName, 'position' => 'QA Checker', 'is_active' => true],
            );
        }
    }
}
