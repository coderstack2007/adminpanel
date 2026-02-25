<?php
// app/Models/VacancyRequest.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VacancyRequest extends Model
{
    protected $fillable = [
        'requester_id', 'branch_id', 'department_id', 'subdivision_id', 'position_id',
        'reports_to', 'subordinates',
        'work_schedule', 'work_start', 'work_end',
        'position_category', 'grade', 'daily_rate',
        'salary_probation', 'salary_after_probation',
        'bonuses', 'workplace', 'opening_reason',
        'age_category', 'gender', 'education', 'experience',
        'languages', 'specialized_knowledge', 'job_responsibilities', 'additional_requirements',
        'status', 'hr_editor_id', 'submitted_at', 'approved_at', 'closed_at',
        'vacancy_close_deadline',
    ];

    protected $casts = [
        'subordinates'        => 'array',
        'languages'           => 'array',
        'submitted_at'        => 'datetime',
        'approved_at'         => 'datetime',
        'closed_at'           => 'datetime',
        'vacancy_close_deadline' => 'date',
    ];

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

    // ─── Отношения ────────────────────────────────────────────
    public function requester()   { return $this->belongsTo(User::class, 'requester_id'); }
    public function branch()      { return $this->belongsTo(Branch::class); }
    public function department()  { return $this->belongsTo(Department::class); }
    public function subdivision() { return $this->belongsTo(Subdivision::class); }
    public function position()    { return $this->belongsTo(Position::class); }
    public function hrEditor()    { return $this->belongsTo(User::class, 'hr_editor_id'); }
    public function logs()        { return $this->hasMany(VacancyRequestLog::class)->orderByDesc('created_at'); }

    // ─── Хелперы ─────────────────────────────────────────────
    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'draft'            => 'secondary',
            'submitted'        => 'info',
            'hr_reviewed'      => 'primary',
            'approved'         => 'success',
            'rejected'         => 'danger',
            'on_hold'          => 'warning',
            'searching'        => 'primary',
            'closed'           => 'dark',
            'confirmed_closed' => 'success',
            default            => 'secondary',
        };
    }

    const STATUS_COLORS = [
    'draft'            => 'secondary',
    'submitted'        => 'info',
    'hr_reviewed'      => 'primary',
    'approved'         => 'success',
    'rejected'         => 'danger',
    'on_hold'          => 'warning',
    'searching'        => 'primary',
    'closed'           => 'dark',
    'confirmed_closed' => 'success',
];

    public function isDraft(): bool      { return $this->status === 'draft'; }
    public function isSubmitted(): bool  { return $this->status === 'submitted'; }
    public function isEditable(): bool   { return in_array($this->status, ['draft', 'hr_reviewed']); }
}