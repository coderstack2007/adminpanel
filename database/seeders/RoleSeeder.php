<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        Role::firstOrCreate(['name' => 'super_admin',  'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'hr_manager',   'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'employee',     'guard_name' => 'web']);
    }
}