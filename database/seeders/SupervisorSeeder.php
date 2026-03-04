<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Department;
use App\Models\Position;
use App\Models\Subdivision;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SupervisorSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {

            $branch = Branch::firstOrCreate(
                ['code' => 'HQ'],
                [
                    'name'      => 'Главный офис',
                    'address'   => 'г. Ташкент, ул. Примерная 1',
                    'is_active' => true,
                ]
            );

            $department = Department::firstOrCreate(
                ['code' => 'ADMIN', 'branch_id' => $branch->id],
                [
                    'name'      => 'Административный отдел',
                    'is_active' => true,
                ]
            );

            $subdivision = Subdivision::firstOrCreate(
                ['code' => 'MGMT', 'department_id' => $department->id],
                [
                    'name'      => 'Управление',
                    'is_active' => true,
                ]
            );

            $position = Position::firstOrCreate(
                [
                    'name'           => 'Supervisor / Super Administrator',
                    'subdivision_id' => $subdivision->id,
                ],
                [
                    'category'  => 'A',
                    'grade'     => 5,
                    'is_vacant' => false
            
                ]
            );

            $user = User::firstOrCreate(
                ['email' => 'supervisor@hr.uz'],
                [
                    'name'           => 'Supervisor',
                    'password'       => Hash::make('1234'),
                    'employee_code'  => $this->generateUniqueCode(),
                    'branch_id'      => $branch->id,
                    'department_id'  => $department->id,
                    'subdivision_id' => $subdivision->id,
                    'position_id'    => $position->id,
           
          
                ]
            );

            $user->syncRoles(['super_admin']);

            $subdivision->update(['head_user_id' => $user->id]);
            $department->update(['head_user_id'  => $user->id]);

            $this->command->info('✅ Supervisor: supervisor@hr.uz / 1234');
            $this->command->info("   Code: {$user->employee_code}");
            $this->command->info("   Кат. {$position->category}, {$position->grade}-й разряд");
        });
    }

    private function generateUniqueCode(): string
    {
        do {
            $letters = strtoupper(substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ'), 0, 3));
            $digits  = str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT);
            $code    = "EMP-{$letters}{$digits}";
        } while (User::where('employee_code', $code)->exists());

        return $code;
    }
}