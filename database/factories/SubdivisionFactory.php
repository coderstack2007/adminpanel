<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\Subdivision;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Subdivision>
 */
class SubdivisionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    // database/factories/SubdivisionFactory.php
    public function definition(): array
    {
        return [
            'department_id' => Department::factory(),
            'name' => $this->faker->words(2, true),
            'code' => strtoupper($this->faker->unique()->bothify('SUB-###')),
        ];
    }
}
