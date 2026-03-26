<?php

namespace Database\Factories;

use App\Models\Position;
use App\Models\Subdivision;
use Illuminate\Database\Eloquent\Factories\Factory;

class PositionFactory extends Factory
{
    protected $model = Position::class;

    public function definition(): array
    {
        return [
            'subdivision_id' => Subdivision::factory(),
            'name' => $this->faker->jobTitle(),
            'category' => $this->faker->randomElement(['A', 'B', 'C', 'D']),
            'grade' => $this->faker->numberBetween(1, 5),
            'is_vacant' => true,
        ];
    }
}
