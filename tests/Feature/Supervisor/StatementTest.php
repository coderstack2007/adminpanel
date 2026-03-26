<?php

namespace Tests\Feature\Supervisor;

use App\Models\State;
use App\Models\User;
use App\Models\VacancyRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class StatementTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'vacancy_request.view',
            'vacancy_request.view_any',
            'vacancy_request.approve',
            'vacancy_request.reject',
            'vacancy_request.hold',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $superAdminRole->syncPermissions(Permission::all());

        $hr_manager = Role::firstOrCreate(['name' => 'hr_manager',      'guard_name' => 'web']);

        $this->superAdmin = User::factory()->create();
        $this->superAdmin->assignRole('super_admin');
    }

    // ================================================================
    // INDEX — список всех заявок
    // ================================================================

    #[Test]
    public function super_admin_can_view_statements_index(): void
    {
        State::factory()->supervisorReview()->create();
        VacancyRequest::factory()->count(3)->supervisorReview()->create();

        $this->actingAs($this->superAdmin)
            ->get(route('supervisor.statements.index'))
            ->assertOk()
            ->assertViewIs('supervisor.statements')
            ->assertViewHas('statements');
    }

    #[Test]
    public function guest_cannot_view_statements_index(): void
    {
        $this->get(route('supervisor.statements.index'))
            ->assertRedirect(route('login'));
    }

    // ================================================================
    // SHOW — детальный просмотр заявки
    // ================================================================

    #[Test]
    public function super_admin_can_view_statement_show(): void
    {
        $statement = VacancyRequest::factory()->supervisorReview()->create();

        $this->actingAs($this->superAdmin)
            ->get(route('supervisor.statements.show', $statement))
            ->assertOk()
            ->assertViewIs('supervisor.statement_show')
            ->assertViewHas('statement');
    }

    #[Test]
    public function guest_cannot_view_statement_show(): void
    {
        $statement = VacancyRequest::factory()->supervisorReview()->create();

        $this->get(route('supervisor.statements.show', $statement))
            ->assertRedirect(route('login'));
    }

    // ================================================================
    // APPROVE — одобрение заявки
    // ================================================================

    #[Test]
    public function super_admin_can_approve_statement(): void
    {
        State::factory()->approved()->create();
        $statement = VacancyRequest::factory()->supervisorReview()->create();

        $this->actingAs($this->superAdmin)
            ->post(route('supervisor.statements.approve', $statement), [
                'comment' => 'Всё хорошо, одобряю.',
            ])
            ->assertRedirect(route('supervisor.statements.show', $statement))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('vacancy_requests', [
            'id' => $statement->id,
            'status' => 'approved',
            'supervisor_id' => $this->superAdmin->id,
        ]);
    }

    #[Test]
    public function approve_without_comment_is_also_valid(): void
    {
        State::factory()->approved()->create();
        $statement = VacancyRequest::factory()->supervisorReview()->create();

        $this->actingAs($this->superAdmin)
            ->post(route('supervisor.statements.approve', $statement), [])
            ->assertRedirect(route('supervisor.statements.show', $statement))
            ->assertSessionHas('success');
    }

    #[Test]
    public function guest_cannot_approve_statement(): void
    {
        $statement = VacancyRequest::factory()->supervisorReview()->create();

        $this->post(route('supervisor.statements.approve', $statement))
            ->assertRedirect(route('login'));

        $this->assertDatabaseMissing('vacancy_requests', [
            'id' => $statement->id,
            'status' => 'approved',
        ]);
    }

    // ================================================================
    // REJECT — отклонение заявки
    // ================================================================

    #[Test]
    public function super_admin_can_reject_statement_with_comment(): void
    {
        State::factory()->rejected()->create();
        $statement = VacancyRequest::factory()->supervisorReview()->create();

        $this->actingAs($this->superAdmin)
            ->post(route('supervisor.statements.reject', $statement), [
                'comment' => 'Не соответствует требованиям.',
            ])
            ->assertRedirect(route('supervisor.statements.show', $statement))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('vacancy_requests', [
            'id' => $statement->id,
            'status' => 'rejected',
        ]);
    }

    #[Test]
    public function reject_without_comment_fails_validation(): void
    {
        $statement = VacancyRequest::factory()->supervisorReview()->create();

        $this->actingAs($this->superAdmin)
            ->post(route('supervisor.statements.reject', $statement), [])
            ->assertSessionHasErrors('comment');

        $this->assertDatabaseMissing('vacancy_requests', [
            'id' => $statement->id,
            'status' => 'rejected',
        ]);
    }

    #[Test]
    public function guest_cannot_reject_statement(): void
    {
        $statement = VacancyRequest::factory()->supervisorReview()->create();

        $this->post(route('supervisor.statements.reject', $statement), [
            'comment' => 'Причина отказа.',
        ])->assertRedirect(route('login'));

        $this->assertDatabaseMissing('vacancy_requests', [
            'id' => $statement->id,
            'status' => 'rejected',
        ]);
    }

    // ================================================================
    // ON HOLD — приостановка заявки
    // ================================================================

    #[Test]
    public function super_admin_can_put_statement_on_hold(): void
    {
        State::factory()->onHold()->create();
        $statement = VacancyRequest::factory()->supervisorReview()->create();

        $this->actingAs($this->superAdmin)
            ->post(route('supervisor.statements.on-hold', $statement), [
                'comment' => 'Требуется дополнительная проверка.',
            ])
            ->assertRedirect(route('supervisor.statements.show', $statement))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('vacancy_requests', [
            'id' => $statement->id,
            'status' => 'on_hold',
        ]);
    }

    #[Test]
    public function guest_cannot_put_statement_on_hold(): void
    {
        $statement = VacancyRequest::factory()->supervisorReview()->create();

        $this->post(route('supervisor.statements.on-hold', $statement))
            ->assertRedirect(route('login'));

        $this->assertDatabaseMissing('vacancy_requests', [
            'id' => $statement->id,
            'status' => 'on_hold',
        ]);
    }

    // ================================================================
    // authorizeDecision — заявка не в статусе supervisor_review
    // ================================================================

    #[Test]
    public function approve_aborts_if_statement_is_not_in_supervisor_review(): void
    {
        $statement = VacancyRequest::factory()->approved()->create();

        $this->actingAs($this->superAdmin)
            ->post(route('supervisor.statements.approve', $statement))
            ->assertForbidden();
    }

    #[Test]
    public function reject_aborts_if_statement_is_not_in_supervisor_review(): void
    {
        $statement = VacancyRequest::factory()->rejected()->create();

        $this->actingAs($this->superAdmin)
            ->post(route('supervisor.statements.reject', $statement), [
                'comment' => 'Комментарий.',
            ])
            ->assertForbidden();
    }

    #[Test]
    public function on_hold_aborts_if_statement_is_not_in_supervisor_review(): void
    {
        $statement = VacancyRequest::factory()->onHold()->create();

        $this->actingAs($this->superAdmin)
            ->post(route('supervisor.statements.on-hold', $statement))
            ->assertForbidden();
    }
}
