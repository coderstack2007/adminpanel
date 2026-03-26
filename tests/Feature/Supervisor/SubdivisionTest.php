<?php

namespace Tests\Feature\Supervisor;

use App\Models\Department;
use App\Models\Subdivision;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class SubdivisionTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;

    protected Department $department;

    protected function setUp(): void
    {
        parent::setUp();

        // Сбрасываем кэш прав Spatie
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Создаём нужные права
        $permissions = [
            'subdivision.view',
            'subdivision.manage',
            'department.view',
            'department.manage',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // super_admin — все права
        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $superAdminRole->syncPermissions(Permission::all());

        // Создаём пользователя и назначаем роль
        $this->superAdmin = User::factory()->create();
        $this->superAdmin->assignRole('super_admin');

        // Общий отдел для всех тестов
        $this->department = Department::factory()->create();
    }

    // ================================================================
    // INDEX — просмотр списка подразделений
    // ================================================================

    #[Test]
    public function super_admin_can_view_subdivisions_page(): void
    {
        Subdivision::factory()->count(3)->create(['department_id' => $this->department->id]);

        $this->actingAs($this->superAdmin)
            ->get(route('supervisor.departments.subdivisions.index', $this->department))
            ->assertOk()
            ->assertViewIs('supervisor.subdivisions')
            ->assertViewHas('department', $this->department)
            ->assertViewHas('subdivisions');
    }

    #[Test]
    public function guest_cannot_view_subdivisions_page(): void
    {
        $this->get(route('supervisor.departments.subdivisions.index', $this->department))
            ->assertRedirect(route('login'));
    }

    // ================================================================
    // STORE — создание подразделения
    // ================================================================

    #[Test]
    public function super_admin_can_create_subdivision(): void
    {
        $payload = ['name' => 'Бухгалтерия', 'code' => 'ACC-001'];

        $this->actingAs($this->superAdmin)
            ->post(route('supervisor.departments.subdivisions.store', $this->department), $payload)
            ->assertRedirect(route('supervisor.departments.subdivisions.index', $this->department))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('subdivisions', [
            'department_id' => $this->department->id,
            'name' => 'Бухгалтерия',
            'code' => 'ACC-001',
        ]);
    }

    #[Test]
    public function guest_cannot_create_subdivision(): void
    {
        $this->post(route('supervisor.departments.subdivisions.store', $this->department), [
            'name' => 'Бухгалтерия',
            'code' => 'ACC-001',
        ])->assertRedirect(route('login'));

        $this->assertDatabaseMissing('subdivisions', ['code' => 'ACC-001']);
    }

    // ================================================================
    // DESTROY — удаление подразделения
    // ================================================================

    #[Test]
    public function super_admin_can_delete_subdivision(): void
    {
        $subdivision = Subdivision::factory()->create(['department_id' => $this->department->id]);

        $this->actingAs($this->superAdmin)
            ->delete(route('supervisor.departments.subdivisions.destroy', [$this->department, $subdivision]))
            ->assertRedirect(route('supervisor.departments.subdivisions.index', $this->department))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('subdivisions', ['id' => $subdivision->id]);
    }

    #[Test]
    public function super_admin_cannot_delete_subdivision_that_has_positions(): void
    {
        $subdivision = Subdivision::factory()->create(['department_id' => $this->department->id]);

        // Создаём должность, привязанную к подразделению
        $subdivision->positions()->create([
            'name' => 'Менеджер',
            'code' => 'POS-001',
        ]);

        $this->actingAs($this->superAdmin)
            ->delete(route('supervisor.departments.subdivisions.destroy', [$this->department, $subdivision]))
            ->assertRedirect(route('supervisor.departments.subdivisions.index', $this->department))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('subdivisions', ['id' => $subdivision->id]);
    }

    #[Test]
    public function guest_cannot_delete_subdivision(): void
    {
        $subdivision = Subdivision::factory()->create(['department_id' => $this->department->id]);

        $this->delete(route('supervisor.departments.subdivisions.destroy', [$this->department, $subdivision]))
            ->assertRedirect(route('login'));

        $this->assertDatabaseHas('subdivisions', ['id' => $subdivision->id]);
    }
}
