{{-- resources/views/supervisor/statement_show.blade.php --}}

@push('styles')
<style>
    .card { background-color: rgb(31, 41, 65) !important; color: #f9fafb !important; }
    .card-header { background-color: rgb(31, 41, 55) !important; border-bottom-color: rgba(255,255,255,0.1) !important; color: #f9fafb !important; }
    .form-control, .form-select { background-color: rgb(17, 24, 39) !important; border-color: rgba(255,255,255,0.15) !important; color: #f9fafb !important; }
    .form-control:focus, .form-select:focus { border-color: #6366f1 !important; box-shadow: 0 0 0 0.25rem rgba(99,102,241,0.25) !important; }
    .form-label { color: #d1d5db !important; }
    .info-label { color: #9ca3af; font-size: 0.82rem; margin-bottom: 0.2rem; }
    .info-value { font-weight: 600; color: #f9fafb; font-size: 0.9rem; }
    .info-badge { background-color: rgb(17, 24, 39); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; padding: 0.5rem 1rem; color: #f9fafb; font-size: 0.85rem; }
    .section-title { font-size: 0.7rem; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: #6366f1; margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 1px solid rgba(99,102,241,0.3); }
    hr { border-color: rgba(255,255,255,0.08) !important; }
    .status-pill { display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.35rem 0.9rem; border-radius: 999px; font-size: 0.78rem; font-weight: 600; }
    .log-line { position: relative; padding-left: 1.5rem; padding-bottom: 1rem; }
    .log-line::before { content: ''; position: absolute; left: 0.35rem; top: 0.5rem; bottom: 0; width: 2px; background: rgba(99,102,241,0.25); }
    .log-line:last-child::before { display: none; }
    .log-dot { position: absolute; left: 0; top: 0.35rem; width: 0.75rem; height: 0.75rem; border-radius: 50%; background: #6366f1; border: 2px solid rgb(31,41,55); }
    .decision-textarea {
        background: rgb(17,24,39) !important;
        color: #f9fafb !important;
        border-color: rgba(255,255,255,0.15) !important;
        resize: vertical;
    }
    .decision-textarea:focus { border-color: #6366f1 !important; box-shadow: 0 0 0 0.2rem rgba(99,102,241,0.2) !important; }
</style>
@endpush

<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>
                <h2 class="h5 fw-semibold mb-1">
                    <i class="bi bi-file-earmark-check me-2"></i>Заявка #{{ $statement->id }}
                </h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0" style="font-size:0.8rem;">
                        <li class="breadcrumb-item">
                            <a href="{{ route('supervisor.statements.index') }}" style="color:#6366f1">Заявки на согласование</a>
                        </li>
                        <li class="breadcrumb-item active" style="color:#9ca3af">#{{ $statement->id }}</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex align-items-center gap-2" >
                <span style="color: #fff;" class="status-pill bg-{{ $statement->status_color }} bg-opacity-20 text-{{ $statement->status_color }}">
                    <span class="rounded-circle d-inline-block" style="width:6px;height:6px;background:currentColor"></span>
                    {{ $statement->status_label }}
                </span>
                <a href="{{ route('supervisor.statements.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Назад
                </a>
            </div>
        </div>
    </x-slot>

    <div class="container-fluid py-4">

        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <div class="row g-4">

            {{-- ЛЕВАЯ КОЛОНКА: Информация о заявке (только чтение) --}}
            <div class="col-lg-8">

                {{-- Должность и организация --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between mb-3 flex-wrap gap-2">
                            <div>
                                <h5 class="mb-1 fw-bold" style="color:#fff">{{ $statement->position?->name ?? '—' }}</h5>
                                @if($statement->editedBy)
                                    <span class="badge bg-warning text-dark">
                                        <i class="bi bi-pencil me-1"></i>Редактировал HR: {{ $statement->editedBy->name }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="row g-3">
                            <div class="col-sm-4">
                                <div class="info-label">Заявитель</div>
                                <div class="info-value">{{ $statement->requester?->name ?? '—' }}</div>
                            </div>
                            <div class="col-sm-4">
                                <div class="info-label">Отдел</div>
                                <div class="info-value">{{ $statement->department?->name ?? '—' }}</div>
                            </div>
                            <div class="col-sm-4">
                                <div class="info-label">Подразделение</div>
                                <div class="info-value">{{ $statement->subdivision?->name ?? '—' }}</div>
                            </div>
                            <div class="col-sm-4">
                                <div class="info-label">Категория</div>
                                <div class="info-value">{{ $statement->position_category ?? '—' }}</div>
                            </div>
                            <div class="col-sm-4">
                                <div class="info-label">Разряд</div>
                                <div class="info-value">{{ $statement->grade ? $statement->grade.'-й' : '—' }}</div>
                            </div>
                            <div class="col-sm-4">
                                <div class="info-label">Место работы</div>
                                <div class="info-value">{{ $statement->workplace ?? '—' }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Должностные связи --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header py-3">
                        <h6 class="mb-0 fw-semibold"><i class="bi bi-diagram-3 me-2 text-primary"></i>Должностные связи</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <div class="info-label">Кому подчиняется</div>
                                <div class="info-value">{{ $statement->reports_to ?? '—' }}</div>
                            </div>
                            <div class="col-sm-6">
                                <div class="info-label">Подчинённые</div>
                                <div class="info-value">
                                    {{ $statement->subordinates ? implode(', ', $statement->subordinates) : '—' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Условия работы --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header py-3">
                        <h6 class="mb-0 fw-semibold"><i class="bi bi-cash-coin me-2 text-primary"></i>Условия работы</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-sm-3">
                                <div class="info-label">График</div>
                                <div class="info-value">{{ $statement->work_schedule ?? '—' }}</div>
                            </div>
                            <div class="col-sm-3">
                                <div class="info-label">Время</div>
                                <div class="info-value">
                                    {{ $statement->work_start ? substr($statement->work_start,0,5) : '—' }}
                                    –
                                    {{ $statement->work_end ? substr($statement->work_end,0,5) : '—' }}
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <div class="info-label">Причина открытия</div>
                                <div class="info-value">{{ \App\Models\VacancyRequest::OPENING_REASON_LABELS[$statement->opening_reason] ?? '—' }}</div>
                            </div>
                            <div class="col-sm-3">
                                <div class="info-label">Срок закрытия</div>
                                <div class="info-value">{{ $statement->vacancy_close_deadline?->format('d.m.Y') ?? '—' }}</div>
                            </div>
                            <div class="col-sm-4">
                                <div class="info-label">На испытательный срок</div>
                                <div class="info-value">
                                    {{ $statement->salary_probation ? number_format($statement->salary_probation,0,'.',' ').' сум' : '—' }}
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="info-label">После испытания</div>
                                <div class="info-value">
                                    {{ $statement->salary_after_probation ? number_format($statement->salary_after_probation,0,'.',' ').' сум' : '—' }}
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="info-label">Бонусы</div>
                                <div class="info-value">{{ $statement->bonuses ?? '—' }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Требования --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header py-3">
                        <h6 class="mb-0 fw-semibold"><i class="bi bi-person-check me-2 text-primary"></i>Требования к кандидату</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3 mb-3">
                            <div class="col-sm-4">
                                <div class="info-label">Возраст</div>
                                <div class="info-value">{{ $statement->age_category ?? '—' }}</div>
                            </div>
                            <div class="col-sm-4">
                                <div class="info-label">Пол</div>
                                <div class="info-value">
                                    {{ $statement->gender === 'male' ? 'Мужской' : ($statement->gender === 'female' ? 'Женский' : 'Не важно') }}
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="info-label">Образование</div>
                                <div class="info-value">{{ $statement->education ?? '—' }}</div>
                            </div>
                            <div class="col-12">
                                <div class="info-label">Опыт работы</div>
                                <div class="info-value">{{ $statement->experience ?? '—' }}</div>
                            </div>
                            @if($statement->languages)
                            <div class="col-12">
                                <div class="info-label mb-2">Языки</div>
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach($statement->languages as $lang)
                                        <span class="badge bg-primary">{{ $lang['lang'] ?? '' }} — {{ $lang['level'] ?? '' }}</span>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                        </div>

                        @if($statement->specialized_knowledge)
                        <hr>
                        <div class="mb-3">
                            <div class="info-label">Специализированные знания</div>
                            <div class="info-value" style="white-space:pre-line">{{ $statement->specialized_knowledge }}</div>
                        </div>
                        @endif

                        @if($statement->job_responsibilities)
                        <hr>
                        <div class="mb-3">
                            <div class="info-label">Должностные обязанности</div>
                            <div class="info-value" style="white-space:pre-line">{{ $statement->job_responsibilities }}</div>
                        </div>
                        @endif

                        @if($statement->additional_requirements)
                        <hr>
                        <div>
                            <div class="info-label">Дополнительные требования</div>
                            <div class="info-value" style="white-space:pre-line">{{ $statement->additional_requirements }}</div>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- История --}}
                <div class="card border-0 shadow-sm">
                    <div class="card-header py-3">
                        <h6 class="mb-0 fw-semibold"><i class="bi bi-clock-history me-2 text-primary"></i>История заявки</h6>
                    </div>
                    <div class="card-body">
                        @forelse($statement->logs as $log)
                        <div class="log-line">
                            <div class="log-dot"></div>
                            <div class="d-flex align-items-start justify-content-between gap-2">
                                <div>
                                    <span class="badge bg-{{ \App\Models\VacancyRequest::STATUS_COLORS[$log->status] ?? 'secondary' }} bg-opacity-80 me-2">
                                        {{ \App\Models\VacancyRequest::STATUS_LABELS[$log->status] ?? $log->status }}
                                    </span>
                                    <span class="small" style="color:#d1d5db">{{ $log->user?->name }}</span>
                                    @if($log->comment)
                                        <div class="text-muted small mt-1">{{ $log->comment }}</div>
                                    @endif
                                </div>
                                <span class="text-muted small text-nowrap">{{ $log->created_at->format('d.m.Y H:i') }}</span>
                            </div>
                        </div>
                        @empty
                        <div class="text-muted small text-center py-3">История пуста</div>
                        @endforelse
                    </div>
                </div>

            </div>

            {{-- ПРАВАЯ КОЛОНКА: Действия и мета --}}
            <div class="col-lg-4">

                {{-- ════ КНОПКИ РЕШЕНИЯ (только если на рассмотрении) ════ --}}
                @if($statement->isSupervisorReview())
                <div class="card border-0 shadow-sm mb-4" style="border:1px solid rgba(234,179,8,0.3) !important">
                    <div class="card-header py-3" style="background:rgba(234,179,8,0.06) !important">
                        <h6 class="mb-0 fw-semibold text-warning">
                            <i class="bi bi-exclamation-circle me-2"></i>Требует вашего решения
                        </h6>
                    </div>
                    <div class="card-body d-flex flex-column gap-3">

                        {{-- Одобрить --}}
                        <form method="POST" action="{{ route('supervisor.statements.approve', $statement) }}">
                            @csrf
                            <div class="mb-2">
                                <label class="form-label small fw-semibold">Комментарий (необязательно)</label>
                                <textarea name="comment" class="form-control decision-textarea" rows="2"
                                    placeholder="Добавьте комментарий..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-success w-100"
                                onclick="return confirm('Одобрить заявку?')">
                                <i class="bi bi-check-circle me-2"></i>Одобрить
                            </button>
                        </form>

                        <hr>

                        {{-- Отклонить --}}
                        <form method="POST" action="{{ route('supervisor.statements.reject', $statement) }}">
                            @csrf
                            <div class="mb-2">
                                <label class="form-label small fw-semibold">Причина отклонения <span class="text-danger">*</span></label>
                                <textarea name="comment" class="form-control decision-textarea" rows="2"
                                    placeholder="Укажите причину..." required></textarea>
                            </div>
                            <button type="submit" class="btn btn-danger w-100"
                                onclick="return confirm('Отклонить заявку?')">
                                <i class="bi bi-x-circle me-2"></i>Отклонить
                            </button>
                        </form>

                        <hr>

                        {{-- Приостановить --}}
                        <form method="POST" action="{{ route('supervisor.statements.on-hold', $statement) }}">
                            @csrf
                            <div class="mb-2">
                                <label class="form-label small fw-semibold">Причина (необязательно)</label>
                                <textarea name="comment" class="form-control decision-textarea" rows="2"
                                    placeholder="Добавьте комментарий..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-warning w-100"
                                onclick="return confirm('Приостановить заявку?')">
                                <i class="bi bi-pause-circle me-2"></i>Приостановить
                            </button>
                        </form>

                    </div>
                </div>
                @endif

                {{-- Принятое решение (если уже есть) --}}
                @if(!$statement->isSupervisorReview() && $statement->supervisor_comment)
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header py-3">
                        <h6 class="mb-0 fw-semibold"><i class="bi bi-chat-quote me-2 text-primary"></i>Ваш комментарий</h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-2" style="white-space:pre-line;color:#f9fafb">{{ $statement->supervisor_comment }}</p>
                        @if($statement->supervisor_reviewed_at)
                            <div class="text-muted small">
                                <i class="bi bi-clock me-1"></i>{{ $statement->supervisor_reviewed_at->format('d.m.Y H:i') }}
                            </div>
                        @endif
                    </div>
                </div>
                @endif

                {{-- Инфо о заявителе и HR --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header py-3">
                        <h6 class="mb-0 fw-semibold"><i class="bi bi-person me-2 text-primary"></i>Участники</h6>
                    </div>
                    <div class="card-body d-flex flex-column gap-3">
                        <div>
                            <div class="info-label">Заявитель</div>
                            <div class="info-value">{{ $statement->requester?->name ?? '—' }}</div>
                            @if($statement->requester?->email)
                                <div class="text-muted small">{{ $statement->requester->email }}</div>
                            @endif
                        </div>
                        @if($statement->editedBy)
                        <div>
                            <div class="info-label">Редактировал HR</div>
                            <div class="info-value" style="color:#818cf8">{{ $statement->editedBy->name }}</div>
                        </div>
                        @endif
                        @if($statement->sent_to_supervisor_at)
                        <div>
                            <div class="info-label">Отправлено на согласование</div>
                            <div class="info-value">{{ $statement->sent_to_supervisor_at->format('d.m.Y H:i') }}</div>
                        </div>
                        @endif
                        <div>
                            <div class="info-label">Создана</div>
                            <div class="info-value">{{ $statement->created_at->format('d.m.Y H:i') }}</div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>