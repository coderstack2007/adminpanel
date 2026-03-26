<?php

namespace Tests\Feature\HR;

use App\Models\State;
use App\Models\User;
use App\Models\VacancyRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class HrStatementTest extends TestCase
{
    use RefreshDatabase;

    protected User $hrManager;

    protected function setUp(): void
    {
        parent::setUp();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'vacancy_request.view',
            'vacancy_request.view_any',
            'vacancy_request.edit',
            'vacancy_request.submit',
            'vacancy_request.manage',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // Создаём все роли чтобы NotificationService не падал
        Role::firstOrCreate(['name' => 'super_admin',     'guard_name' => 'web'])->syncPermissions(Permission::all());
        Role::firstOrCreate(['name' => 'department_head', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'employee',        'guard_name' => 'web']);

        $hrRole = Role::firstOrCreate(['name' => 'hr_manager', 'guard_name' => 'web']);
        $hrRole->syncPermissions(Permission::all());

        $this->hrManager = User::factory()->create();
        $this->hrManager->assignRole('hr_manager');

        config(['adminresumes' => config('database.connections.'.config('database.default'))]);
    }

    // ================================================================
    // INDEX — список всех заявок
    // ================================================================

    #[Test]
    public function hr_manager_can_view_statements_index(): void
    {
        VacancyRequest::factory()->count(3)->supervisorReview()->create();

        $this->actingAs($this->hrManager)
            ->get(route('hr.statements.index'))
            ->assertOk()
            ->assertViewIs('hr.statements')
            ->assertViewHas('statements');
    }

    #[Test]
    public function guest_cannot_view_statements_index(): void
    {
        $this->get(route('hr.statements.index'))
            ->assertRedirect(route('login'));
    }

    // ================================================================
    // SHOW — детальный просмотр заявки
    // ================================================================

    #[Test]
    public function hr_manager_can_view_statement_show(): void
    {
        $statement = VacancyRequest::factory()->create(['status' => 'submitted']);

        $this->actingAs($this->hrManager)
            ->get(route('hr.statements.show', $statement))
            ->assertOk()
            ->assertViewIs('hr.statement_show')
            ->assertViewHas('statement')
            ->assertViewHas('supervisors');
    }

    #[Test]
    public function guest_cannot_view_statement_show(): void
    {
        $statement = VacancyRequest::factory()->create(['status' => 'submitted']);

        $this->get(route('hr.statements.show', $statement))
            ->assertRedirect(route('login'));
    }

    // ================================================================
    // UPDATE — HR редактирует заявку
    // ================================================================

    #[Test]
    public function hr_manager_can_update_submitted_statement(): void
    {
        State::factory()->create(['key' => 'hr_reviewed', 'label_ru' => 'HR рассматривает']);

        $statement = VacancyRequest::factory()->create(['status' => 'submitted']);

        $this->actingAs($this->hrManager)
            ->put(route('hr.statements.update', $statement), [
                'reports_to' => 'Директор',
                'grade' => 3,
            ])
            ->assertRedirect(route('hr.statements.show', $statement))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('vacancy_requests', [
            'id' => $statement->id,
            'status' => 'hr_reviewed',
            'hr_editor_id' => $this->hrManager->id,
        ]);
    }

    #[Test]
    public function hr_manager_cannot_update_statement_in_supervisor_review(): void
    {
        $statement = VacancyRequest::factory()->supervisorReview()->create();

        $this->actingAs($this->hrManager)
            ->put(route('hr.statements.update', $statement), [
                'reports_to' => 'Директор',
            ])
            ->assertForbidden();
    }

    #[Test]
    public function update_creates_vacancy_request_log(): void
    {
        State::factory()->create(['key' => 'hr_reviewed', 'label_ru' => 'HR рассматривает']);

        $statement = VacancyRequest::factory()->create(['status' => 'submitted']);

        $this->actingAs($this->hrManager)
            ->put(route('hr.statements.update', $statement), [
                'grade' => 2,
            ]);

        $this->assertDatabaseHas('vacancy_request_logs', [
            'vacancy_request_id' => $statement->id,
            'user_id' => $this->hrManager->id,
            'status' => 'hr_reviewed',
        ]);
    }

    #[Test]
    public function guest_cannot_update_statement(): void
    {
        $statement = VacancyRequest::factory()->create(['status' => 'submitted']);

        $this->put(route('hr.statements.update', $statement), [
            'grade' => 2,
        ])->assertRedirect(route('login'));
    }

    // ================================================================
    // SEND TO SUPERVISOR — отправка на подпись
    // ================================================================

    #[Test]
    public function hr_manager_can_send_statement_to_supervisor(): void
    {
        State::factory()->create(['key' => 'supervisor_review', 'label_ru' => 'На подписи']);

        $supervisor = User::factory()->create();
        $supervisor->assignRole('super_admin');

        $statement = VacancyRequest::factory()->create(['status' => 'submitted']);

        $this->actingAs($this->hrManager)
            ->post(route('hr.statements.send-supervisor', $statement), [
                'supervisor_id' => $supervisor->id,
            ])
            ->assertRedirect(route('hr.statements.show', $statement))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('vacancy_requests', [
            'id' => $statement->id,
            'status' => 'supervisor_review',
            'supervisor_id' => $supervisor->id,
        ]);
    }

    #[Test]
    public function send_to_supervisor_fails_if_already_in_review(): void
    {
        $supervisor = User::factory()->create();
        $supervisor->assignRole('super_admin');

        $statement = VacancyRequest::factory()->supervisorReview()->create();

        $this->actingAs($this->hrManager)
            ->post(route('hr.statements.send-supervisor', $statement), [
                'supervisor_id' => $supervisor->id,
            ])
            ->assertForbidden();
    }

    #[Test]
    public function send_to_supervisor_requires_supervisor_id(): void
    {
        $statement = VacancyRequest::factory()->create(['status' => 'submitted']);

        $this->actingAs($this->hrManager)
            ->post(route('hr.statements.send-supervisor', $statement), [])
            ->assertSessionHasErrors('supervisor_id');
    }

    #[Test]
    public function guest_cannot_send_to_supervisor(): void
    {
        $statement = VacancyRequest::factory()->create(['status' => 'submitted']);

        $this->post(route('hr.statements.send-supervisor', $statement), [
            'supervisor_id' => 1,
        ])->assertRedirect(route('login'));
    }

    // ================================================================
    // DELETE VACANCY — удаление заявки
    // ================================================================

}
