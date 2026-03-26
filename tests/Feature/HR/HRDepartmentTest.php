<?php

namespace Tests\Feature\HR;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class HRDepartmentTest extends TestCase
{
    use RefreshDatabase;

    protected User $hrManager;

    protected function setUp(): void
    {
        parent::setUp();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'department.view',
            'department.manage',
            'branch.view',
            'branch.manage',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        Role::firstOrCreate(['name' => 'super_admin',     'guard_name' => 'web'])->syncPermissions(Permission::all());
        $hrRole = Role::firstOrCreate(['name' => 'hr_manager', 'guard_name' => 'web']);
        $hrRole->syncPermissions(['department.view', 'branch.view']);

        $this->hrManager = User::factory()->create();
        $this->hrManager->assignRole('hr_manager');
    }

    // ================================================================
    // INDEX — просмотр всех отделов по филиалам
    // ================================================================

    #[Test]
    public function hr_manager_can_view_departments_page(): void
    {
        Branch::factory()->count(2)->create(['is_active' => true]);

        $this->actingAs($this->hrManager)
            ->get(route('hr.departments.index'))
            ->assertOk()
            ->assertViewIs('hr.departments')
            ->assertViewHas('branches');
    }

    #[Test]
    public function hr_manager_sees_only_own_branch_if_assigned(): void
    {
        $ownBranch = Branch::factory()->create(['is_active' => true]);
        $otherBranch = Branch::factory()->create(['is_active' => true]);

        // Привязываем HR к конкретному филиалу
        $this->hrManager->update(['branch_id' => $ownBranch->id]);

        $response = $this->actingAs($this->hrManager)
            ->get(route('hr.departments.index'))
            ->assertOk();

        $branches = $response->viewData('branches');
        $this->assertCount(1, $branches);
        $this->assertEquals($ownBranch->id, $branches->first()->id);
    }

    #[Test]
    public function hr_manager_sees_all_active_branches_if_not_assigned(): void
    {
        Branch::factory()->count(3)->create(['is_active' => true]);
        Branch::factory()->count(2)->create(['is_active' => false]); // неактивные — не должны попасть

        $response = $this->actingAs($this->hrManager)
            ->get(route('hr.departments.index'))
            ->assertOk();

        $branches = $response->viewData('branches');
        $this->assertCount(3, $branches);
    }

    #[Test]
    public function guest_cannot_view_departments_page(): void
    {
        $this->get(route('hr.departments.index'))
            ->assertRedirect(route('login'));
    }
}
