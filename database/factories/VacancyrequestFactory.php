<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\Department;
use App\Models\Position;
use App\Models\Subdivision;
use App\Models\User;
use App\Models\VacancyRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

class VacancyRequestFactory extends Factory
{
    protected $model = VacancyRequest::class;

    public function definition(): array
    {
        $branch = Branch::factory()->create();
        $department = Department::factory()->create(['branch_id' => $branch->id]);
        $subdivision = Subdivision::factory()->create(['department_id' => $department->id]);
        $position = Position::factory()->create(['subdivision_id' => $subdivision->id]);

        return [
            'branch_id' => $branch->id,
            'department_id' => $department->id,
            'subdivision_id' => $subdivision->id,
            'position_id' => $position->id,
            'requester_id' => User::factory(),
            'status' => 'supervisor_review',
            'sent_to_supervisor_at' => now(),
        ];
    }

    public function supervisorReview(): static
    {
        return $this->state(['status' => 'supervisor_review']);
    }

    public function approved(): static
    {
        return $this->state(['status' => 'approved']);
    }

    public function rejected(): static
    {
        return $this->state(['status' => 'rejected']);
    }

    public function onHold(): static
    {
        return $this->state(['status' => 'on_hold']);
    }
}
