<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VacancyRequest extends Model
{
    protected $fillable = [
        'requester_id', 'branch_id', 'department_id', 'subdivision_id', 'position_id',
        'reports_to', 'subordinates', 'work_schedule', 'work_start', 'work_end',
        'position_category', 'grade', 'daily_rate', 'salary_probation', 'salary_after_probation',
        'bonuses', 'workplace', 'opening_reason', 'age_category', 'gender',
        'education', 'experience', 'languages', 'specialized_knowledge',
        'job_responsibilities', 'additional_requirements', 'vacancy_close_deadline',
        'status', 'state_id', 'hr_editor_id', 'edited_by',
        'supervisor_id', 'supervisor_comment', 'supervisor_reviewed_at', 'sent_to_supervisor_at',
        'submitted_at', 'approved_at', 'closed_at',
    ];

    protected $casts = [
        'subordinates'            => 'array',
        'languages'               => 'array',
        'submitted_at'            => 'datetime',
        'approved_at'             => 'datetime',
        'closed_at'               => 'datetime',
        'supervisor_reviewed_at'  => 'datetime',
        'sent_to_supervisor_at'   => 'datetime',
        'vacancy_close_deadline'  => 'date',
    ];

    // ─── Relationships ────────────────────────────────────────

    public function requester()     { return $this->belongsTo(User::class, 'requester_id'); }
    public function branch()        { return $this->belongsTo(Branch::class); }
    public function department()    { return $this->belongsTo(Department::class); }
    public function subdivision()   { return $this->belongsTo(Subdivision::class); }
    public function position()      { return $this->belongsTo(Position::class); }
    public function hrEditor()      { return $this->belongsTo(User::class, 'hr_editor_id'); }
    public function editedBy()      { return $this->belongsTo(User::class, 'edited_by'); }
    public function supervisor()    { return $this->belongsTo(User::class, 'supervisor_id'); }
    public function state()         { return $this->belongsTo(State::class); }
    public function logs()          { return $this->hasMany(VacancyRequestLog::class)->orderBy('created_at'); }
    public function notifications() { return $this->hasMany(Notification::class); }

    // ─── Status helpers ───────────────────────────────────────

    public function isDraft(): bool              { return $this->status === 'draft'; }
    public function isSubmitted(): bool          { return $this->status === 'submitted'; }
    public function isHrReviewed(): bool         { return $this->status === 'hr_reviewed'; }
    public function isSupervisorReview(): bool   { return $this->status === 'supervisor_review'; }
    public function isApproved(): bool           { return $this->status === 'approved'; }
    public function isRejected(): bool           { return $this->status === 'rejected'; }
    public function isOnHold(): bool             { return $this->status === 'on_hold'; }
    public function isSearching(): bool          { return $this->status === 'searching'; }
    public function isClosed(): bool             { return $this->status === 'closed'; }
    public function isConfirmedClosed(): bool    { return $this->status === 'confirmed_closed'; }

    // DepartmentHead может редактировать только черновик
    public function canEditByRequester(): bool   { return $this->isDraft(); }

    // HR может редактировать до отправки supervisor
    public function canEditByHr(): bool          { return in_array($this->status, ['submitted', 'hr_reviewed']); }

    // ─── Status label & color (из state или fallback) ─────────

    public function getStatusLabelAttribute(): string
    {
        if ($this->state) {
            return $this->state->label_ru;
        }
        return match($this->status) {
            'draft'              => 'Черновик',
            'submitted'          => 'Отправлена в HR',
            'hr_reviewed'        => 'HR рассматривает',
            'supervisor_review'  => 'На подписи у руководителя',
            'approved'           => 'Одобрена',
            'rejected'           => 'Отклонена',
            'on_hold'            => 'Приостановлена',
            'searching'          => 'Идёт поиск',
            'closed'             => 'HR закрыл',
            'confirmed_closed'   => 'Закрыта',
            default              => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        if ($this->state) {
            return $this->state->color;
        }
        return match($this->status) {
            'draft'              => 'secondary',
            'submitted'          => 'info',
            'hr_reviewed'        => 'primary',
            'supervisor_review'  => 'warning',
            'approved'           => 'success',
            'rejected'           => 'danger',
            'on_hold'            => 'warning',
            'searching'          => 'primary',
            'closed'             => 'secondary',
            'confirmed_closed'   => 'dark',
            default              => 'secondary',
        };
    }
    // ─── Статусы ──────────────────────────────────────────────
    const STATUS_LABELS = [
        'draft'            => 'Черновик',
        'submitted'        => 'Отправлена',
        'hr_reviewed'      => 'На проверке HR',
        'approved'         => 'Одобрена',
        'rejected'         => 'Отклонена',
        'on_hold'          => 'Приостановлена',
        'searching'        => 'Поиск начат',
        'closed'           => 'Вакансия закрыта',
        'confirmed_closed' => 'Закрытие подтверждено',
    ];

    const OPENING_REASON_LABELS = [
        'employee_resigned'   => 'Уволился сотрудник',
        'new_position'        => 'Новая должность',
        'workload_increased'  => 'Увеличился объём работы',
        'rotation'            => 'Ротация сотрудника',
        'handover_needed'     => 'Передача дел новому сотруднику',
        'other'               => 'Другое',
    ];


    const STATUS_COLORS = [
    'draft'            => 'secondary',
    'submitted'        => 'info',
    'hr_reviewed'      => 'primary',

    'rejected'         => 'danger',
    'on_hold'          => 'warning',
    'searching'        => 'primary',
    'closed'           => 'dark',
    'confirmed_closed' => 'success',
];
    // ─── Sync state_id from states table ──────────────────────

    public function syncState(): void
    {
        $state = State::byKey($this->status);
        if ($state) {
            $this->update(['state_id' => $state->id]);
        }
    }
}