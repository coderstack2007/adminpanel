<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'employee_code' => 'EMP-'.strtoupper(Str::random(6)),
            'remember_token' => Str::random(10),
        ];
    }

    // User::factory()->hrManager()->create()
    public function hrManager(): static
    {
        return $this->afterCreating(function (User $user) {
            $user->assignRole('hr_manager');
        });
    }

    // User::factory()->superAdmin()->create()
    public function superAdmin(): static
    {
        return $this->afterCreating(function (User $user) {
            $user->assignRole('super_admin');
        });
    }

    // User::factory()->departmentHead()->create()
    public function departmentHead(): static
    {
        return $this->afterCreating(function (User $user) {
            $user->assignRole('department_head');
        });
    }

    // User::factory()->employee()->create()
    public function employee(): static
    {
        return $this->afterCreating(function (User $user) {
            $user->assignRole('employee');
        });
    }
}
