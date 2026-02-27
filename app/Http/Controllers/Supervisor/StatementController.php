<?php
// ═══════════════════════════════════════════════════════════════
// app/Http/Controllers/Supervisor/StatementController.php
// ВАЖНО: supervisor = super_admin в твоей системе.
// Заявки назначаются не конкретному юзеру, а роли super_admin.
// Поэтому убираем проверку supervisor_id и показываем все
// заявки со статусом supervisor_review.
// ═══════════════════════════════════════════════════════════════

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\VacancyRequest;
use App\Models\VacancyRequestLog;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatementController extends Controller
{
    /**
     * Все заявки на согласование (super_admin видит все)
     */
    public function index()
    {
        $statements = VacancyRequest::with(['position', 'subdivision', 'requester', 'state'])
            ->whereIn('status', ['supervisor_review', 'approved', 'rejected', 'on_hold'])
            ->orderByDesc('sent_to_supervisor_at')
            ->get();

        return view('supervisor.statements', compact('statements'));
    }

    /**
     * Детальный просмотр
     */
    public function show(VacancyRequest $statement)
    {
        // super_admin видит все заявки, дополнительной проверки не нужно
        $statement->load([
            'position', 'subdivision.head',
            'department', 'branch', 'requester',
            'hrEditor', 'editedBy', 'logs.user', 'state',
        ]);

        return view('supervisor.statement_show', compact('statement'));
    }

    /**
     * Одобрить заявку
     */
    public function approve(Request $request, VacancyRequest $statement)
    {
        $this->authorizeDecision($statement);

        $request->validate([
            'comment' => 'nullable|string|max:1000',
        ]);

        $this->applyDecision($statement, 'approved', $request->comment,
            'Заявка одобрена руководителем', now()
        );

        return redirect()
            ->route('supervisor.statements.show', $statement)
            ->with('success', 'Заявка одобрена.');
    }

    /**
     * Отклонить заявку
     */
    public function reject(Request $request, VacancyRequest $statement)
    {
        $this->authorizeDecision($statement);

        $request->validate([
            'comment' => 'required|string|max:1000',
        ]);

        $this->applyDecision($statement, 'rejected', $request->comment,
            'Заявка отклонена руководителем'
        );

        return redirect()
            ->route('supervisor.statements.show', $statement)
            ->with('success', 'Заявка отклонена.');
    }

    /**
     * Приостановить заявку
     * Метод называется onHold (роут /hold → onHold)
     */
    public function onHold(Request $request, VacancyRequest $statement)
    {
        $this->authorizeDecision($statement);

        $request->validate([
            'comment' => 'nullable|string|max:1000',
        ]);

        $this->applyDecision($statement, 'on_hold', $request->comment,
            'Заявка приостановлена руководителем'
        );

        return redirect()
            ->route('supervisor.statements.show', $statement)
            ->with('success', 'Заявка приостановлена.');
    }

    // ─── Helpers ──────────────────────────────────────────────

    private function authorizeDecision(VacancyRequest $statement): void
    {
        if (!$statement->isSupervisorReview()) {
            abort(403, 'Заявка не находится на стадии согласования.');
        }
    }

    private function applyDecision(
        VacancyRequest $statement,
        string $newStatus,
        ?string $comment,
        string $logComment,
        $approvedAt = null
    ): void {
        $state = \App\Models\State::byKey($newStatus);

        DB::transaction(function () use ($statement, $newStatus, $comment, $logComment, $state, $approvedAt) {
            $updateData = [
                'status'                 => $newStatus,
                'state_id'               => $state?->id,
                'supervisor_id'          => auth()->id(), // фиксируем кто принял решение
                'supervisor_comment'     => $comment,
                'supervisor_reviewed_at' => now(),
            ];

            if ($approvedAt) {
                $updateData['approved_at'] = $approvedAt;
            }

            $statement->update($updateData);

            VacancyRequestLog::create([
                'vacancy_request_id' => $statement->id,
                'user_id'            => auth()->id(),
                'status'             => $newStatus,
                'comment'            => $logComment . ($comment ? ": {$comment}" : ''),
            ]);

            // Уведомить HR и заявителя
            NotificationService::onSupervisorDecision($statement, $newStatus);
        });
    }
}