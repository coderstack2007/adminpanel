<?php

namespace Database\Factories;

use App\Models\State;
use Illuminate\Database\Eloquent\Factories\Factory;

class StateFactory extends Factory
{
    protected $model = State::class;

    public function definition(): array
    {
        return [
            'key' => $this->faker->unique()->slug(1),
            'label_ru' => $this->faker->word(),
            'color' => $this->faker->randomElement(['primary', 'secondary', 'success', 'danger', 'warning']),
            'order' => $this->faker->numberBetween(0, 10),
        ];
    }

    // State::factory()->supervisorReview()
    public function supervisorReview(): static
    {
        return $this->state(['key' => 'supervisor_review', 'label_ru' => 'На согласовании']);
    }

    public function approved(): static
    {
        return $this->state(['key' => 'approved', 'label_ru' => 'Одобрена']);
    }

    public function rejected(): static
    {
        return $this->state(['key' => 'rejected', 'label_ru' => 'Отклонена']);
    }

    public function onHold(): static
    {
        return $this->state(['key' => 'on_hold', 'label_ru' => 'Приостановлена']);
    }
}
