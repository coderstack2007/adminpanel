<?php

namespace Tests\Feature\HR;

use App\Models\Department;
use App\Models\Subdivision;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class HrSubdivisionTest extends TestCase
{
    use RefreshDatabase;

    protected User $hrManager;

    protected Department $department;

    protected function setUp(): void
    {
        parent::setUp();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'subdivision.view',
            'subdivision.manage',
            'department.view',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web'])->syncPermissions(Permission::all());
        $hrRole = Role::firstOrCreate(['name' => 'hr_manager', 'guard_name' => 'web']);
        $hrRole->syncPermissions(['subdivision.view', 'department.view']);

        $this->hrManager = User::factory()->create();
        $this->hrManager->assignRole('hr_manager');

        $this->department = Department::factory()->create();
    }

    // ================================================================
    // INDEX — просмотр подразделений отдела
    // ================================================================

    #[Test]
    public function hr_manager_can_view_subdivisions_page(): void
    {
        Subdivision::factory()->count(3)->create(['department_id' => $this->department->id]);

        $this->actingAs($this->hrManager)
            ->get(route('hr.departments.subdivisions.index', $this->department))
            ->assertOk()
            ->assertViewIs('hr.subdivisions')
            ->assertViewHas('department', $this->department)
            ->assertViewHas('subdivisions');
    }

    #[Test]
    public function subdivisions_page_loads_positions_and_users(): void
    {
        $subdivision = Subdivision::factory()->create(['department_id' => $this->department->id]);

        $response = $this->actingAs($this->hrManager)
            ->get(route('hr.departments.subdivisions.index', $this->department))
            ->assertOk();

        $subdivisions = $response->viewData('subdivisions');
        $this->assertTrue($subdivisions->first()->relationLoaded('positions'));
    }

    #[Test]
    public function guest_cannot_view_subdivisions_page(): void
    {
        $this->get(route('hr.departments.subdivisions.index', $this->department))
            ->assertRedirect(route('login'));
    }
}
