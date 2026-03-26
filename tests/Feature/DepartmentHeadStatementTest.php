<?php

namespace Tests\Feature\DepartmentHead;

use App\Models\Position;
use App\Models\State;
use App\Models\Subdivision;
use App\Models\User;
use App\Models\VacancyRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class DepartmentHeadStatementTest extends TestCase
{
    use RefreshDatabase;

    protected User $departmentHead;

    protected function setUp(): void
    {
        parent::setUp();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'vacancy_request.create',
            'vacancy_request.view',
            'vacancy_request.edit',
            'vacancy_request.submit',
            'vacancy_request.confirm_close',
            'position.view',
            'subdivision.view',
            'department.view',
            'branch.view',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // Создаём все роли чтобы NotificationService не падал
        Role::firstOrCreate(['name' => 'super_admin',     'guard_name' => 'web'])->syncPermissions(Permission::all());
        Role::firstOrCreate(['name' => 'hr_manager',      'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'employee',        'guard_name' => 'web']);

        $deptHeadRole = Role::firstOrCreate(['name' => 'department_head', 'guard_name' => 'web']);
        $deptHeadRole->syncPermissions([
            'vacancy_request.create', 'vacancy_request.view',
            'vacancy_request.edit',   'vacancy_request.submit',
            'vacancy_request.confirm_close',
            'position.view', 'subdivision.view', 'department.view', 'branch.view',
        ]);

        $this->departmentHead = User::factory()->create();
        $this->departmentHead->assignRole('department_head');
    }

    // ================================================================
    // INDEX — список своих заявок
    // ================================================================

    #[Test]
    public function department_head_can_view_own_statements(): void
    {
        // Свои заявки
        VacancyRequest::factory()->count(2)->create([
            'requester_id' => $this->departmentHead->id,
            'status' => 'draft',
        ]);

        // Чужая заявка — не должна попасть
        VacancyRequest::factory()->create(['status' => 'draft']);

        $response = $this->actingAs($this->departmentHead)
            ->get(route('department_head.statements.index'))
            ->assertOk()
            ->assertViewIs('department_head.statements')
            ->assertViewHas('statements');

        $statements = $response->viewData('statements');
        $this->assertCount(2, $statements);
    }

    #[Test]
    public function guest_cannot_view_statements_index(): void
    {
        $this->get(route('department_head.statements.index'))
            ->assertRedirect(route('login'));
    }

    // ================================================================
    // CREATE — форма создания заявки
    // ================================================================

    #[Test]
    public function department_head_can_view_create_form(): void
    {
        $subdivision = Subdivision::factory()->create();
        $this->departmentHead->update(['subdivision_id' => $subdivision->id]);

        $this->actingAs($this->departmentHead)
            ->get(route('department_head.statements.create'))
            ->assertOk()
            ->assertViewIs('department_head.statement_create')
            ->assertViewHas('user')
            ->assertViewHas('vacantPositions')
            ->assertViewHas('allPositions');
    }

    #[Test]
    public function guest_cannot_view_create_form(): void
    {
        $this->get(route('department_head.statements.create'))
            ->assertRedirect(route('login'));
    }

    // ================================================================
    // STORE — создание заявки (черновик)
    // ================================================================

    #[Test]
    public function department_head_can_store_statement_as_draft(): void
    {
        State::factory()->create(['key' => 'draft', 'label_ru' => 'Черновик']);

        $subdivision = Subdivision::factory()->create();
        $position = Position::factory()->create(['subdivision_id' => $subdivision->id]);

        $this->departmentHead->update([
            'subdivision_id' => $subdivision->id,
            'branch_id' => $subdivision->department->branch_id,
            'department_id' => $subdivision->department_id,
        ]);

        $this->actingAs($this->departmentHead)
            ->post(route('department_head.statements.store'), [
                'position_id' => $position->id,
                'grade' => 3,
            ])
            ->assertRedirect(route('department_head.statements.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('vacancy_requests', [
            'requester_id' => $this->departmentHead->id,
            'position_id' => $position->id,
            'status' => 'draft',
        ]);
    }

    #[Test]
    public function store_fails_without_position_id(): void
    {
        $this->actingAs($this->departmentHead)
            ->post(route('department_head.statements.store'), [])
            ->assertSessionHasErrors('position_id');
    }

    #[Test]
    public function guest_cannot_store_statement(): void
    {
        $position = Position::factory()->create();

        $this->post(route('department_head.statements.store'), [
            'position_id' => $position->id,
        ])->assertRedirect(route('login'));

        $this->assertDatabaseMissing('vacancy_requests', [
            'position_id' => $position->id,
        ]);
    }

    // ================================================================
    // SHOW — просмотр заявки
    // ================================================================

    #[Test]
    public function department_head_can_view_own_statement(): void
    {
        $statement = VacancyRequest::factory()->create([
            'requester_id' => $this->departmentHead->id,
            'status' => 'draft',
        ]);

        $this->actingAs($this->departmentHead)
            ->get(route('department_head.statements.show', $statement))
            ->assertOk()
            ->assertViewIs('department_head.statement_show')
            ->assertViewHas('statement');
    }

    #[Test]
    public function guest_cannot_view_statement_show(): void
    {
        $statement = VacancyRequest::factory()->create(['status' => 'draft']);

        $this->get(route('department_head.statements.show', $statement))
            ->assertRedirect(route('login'));
    }

    // ================================================================
    // EDIT — форма редактирования черновика
    // ================================================================

    #[Test]
    public function department_head_can_view_edit_form_for_own_draft(): void
    {
        $subdivision = Subdivision::factory()->create();
        $this->departmentHead->update(['subdivision_id' => $subdivision->id]);

        $statement = VacancyRequest::factory()->create([
            'requester_id' => $this->departmentHead->id,
            'subdivision_id' => $subdivision->id,
            'status' => 'draft',
        ]);

        $this->actingAs($this->departmentHead)
            ->get(route('department_head.statements.show', $statement))
            ->assertOk();
    }

    #[Test]
    public function department_head_cannot_edit_someone_elses_statement(): void
    {
        $otherStatement = VacancyRequest::factory()->create(['status' => 'draft']);

        $this->actingAs($this->departmentHead)
            ->get(route('department_head.statements.edit', $otherStatement))
            ->assertForbidden();
    }

    #[Test]
    public function department_head_cannot_edit_submitted_statement(): void
    {
        $statement = VacancyRequest::factory()->supervisorReview()->create([
            'requester_id' => $this->departmentHead->id,
        ]);

        $this->actingAs($this->departmentHead)
            ->get(route('department_head.statements.edit', $statement))
            ->assertForbidden();
    }

    // ================================================================
    // UPDATE — сохранение изменений черновика
    // ================================================================

    #[Test]
    public function department_head_can_update_own_draft(): void
    {
        $subdivision = Subdivision::factory()->create();
        $position = Position::factory()->create(['subdivision_id' => $subdivision->id]);

        $this->departmentHead->update(['subdivision_id' => $subdivision->id]);

        $statement = VacancyRequest::factory()->create([
            'requester_id' => $this->departmentHead->id,
            'subdivision_id' => $subdivision->id,
            'position_id' => $position->id,
            'status' => 'draft',
        ]);

        $this->actingAs($this->departmentHead)
            ->put(route('department_head.statements.update', $statement), [
                'position_id' => $position->id,
                'reports_to' => 'Генеральный директор',
                'grade' => 4,
            ])
            ->assertRedirect(route('department_head.statements.show', $statement))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('vacancy_requests', [
            'id' => $statement->id,
            'reports_to' => 'Генеральный директор',
            'grade' => 4,
        ]);
    }

    #[Test]
    public function department_head_cannot_update_someone_elses_statement(): void
    {
        $position = Position::factory()->create();
        $otherStatement = VacancyRequest::factory()->create(['status' => 'draft']);

        $this->actingAs($this->departmentHead)
            ->put(route('department_head.statements.update', $otherStatement), [
                'position_id' => $position->id,
            ])
            ->assertForbidden();
    }

    #[Test]
    public function update_creates_vacancy_request_log(): void
    {
        $subdivision = Subdivision::factory()->create();
        $position = Position::factory()->create(['subdivision_id' => $subdivision->id]);

        $this->departmentHead->update(['subdivision_id' => $subdivision->id]);

        $statement = VacancyRequest::factory()->create([
            'requester_id' => $this->departmentHead->id,
            'subdivision_id' => $subdivision->id,
            'position_id' => $position->id,
            'status' => 'draft',
        ]);

        $this->actingAs($this->departmentHead)
            ->put(route('department_head.statements.update', $statement), [
                'position_id' => $position->id,
            ]);

        $this->assertDatabaseHas('vacancy_request_logs', [
            'vacancy_request_id' => $statement->id,
            'user_id' => $this->departmentHead->id,
            'status' => 'draft',
        ]);
    }

    #[Test]
    public function guest_cannot_update_statement(): void
    {
        $position = Position::factory()->create();
        $statement = VacancyRequest::factory()->create(['status' => 'draft']);

        $this->put(route('department_head.statements.update', $statement), [
            'position_id' => $position->id,
        ])->assertRedirect(route('login'));
    }

    // ================================================================
    // SUBMIT — отправка черновика в HR
    // ================================================================

    #[Test]
    public function department_head_can_submit_own_draft(): void
    {
        State::factory()->create(['key' => 'submitted', 'label_ru' => 'Отправлена в HR']);

        $statement = VacancyRequest::factory()->create([
            'requester_id' => $this->departmentHead->id,
            'status' => 'draft',
        ]);

        $this->actingAs($this->departmentHead)
            ->post(route('department_head.statements.submit', $statement))
            ->assertRedirect(route('department_head.statements.show', $statement))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('vacancy_requests', [
            'id' => $statement->id,
            'status' => 'submitted',
        ]);
    }

    #[Test]
    public function department_head_cannot_submit_someone_elses_statement(): void
    {
        $otherStatement = VacancyRequest::factory()->create(['status' => 'draft']);

        $this->actingAs($this->departmentHead)
            ->post(route('department_head.statements.submit', $otherStatement))
            ->assertForbidden();
    }

    #[Test]
    public function department_head_cannot_submit_already_submitted_statement(): void
    {
        $statement = VacancyRequest::factory()->create([
            'requester_id' => $this->departmentHead->id,
            'status' => 'submitted',
        ]);

        $this->actingAs($this->departmentHead)
            ->post(route('department_head.statements.submit', $statement))
            ->assertForbidden();
    }

    #[Test]
    public function submit_creates_vacancy_request_log(): void
    {
        State::factory()->create(['key' => 'submitted', 'label_ru' => 'Отправлена в HR']);

        $statement = VacancyRequest::factory()->create([
            'requester_id' => $this->departmentHead->id,
            'status' => 'draft',
        ]);

        $this->actingAs($this->departmentHead)
            ->post(route('department_head.statements.submit', $statement));

        $this->assertDatabaseHas('vacancy_request_logs', [
            'vacancy_request_id' => $statement->id,
            'user_id' => $this->departmentHead->id,
            'status' => 'submitted',
        ]);
    }

    #[Test]
    public function guest_cannot_submit_statement(): void
    {
        $statement = VacancyRequest::factory()->create([
            'requester_id' => $this->departmentHead->id,
            'status' => 'draft',
        ]);

        $this->post(route('department_head.statements.submit', $statement))
            ->assertRedirect(route('login'));

        $this->assertDatabaseMissing('vacancy_requests', [
            'id' => $statement->id,
            'status' => 'submitted',
        ]);
    }

    // ================================================================
    // CONFIRM CLOSE — подтверждение закрытия вакансии
    // ================================================================

    #[Test]
    public function department_head_can_confirm_close(): void
    {
        State::factory()->create(['key' => 'confirmed_closed', 'label_ru' => 'Закрыта']);

        $statement = VacancyRequest::factory()->create([
            'requester_id' => $this->departmentHead->id,
            'status' => 'closed',
        ]);

        $this->actingAs($this->departmentHead)
            ->post(route('department_head.statements.confirm-close', $statement))
            ->assertRedirect(route('department_head.statements.show', $statement))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('vacancy_requests', [
            'id' => $statement->id,
            'status' => 'confirmed_closed',
        ]);
    }

    #[Test]
    public function department_head_cannot_confirm_close_someone_elses_statement(): void
    {
        $otherStatement = VacancyRequest::factory()->create(['status' => 'closed']);

        $this->actingAs($this->departmentHead)
            ->post(route('department_head.statements.confirm-close', $otherStatement))
            ->assertForbidden();
    }

    #[Test]
    public function department_head_cannot_confirm_close_non_closed_statement(): void
    {
        $statement = VacancyRequest::factory()->create([
            'requester_id' => $this->departmentHead->id,
            'status' => 'approved',
        ]);

        $this->actingAs($this->departmentHead)
            ->post(route('department_head.statements.confirm-close', $statement))
            ->assertForbidden();
    }

    #[Test]
    public function guest_cannot_confirm_close(): void
    {
        $statement = VacancyRequest::factory()->create([
            'requester_id' => $this->departmentHead->id,
            'status' => 'closed',
        ]);

        $this->post(route('department_head.statements.confirm-close', $statement))
            ->assertRedirect(route('login'));

        $this->assertDatabaseMissing('vacancy_requests', [
            'id' => $statement->id,
            'status' => 'confirmed_closed',
        ]);
    }
}
