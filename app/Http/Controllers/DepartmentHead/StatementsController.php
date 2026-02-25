<?php
// app/Http/Controllers/DepartmentHead/StatementsController.php

namespace App\Http\Controllers\DepartmentHead;

use App\Http\Controllers\Controller;
use App\Models\Position;
use App\Models\VacancyRequest;
use App\Models\VacancyRequestLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatementsController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $statements = VacancyRequest::where('requester_id', $user->id)
            ->with(['position', 'subdivision'])
            ->orderByDesc('created_at')
            ->get();

        return view('department_head.statements', compact('statements'));
    }

    public function create(Request $request)
    {
        $user = $request->user()->load([
            'branch', 'department', 'subdivision.positions', 'subdivision.head.position', 'position',
        ]);

        $vacantPositions = Position::where('subdivision_id', $user->subdivision_id)
            ->where('is_vacant', true)
            ->get();

        $allPositions = Position::where('subdivision_id', $user->subdivision_id)->get();

        return view('department_head.statement_create', compact('user', 'vacantPositions', 'allPositions'));
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
            'salary_probation'         => 'nullable|numeric|min:0',
            'salary_after_probation'   => 'nullable|numeric|min:0',
            'bonuses'                  => 'nullable|string',
            'opening_reason'           => 'required|string',
            'age_category'             => 'nullable|string',
            'gender'                   => 'nullable|string',
            'education'                => 'nullable|string',
            'experience'               => 'nullable|string',
            'languages'                => 'nullable|array',
            'languages.*.lang'         => 'nullable|string',
            'languages.*.level'        => 'nullable|string',
            'specialized_knowledge'    => 'nullable|string',
            'job_responsibilities'     => 'nullable|string',
            'additional_requirements'  => 'nullable|string',
            'vacancy_close_deadline'   => 'nullable|date|after:today',
        ]);

        $position = Position::findOrFail($validated['position_id']);

        // Убираем пустые языки
        if (!empty($validated['languages'])) {
            $validated['languages'] = array_values(array_filter(
                $validated['languages'],
                fn($l) => !empty($l['lang'])
            ));
        }

        DB::transaction(function () use ($validated, $user, $position) {
            $statement = VacancyRequest::create([
                ...$validated,
                'requester_id'      => $user->id,
                'branch_id'         => $user->branch_id,
                'department_id'     => $user->department_id,
                'subdivision_id'    => $user->subdivision_id,
                'position_category' => $position->category,
                'workplace'         => $user->branch?->name,
                'status'            => 'draft',
            ]);

            VacancyRequestLog::create([
                'vacancy_request_id' => $statement->id,
                'user_id'            => $user->id,
                'status'             => 'draft',
                'comment'            => 'Заявка создана',
            ]);
        });

        return redirect()
            ->route('department_head.statements.index')
            ->with('success', 'Заявка сохранена как черновик.');
    }

    public function show(VacancyRequest $statement)
    {
        // Заказчик видит только свои
        if (!auth()->user()->hasAnyRole(['hr_manager', 'super_admin'])) {
            abort_if($statement->requester_id !== auth()->id(), 403);
        }

        $statement->load([
            'position', 'subdivision.head.position',
            'department', 'branch', 'requester',
            'hrEditor', 'logs.user',
        ]);

        return view('department_head.statement_show', compact('statement'));
    }

    public function update(Request $request, VacancyRequest $statement)
    {
        $user = $request->user();

        // Проверка прав
        $isOwner = $statement->requester_id === $user->id && $statement->isDraft();
        $isHr    = $user->hasAnyRole(['hr_manager', 'super_admin'])
                   && in_array($statement->status, ['submitted', 'hr_reviewed']);

        abort_if(!$isOwner && !$isHr, 403);

        $validated = $request->validate([
            'grade'                    => 'nullable|integer|min:1|max:5',
            'work_schedule'            => 'nullable|string|max:50',
            'work_start'               => 'nullable|date_format:H:i',
            'work_end'                 => 'nullable|date_format:H:i',
            'salary_probation'         => 'nullable|numeric|min:0',
            'salary_after_probation'   => 'nullable|numeric|min:0',
            'bonuses'                  => 'nullable|string',
            'opening_reason'           => 'nullable|string',
            'age_category'             => 'nullable|string',
            'gender'                   => 'nullable|string',
            'education'                => 'nullable|string',
            'experience'               => 'nullable|string',
            'languages'                => 'nullable|array',
            'languages.*.lang'         => 'nullable|string',
            'languages.*.level'        => 'nullable|string',
            'specialized_knowledge'    => 'nullable|string',
            'job_responsibilities'     => 'nullable|string',
            'additional_requirements'  => 'nullable|string',
            'vacancy_close_deadline'   => 'nullable|date',
        ]);

        if (!empty($validated['languages'])) {
            $validated['languages'] = array_values(array_filter(
                $validated['languages'],
                fn($l) => !empty($l['lang'])
            ));
        }

        DB::transaction(function () use ($validated, $statement, $user, $isHr, $request) {
            $action = $request->input('action', 'save');

            // HR фиксируется как редактор
            if ($isHr) {
                $validated['hr_editor_id'] = $user->id;

                // Если HR нажал "Отправить руководителю"
                if ($action === 'send_to_head') {
                    $validated['status'] = 'hr_reviewed';
                    $statement->update($validated);

                    VacancyRequestLog::create([
                        'vacancy_request_id' => $statement->id,
                        'user_id'            => $user->id,
                        'status'             => 'hr_reviewed',
                        'comment'            => 'HR проверил и отправил руководителю',
                    ]);

                    return;
                }
            }

            $statement->update($validated);

            VacancyRequestLog::create([
                'vacancy_request_id' => $statement->id,
                'user_id'            => $user->id,
                'status'             => $statement->status,
                'comment'            => $isHr ? 'HR внёс изменения' : 'Заявка обновлена',
            ]);
        });

        $route = auth()->user()->hasAnyRole(['hr_manager', 'super_admin'])
            ? route('hr.statements.show', $statement)
            : route('department_head.statements.show', $statement);

        return redirect($route)->with('success', 'Изменения сохранены.');
    }

    public function submit(VacancyRequest $statement)
    {
        abort_if($statement->requester_id !== auth()->id(), 403);
        abort_if(!$statement->isDraft(), 403);

        DB::transaction(function () use ($statement) {
            $statement->update([
                'status'       => 'submitted',
                'submitted_at' => now(),
            ]);

            VacancyRequestLog::create([
                'vacancy_request_id' => $statement->id,
                'user_id'            => auth()->id(),
                'status'             => 'submitted',
                'comment'            => 'Заявка отправлена на рассмотрение в HR',
            ]);
        });

        return redirect()
            ->route('department_head.statements.show', $statement)
            ->with('success', 'Заявка отправлена в HR.');
    }
}