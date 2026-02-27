<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\VacancyRequest;
use App\Models\VacancyRequestLog;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HrStatementController extends Controller
{
    public function index()
    {
        $statements = VacancyRequest::with(['position', 'subdivision', 'requester', 'state', 'editedBy'])
            ->whereNotIn('status', ['draft'])
            ->orderByDesc('created_at')
            ->get();

        return view('hr.statements', compact('statements'));
    }

    public function show(VacancyRequest $statement)
    {
        $statement->load([
            'position', 'subdivision.head',
            'department', 'branch', 'requester',
            'hrEditor', 'editedBy', 'supervisor',
            'logs.user', 'state',
        ]);

        // Список supervisor'ов для select'а
        $supervisors = User::role('super_admin')->get();

        return view('hr.statement_show', compact('statement', 'supervisors'));
    }

    /**
     * HR редактирует заявку (только пока не отправлена supervisor'у)
     */
    public function update(Request $request, VacancyRequest $statement)
    {
        if (!$statement->canEditByHr()) {
            abort(403, 'Редактирование недоступно на данном этапе.');
        }

        $validated = $request->validate([
            'reports_to'               => 'nullable|string|max:255',
            'subordinates'             => 'nullable|array',
            'work_schedule'            => 'nullable|string|max:50',
            'work_start'               => 'nullable|date_format:H:i',
            'work_end'                 => 'nullable|date_format:H:i',
            'grade'                    => 'nullable|integer|min:1|max:5',
            'salary_probation'         => 'nullable|numeric',
            'salary_after_probation'   => 'nullable|numeric',
            'bonuses'                  => 'nullable|string',
            'opening_reason'           => 'nullable|string',
            'age_category'             => 'nullable|string',
            'gender'                   => 'nullable|string',
            'education'                => 'nullable|string',
            'experience'               => 'nullable|string',
            'languages'                => 'nullable|array',
            'specialized_knowledge'    => 'nullable|string',
            'job_responsibilities'     => 'nullable|string',
            'additional_requirements'  => 'nullable|string',
            'vacancy_close_deadline'   => 'nullable|date',
        ]);

        $editor = auth()->user();
        $state  = \App\Models\State::byKey('hr_reviewed');

        DB::transaction(function () use ($statement, $validated, $editor, $state) {
            $statement->update([
                ...$validated,
                'status'       => 'hr_reviewed',
                'state_id'     => $state?->id,
                'hr_editor_id' => $editor->id,
                'edited_by'    => $editor->id,
            ]);

            VacancyRequestLog::create([
                'vacancy_request_id' => $statement->id,
                'user_id'            => $editor->id,
                'status'             => 'hr_reviewed',
                'comment'            => "HR ({$editor->name}) внёс изменения в заявку",
            ]);

            // Уведомить заявителя об изменениях
            NotificationService::onHrEdited($statement, $editor);
        });

        return redirect()
            ->route('hr.statements.show', $statement)
            ->with('success', 'Изменения сохранены.');
    }

    /**
     * HR отправляет заявку на подпись Supervisor'у
     */
    public function sendToSupervisor(Request $request, VacancyRequest $statement)
    {
        $request->validate([
            'supervisor_id' => 'required|exists:users,id',
        ]);

        if (!in_array($statement->status, ['submitted', 'hr_reviewed'])) {
            abort(403, 'Нельзя отправить заявку на данном этапе.');
        }

        $state = \App\Models\State::byKey('supervisor_review');

        DB::transaction(function () use ($request, $statement, $state) {
            $statement->update([
                'status'                => 'supervisor_review',
                'state_id'              => $state?->id,
                'supervisor_id'         => $request->supervisor_id,
                'sent_to_supervisor_at' => now(),
            ]);

            VacancyRequestLog::create([
                'vacancy_request_id' => $statement->id,
                'user_id'            => auth()->id(),
                'status'             => 'supervisor_review',
                'comment'            => 'HR отправил заявку на подпись руководителю',
            ]);

            // Уведомить supervisor'а
            NotificationService::onSentToSupervisor($statement);
        });

        return redirect()
            ->route('hr.statements.show', $statement)
            ->with('success', 'Заявка отправлена руководителю на подпись.');
    }
}