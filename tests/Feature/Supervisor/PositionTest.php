<?php

namespace Tests\Feature\Supervisor;

use App\Models\Position;
use App\Models\Subdivision;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class PositionTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;

    protected Subdivision $subdivision;

    protected function setUp(): void
    {
        parent::setUp();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'position.view',
            'position.manage',
            'subdivision.view',
            'subdivision.manage',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $superAdminRole->syncPermissions(Permission::all());

        $this->superAdmin = User::factory()->create();
        $this->superAdmin->assignRole('super_admin');

        $this->subdivision = Subdivision::factory()->create();
    }

    // ================================================================
    // INDEX — просмотр списка должностей
    // ================================================================

    #[Test]
    public function super_admin_can_view_positions_page(): void
    {
        Position::factory()->count(3)->create(['subdivision_id' => $this->subdivision->id]);

        $this->actingAs($this->superAdmin)
            ->get(route('supervisor.subdivisions.positions.index', $this->subdivision))
            ->assertOk()
            ->assertViewIs('supervisor.positions')
            ->assertViewHas('subdivision', $this->subdivision)
            ->assertViewHas('positions');
    }

    #[Test]
    public function guest_cannot_view_positions_page(): void
    {
        $this->get(route('supervisor.subdivisions.positions.index', $this->subdivision))
            ->assertRedirect(route('login'));
    }

    // ================================================================
    // STORE — создание должности без сотрудника
    // ================================================================

    #[Test]
    public function super_admin_can_create_position_without_user(): void
    {
        $payload = [
            'name' => 'Менеджер',
            'category' => 'B',
            'grade' => 3,
        ];

        $this->actingAs($this->superAdmin)
            ->post(route('supervisor.subdivisions.positions.store', $this->subdivision), $payload)
            ->assertRedirect(route('supervisor.subdivisions.positions.index', $this->subdivision))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('positions', [
            'subdivision_id' => $this->subdivision->id,
            'name' => 'Менеджер',
            'category' => 'B',
            'grade' => 3,
            'is_vacant' => true,
        ]);
    }

    #[Test]
    public function super_admin_can_create_position_with_user(): void
    {
        Role::firstOrCreate(['name' => 'hr_manager', 'guard_name' => 'web']);

        $payload = [
            'name' => 'HR Специалист',
            'category' => 'A',
            'grade' => 2,
            'user_name' => 'Иван Иванов',
            'email' => 'ivan@example.com',
            'password' => 'secret123',
            'role' => 'hr_manager',
        ];

        $this->actingAs($this->superAdmin)
            ->post(route('supervisor.subdivisions.positions.store', $this->subdivision), $payload)
            ->assertRedirect(route('supervisor.subdivisions.positions.index', $this->subdivision))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('positions', [
            'subdivision_id' => $this->subdivision->id,
            'name' => 'HR Специалист',
            'is_vacant' => false,
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'ivan@example.com',
            'name' => 'Иван Иванов',
        ]);
    }

    #[Test]
    public function guest_cannot_create_position(): void
    {
        $this->post(route('supervisor.subdivisions.positions.store', $this->subdivision), [
            'name' => 'Менеджер',
            'category' => 'B',
            'grade' => 3,
        ])->assertRedirect(route('login'));

        $this->assertDatabaseMissing('positions', ['name' => 'Менеджер']);
    }

    // ================================================================
    // DESTROY — удаление должности
    // ================================================================

    #[Test]
    public function super_admin_can_delete_position(): void
    {
        $position = Position::factory()->create(['subdivision_id' => $this->subdivision->id]);

        $this->actingAs($this->superAdmin)
            ->delete(route('supervisor.subdivisions.positions.destroy', [$this->subdivision, $position]))
            ->assertRedirect(route('supervisor.subdivisions.positions.index', $this->subdivision))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('positions', ['id' => $position->id]);
    }

    #[Test]
    public function super_admin_cannot_delete_own_position(): void
    {
        $position = Position::factory()->create(['subdivision_id' => $this->subdivision->id]);

        $this->superAdmin->update(['position_id' => $position->id]);

        $this->actingAs($this->superAdmin)
            ->delete(route('supervisor.subdivisions.positions.destroy', [$this->subdivision, $position]))
            ->assertRedirect(route('supervisor.subdivisions.positions.index', $this->subdivision))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('positions', ['id' => $position->id]);
    }

    #[Test]
    public function guest_cannot_delete_position(): void
    {
        $position = Position::factory()->create(['subdivision_id' => $this->subdivision->id]);

        $this->delete(route('supervisor.subdivisions.positions.destroy', [$this->subdivision, $position]))
            ->assertRedirect(route('login'));

        $this->assertDatabaseHas('positions', ['id' => $position->id]);
    }
}
