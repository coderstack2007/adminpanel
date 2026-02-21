<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Department;
use App\Models\Position;
use App\Models\Subdivision;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SupervisorSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Организационная структура
        $branch = Branch::create([
            'name'      => 'Главный офис',
            'code'      => 'HQ',
            'address'   => 'г. Ташкент, ул. Примерная 1',
            'is_active' => true,
        ]);

        $department = Department::create([
            'branch_id' => $branch->id,
            'name'      => 'Административный отдел',
            'code'      => 'ADMIN',
            'is_active' => true,
        ]);

        $subdivision = Subdivision::create([
            'department_id' => $department->id,
            'name'          => 'Управление',
            'code'          => 'MGMT',
            'is_active'     => true,
        ]);

        $position = Position::create([
            'subdivision_id' => $subdivision->id,
            'name'           => 'Supervisor / Super Administrator',
            'category'       => 'A',
            'grade'          => 5,
            'is_vacant'      => false,
            'is_active'      => true,
        ]);

        // 2. Пользователь
        $user = User::create([
            'name'            => 'Supervisor',
            'email'           => 'supervisor@hr.uz',
            'password'        => Hash::make('1234'),
            'employee_code'   => 'EMP-0001',
            'branch_id'       => $branch->id,
            'department_id'   => $department->id,
            'subdivision_id'  => $subdivision->id,
            'position_id'     => $position->id,
            'is_active'       => true,
        ]);

        // 3. Назначаем роль (Spatie)

        $user->assignRole('super_admin');

        // 4. Назначаем head в subdivision и department
        $subdivision->update(['head_user_id' => $user->id]);
        $department->update(['head_user_id'  => $user->id]);

        $this->command->info('✅ Supervisor создан: supervisor@hr.uz / 1234');
        $this->command->info("   Категория: {$position->category}, Разряд: {$position->grade}");
    }
}