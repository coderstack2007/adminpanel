<?php

namespace Tests\Feature\Supervisor;

use App\Models\Branch;
use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class DepartmentTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;

    protected Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();

        // Сбрасываем кэш прав Spatie
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Создаём нужные права
        $permissions = [
            'department.view',
            'department.manage',
            'branch.view',
            'branch.manage',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // super_admin — все права
        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $superAdminRole->syncPermissions(Permission::all());

        // Создаём пользователей и назначаем роли
        $this->superAdmin = User::factory()->create();
        $this->superAdmin->assignRole('super_admin');

        // Общий филиал для всех тестов
        $this->branch = Branch::factory()->create();
    }

    // ================================================================
    // INDEX — просмотр списка отделов
    // ================================================================

    #[Test]
    public function super_admin_can_view_departments_page(): void
    {
        Department::factory()->count(3)->create(['branch_id' => $this->branch->id]);

        $this->actingAs($this->superAdmin)
            ->get(route('supervisor.branches.departments.index', $this->branch))
            ->assertOk()
            ->assertViewIs('supervisor.departments')
            ->assertViewHas('branch', $this->branch)
            ->assertViewHas('departments');
    }

    #[Test]
    public function guest_cannot_view_departments_page(): void
    {
        $this->get(route('supervisor.branches.departments.index', $this->branch))
            ->assertRedirect(route('login'));
    }

    // ================================================================
    // STORE — создание отдела
    // ================================================================

    #[Test]
    public function super_admin_can_create_department(): void
    {
        $payload = ['name' => 'Отдел кадров', 'code' => 'HR-001'];

        $this->actingAs($this->superAdmin)
            ->post(route('supervisor.branches.departments.store', $this->branch), $payload)
            ->assertRedirect(route('supervisor.branches.departments.index', $this->branch))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('departments', [
            'branch_id' => $this->branch->id,
            'name' => 'Отдел кадров',
            'code' => 'HR-001',
        ]);
    }

    // ================================================================
    // DESTROY — удаление отдела
    // ================================================================

    #[Test]
    public function super_admin_can_delete_department(): void
    {
        $department = Department::factory()->create(['branch_id' => $this->branch->id]);

        $this->actingAs($this->superAdmin)
            ->delete(route('supervisor.branches.departments.destroy', [$this->branch, $department]))
            ->assertRedirect(route('supervisor.branches.departments.index', $this->branch))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('departments', ['id' => $department->id]);
    }
}
