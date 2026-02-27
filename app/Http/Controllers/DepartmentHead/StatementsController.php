<?php

namespace App\Http\Controllers\DepartmentHead;

use App\Http\Controllers\Controller;
use App\Models\Position;
use App\Models\VacancyRequest;
use App\Models\VacancyRequestLog;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatementsController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $statements = VacancyRequest::where('requester_id', $user->id)
            ->with(['position', 'subdivision', 'state'])
            ->orderByDesc('created_at')
            ->get();

        return view('department_head.statements', compact('statements'));
    }

    public function create(Request $request)
    {
        $user = $request->user()->load([
            'branch', 'department', 'subdivision.positions', 'position',
        ]);

        // Только вакантные должности своего подразделения
        $vacantPositions = Position::where('subdivision_id', $user->subdivision_id)
            ->where('is_vacant', true)
            ->get();

        $allPositions = Position::where('subdivision_id', $user->subdivision_id)->get();

        return view('department_head.statement_create', compact(
            'user', 'vacantPositions', 'allPositions'
        ));
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'position_id'              => 'required|exists:positions,id',
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
            'languages.*.lang'         => 'required_with:languages|string',
            'languages.*.level'        => 'required_with:languages|string',
            'specialized_knowledge'    => 'nullable|string',
            'job_responsibilities'     => 'nullable|string',
            'additional_requirements'  => 'nullable|string',
            'vacancy_close_deadline'   => 'nullable|date',
        ]);

        $position = Position::findOrFail($validated['position_id']);

        DB::transaction(function () use ($validated, $user, $position) {
            $state = \App\Models\State::byKey('draft');

            $statement = VacancyRequest::create([
                ...$validated,
                'requester_id'      => $user->id,
                'branch_id'         => $user->branch_id,
                'department_id'     => $user->department_id,
                'subdivision_id'    => $user->subdivision_id,
                'position_category' => $position->category,
                'workplace'         => $user->branch?->name,
                'status'            => 'draft',
                'state_id'          => $state?->id,
            ]);

            VacancyRequestLog::create([
                'vacancy_request_id' => $statement->id,
                'user_id'            => $user->id,
                'status'             => 'draft',
                'comment'            => 'Заявка создана как черновик',
            ]);
        });

        return redirect()
            ->route('department_head.statements.index')
            ->with('success', 'Заявка сохранена как черновик.');
    }

    public function edit(VacancyRequest $statement)
    {
        // Только заявитель может редактировать черновик
        if ($statement->requester_id !== auth()->id() || !$statement->isDraft()) {
            abort(403);
        }

        $user = auth()->user()->load(['branch', 'department', 'subdivision', 'position']);

        $vacantPositions = Position::where('subdivision_id', $user->subdivision_id)
            ->where('is_vacant', true)
            ->get();

        $allPositions = Position::where('subdivision_id', $user->subdivision_id)->get();

        return view('department_head.statement_edit', compact('statement', 'user', 'vacantPositions', 'allPositions'));
    }

    public function update(Request $request, VacancyRequest $statement)
    {
        if ($statement->requester_id !== auth()->id() || !$statement->isDraft()) {
            abort(403);
        }

        $validated = $request->validate([
            'position_id'              => 'required|exists:positions,id',
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
            'languages.*.lang'         => 'required_with:languages|string',
            'languages.*.level'        => 'required_with:languages|string',
            'specialized_knowledge'    => 'nullable|string',
            'job_responsibilities'     => 'nullable|string',
            'additional_requirements'  => 'nullable|string',
            'vacancy_close_deadline'   => 'nullable|date',
        ]);

        $statement->update($validated);

        VacancyRequestLog::create([
            'vacancy_request_id' => $statement->id,
            'user_id'            => auth()->id(),
            'status'             => 'draft',
            'comment'            => 'Черновик обновлён заявителем',
        ]);

        return redirect()
            ->route('department_head.statements.show', $statement)
            ->with('success', 'Заявка обновлена.');
    }

    public function show(VacancyRequest $statement)
    {
        $statement->load(['position', 'subdivision.head', 'logs.user', 'requester', 'state', 'editedBy']);
        return view('department_head.statement_show', compact('statement'));
    }

    /**
     * DepartmentHead отправляет черновик в HR
     */
    public function submit(VacancyRequest $statement)
    {
        if ($statement->requester_id !== auth()->id() || !$statement->isDraft()) {
            abort(403);
        }

        $state = \App\Models\State::byKey('submitted');

        DB::transaction(function () use ($statement, $state) {
            $statement->update([
                'status'       => 'submitted',
                'state_id'     => $state?->id,
                'submitted_at' => now(),
            ]);

            VacancyRequestLog::create([
                'vacancy_request_id' => $statement->id,
                'user_id'            => auth()->id(),
                'status'             => 'submitted',
                'comment'            => 'Заявка отправлена в HR на рассмотрение',
            ]);

            // Уведомить всех HR
            NotificationService::onSubmittedToHr($statement);
        });

        return redirect()
            ->route('department_head.statements.show', $statement)
            ->with('success', 'Заявка отправлена в HR.');
    }

    /**
     * DepartmentHead подтверждает закрытие вакансии
     */
    public function confirmClose(VacancyRequest $statement)
    {
        if ($statement->requester_id !== auth()->id() || !$statement->isClosed()) {
            abort(403);
        }

        $state = \App\Models\State::byKey('confirmed_closed');

        DB::transaction(function () use ($statement, $state) {
            $statement->update([
                'status'   => 'confirmed_closed',
                'state_id' => $state?->id,
            ]);

            VacancyRequestLog::create([
                'vacancy_request_id' => $statement->id,
                'user_id'            => auth()->id(),
                'status'             => 'confirmed_closed',
                'comment'            => 'Заявитель подтвердил закрытие вакансии',
            ]);

            // Уведомить HR
            NotificationService::onConfirmedClosed($statement);
        });

        return redirect()
            ->route('department_head.statements.show', $statement)
            ->with('success', 'Закрытие вакансии подтверждено.');
    }
}