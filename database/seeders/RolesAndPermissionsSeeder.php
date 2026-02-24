<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'vacancy_request.create',   'vacancy_request.view',
            'vacancy_request.view_any', 'vacancy_request.edit',
            'vacancy_request.submit',   'vacancy_request.approve',
            'vacancy_request.reject',   'vacancy_request.hold',
            'vacancy_request.close',    'vacancy_request.confirm_close',
            'vacancy_request.manage',
            'position.view',   'position.manage',
            'subdivision.view','subdivision.manage',
            'department.view', 'department.manage',
            'branch.view',     'branch.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // super_admin — всё
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin']);
        $superAdmin->syncPermissions(Permission::all());

        // hr_manager
        $hrManager = Role::firstOrCreate(['name' => 'hr_manager']);
        $hrManager->syncPermissions([
            'vacancy_request.create', 'vacancy_request.view', 'vacancy_request.view_any',
            'vacancy_request.edit', 'vacancy_request.submit', 'vacancy_request.hold',
            'vacancy_request.close', 'position.view', 'position.manage',
            'subdivision.view', 'subdivision.manage',
            'department.view', 'department.manage', 'branch.view',
        ]);

      
        // department_head
        $deptHead = Role::firstOrCreate(['name' => 'department_head']);
        $deptHead->syncPermissions([
            'vacancy_request.view', 'vacancy_request.approve',
            'vacancy_request.reject', 'vacancy_request.hold',
            'position.view', 'subdivision.view', 'department.view', 'branch.view',
        ]);

        // employee — только просмотр структуры
        $employee = Role::firstOrCreate(['name' => 'employee']);
        $employee->syncPermissions([
            'subdivision.view', 'department.view',
            'branch.view', 'position.view',
        ]);

        $this->command->info('✅ Роли: super_admin, hr_manager,  department_head, employee');
    }
}