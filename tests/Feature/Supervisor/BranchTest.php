<?php

namespace Tests\Feature\Supervisor;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BranchTest extends TestCase
{
    use RefreshDatabase;

    protected User $supervisor;

    protected function setUp(): void
    {
        parent::setUp();

        // Создаём нужные права
        Permission::firstOrCreate(['name' => 'branch.view',   'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'branch.manage', 'guard_name' => 'web']);

        // Создаём роль supervisor и назначаем права
        $role = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $role->syncPermissions(['branch.view', 'branch.manage']);

        // Создаём пользователя-супервайзера
        $this->supervisor = User::factory()->create();
        $this->supervisor->assignRole('super_admin');
    }

    #[Test]
    public function supervisor_can_view_branches_page(): void
    {
        $response = $this->actingAs($this->supervisor)
            ->get(route('supervisor.branches.index'));

        $response->assertStatus(200);
    }

    #[Test]
    public function guest_cannot_view_branches_page(): void
    {
        $response = $this->get(route('supervisor.branches.index'));

        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function branches_are_listed_on_index_page(): void
    {
        $branch = Branch::factory()->create(['name' => 'Тестовый филиал']);

        $response = $this->actingAs($this->supervisor)
            ->get(route('supervisor.branches.index'));

        $response->assertStatus(200);
        $response->assertSee($branch->name);
    }

    #[Test]
    public function supervisor_can_create_branch(): void
    {
        $payload = [
            'name' => 'Главный офис',
            'code' => 'HQ-01',
            'address' => 'г. Ташкент, ул. Примерная 1',
        ];

        $response = $this->actingAs($this->supervisor)
            ->post(route('supervisor.branches.store'), $payload);

        $response->assertRedirect(route('supervisor.branches.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('branches', [
            'name' => 'Главный офис',
            'code' => 'HQ-01',
        ]);
    }

    #[Test]
    public function guest_cannot_create_branch(): void
    {
        $response = $this->post(route('supervisor.branches.store'), [
            'name' => 'Новый филиал',
            'code' => 'NEW-01',
        ]);

        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function supervisor_can_delete_branch(): void
    {
        $branch = Branch::factory()->create();

        $response = $this->actingAs($this->supervisor)
            ->delete(route('supervisor.branches.destroy', $branch));

        $response->assertRedirect(route('supervisor.branches.index'));
        $this->assertDatabaseMissing('branches', ['id' => $branch->id]);
    }

    #[Test]
    public function guest_cannot_delete_branch(): void
    {
        $branch = Branch::factory()->create();

        $response = $this->delete(route('supervisor.branches.destroy', $branch));

        $response->assertRedirect(route('login'));
        $this->assertDatabaseHas('branches', ['id' => $branch->id]);
    }
}
