{{-- resources/views/hr/statement_show.blade.php --}}

@push('styles')
<style>
    .card { background-color: rgb(31, 41, 65) !important; color: #f9fafb !important; }
    .card-header { background-color: rgb(31, 41, 55) !important; border-bottom-color: rgba(255,255,255,0.1) !important; color: #f9fafb !important; }
    .form-control, .form-select { background-color: rgb(17, 24, 39) !important; border-color: rgba(255,255,255,0.15) !important; color: #f9fafb !important; }
    .form-control:focus, .form-select:focus { border-color: #6366f1 !important; box-shadow: 0 0 0 0.25rem rgba(99,102,241,0.25) !important; }
    .form-select option { background-color: rgb(17, 24, 39); color: #f9fafb; }
    .form-label { color: #d1d5db !important; }
    .info-badge { background-color: rgb(17, 24, 39); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; padding: 0.5rem 1rem; color: #f9fafb; font-size: 0.85rem; }
    .section-title { font-size: 0.7rem; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: #6366f1; margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 1px solid rgba(99,102,241,0.3); }
    .lang-row { background: rgb(17,24,39); border-radius: 8px; padding: 0.75rem; margin-bottom: 0.5rem; }
    hr { border-color: rgba(255,255,255,0.08) !important; }
    .hr-editable { border-color: rgba(99,102,241,0.4) !important; }
    .hr-editable:focus { border-color: #6366f1 !important; }
    .status-pill { display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.35rem 0.9rem; border-radius: 999px; font-size: 0.78rem; font-weight: 600; }
    .log-line { position: relative; padding-left: 1.5rem; padding-bottom: 1rem; }
    .log-line::before { content: ''; position: absolute; left: 0.35rem; top: 0.5rem; bottom: 0; width: 2px; background: rgba(99,102,241,0.25); }
    .log-line:last-child::before { display: none; }
    .log-dot { position: absolute; left: 0; top: 0.35rem; width: 0.75rem; height: 0.75rem; border-radius: 50%; background: #6366f1; border: 2px solid rgb(31,41,55); }
</style>
@endpush

<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>
                <h2 class="h5 fw-semibold mb-1">
                    <i class="bi bi-file-earmark-text me-2"></i>Заявка #{{ $statement->id }}
                </h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0" style="font-size:0.8rem;">
                        <li class="breadcrumb-item">
                            <a href="{{ route('hr.statements.index') }}" style="color:#6366f1">HR Заявки</a>
                        </li>
                        <li class="breadcrumb-item active" style="color:#9ca3af">#{{ $statement->id }}</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex align-items-center gap-2" style="color: #fff;">
                <span  class="status-pill bg-{{ $statement->status_color }} bg-opacity-20 ">
                    <span  class="rounded-circle d-inline-block" style="width:6px;height:6px;background:currentColor"></span>
                    {{ $statement->status_label }}
                </span>
                @if($statement->editedBy)
                    <span class="badge bg-warning text-dark"><i class="bi bi-pencil me-1"></i>{{ $statement->editedBy->name }}</span>
                @endif
                <a href="{{ route('hr.statements.index') }}" class="btn btn-sm btn-outline-secondary">
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
        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-4">
            <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        @php
            // HR может редактировать пока не отправлено supervisor'у
            $hrCanEdit = in_array($statement->status, ['submitted', 'hr_reviewed']);
        @endphp

        <div class="row g-4">
            <div class="col-lg-8">

                {{-- ════ ФОРМА РЕДАКТИРОВАНИЯ (PUT → hr.statements.update) ════ --}}
                <form method="POST" action="{{ route('hr.statements.update', $statement) }}">
                    @csrf
                    @method('PUT')

                    {{-- БЛОК 1: Основная информация --}}
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header py-3">
                            <h6 class="mb-0 fw-semibold"><i class="bi bi-person-badge me-2 text-primary"></i>Основная информация</h6>
                        </div>
                        <div class="card-body">
                            <p class="section-title">Заявитель</p>
                            <div class="row g-3 mb-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Ф.И.О.</label>
                                    <div class="info-badge"><i class="bi bi-person me-2 text-primary"></i>{{ $statement->requester->name }}</div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Отдел</label>
                                    <div class="info-badge">{{ $statement->department?->name ?? '—' }}</div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Подразделение</label>
                                    <div class="info-badge">{{ $statement->subdivision?->name ?? '—' }}</div>
                                </div>
                            </div>
                            <hr>
                            <p class="section-title mt-3">Вакантная должность</p>
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Должность</label>
                                    <div class="info-badge fw-semibold">{{ $statement->position?->name ?? '—' }}</div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Категория</label>
                                    <div class="info-badge">
                                        @php $colors = ['A'=>'#7c3aed','B'=>'#1d4ed8','C'=>'#065f46','D'=>'#92400e']; @endphp
                                        @if($statement->position_category)
                                            <span class="badge text-white" style="background:{{ $colors[$statement->position_category] ?? '#6b7280' }}">Кат. {{ $statement->position_category }}</span>
                                        @else —
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Разряд</label>
                                    @if($hrCanEdit)
                                        <select class="form-select hr-editable" name="grade">
                                            <option value="">—</option>
                                            @foreach(range(1,5) as $g)
                                                <option value="{{ $g }}" {{ $statement->grade == $g ? 'selected' : '' }}>{{ $g }}-й разряд</option>
                                            @endforeach
                                        </select>
                                    @else
                                        <div class="info-badge">{{ $statement->grade ? $statement->grade.'-й разряд' : '—' }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Кому подчиняется</label>
                                    <div class="info-badge">{{ $statement->reports_to ?: '—' }}</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Кто подчиняется</label>
                                    <div class="info-badge">{{ $statement->subordinates ? implode(', ', $statement->subordinates) : '—' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- БЛОК 2: График и зарплата --}}
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header py-3">
                            <h6 class="mb-0 fw-semibold"><i class="bi bi-clock me-2 text-primary"></i>График и зарплата</h6>
                        </div>
                        <div class="card-body">
                            <p class="section-title">Рабочий график</p>
                            <div class="row g-3 mb-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">График</label>
                                    @if($hrCanEdit)
                                        <select class="form-select hr-editable" name="work_schedule">
                                            <option value="">—</option>
                                            @foreach(['5/2','6/1','7/0','2/2'] as $sch)
                                                <option value="{{ $sch }}" {{ $statement->work_schedule === $sch ? 'selected' : '' }}>{{ $sch }}</option>
                                            @endforeach
                                        </select>
                                    @else
                                        <div class="info-badge">{{ $statement->work_schedule ?? '—' }}</div>
                                    @endif
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Начало</label>
                                    @if($hrCanEdit)
                                        <input type="time" class="form-control hr-editable" name="work_start" value="{{ $statement->work_start }}">
                                    @else
                                        <div class="info-badge">{{ $statement->work_start ?? '—' }}</div>
                                    @endif
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Конец</label>
                                    @if($hrCanEdit)
                                        <input type="time" class="form-control hr-editable" name="work_end" value="{{ $statement->work_end }}">
                                    @else
                                        <div class="info-badge">{{ $statement->work_end ?? '—' }}</div>
                                    @endif
                                </div>
                            </div>
                            <hr>
                            <p class="section-title mt-3">Заработная плата</p>
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">На испытательный срок</label>
                                    @if($hrCanEdit)
                                        <div class="input-group">
                                            <input type="number" name="salary_probation" class="form-control hr-editable" value="{{ $statement->salary_probation }}" min="0">
                                            <span class="input-group-text" style="background:rgb(17,24,39);color:#9ca3af;border-color:rgba(255,255,255,0.15)">сум</span>
                                        </div>
                                    @else
                                        <div class="info-badge">{{ $statement->salary_probation ? number_format($statement->salary_probation,0,'.',' ').' сум' : '—' }}</div>
                                    @endif
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">После испытательного</label>
                                    @if($hrCanEdit)
                                        <div class="input-group">
                                            <input type="number" name="salary_after_probation" class="form-control hr-editable" value="{{ $statement->salary_after_probation }}" min="0">
                                            <span class="input-group-text" style="background:rgb(17,24,39);color:#9ca3af;border-color:rgba(255,255,255,0.15)">сум</span>
                                        </div>
                                    @else
                                        <div class="info-badge">{{ $statement->salary_after_probation ? number_format($statement->salary_after_probation,0,'.',' ').' сум' : '—' }}</div>
                                    @endif
                                </div>
                            </div>
                            <div>
                                <label class="form-label fw-semibold">Бонусы и льготы</label>
                                @if($hrCanEdit)
                                    <textarea class="form-control hr-editable" name="bonuses" rows="3">{{ $statement->bonuses }}</textarea>
                                @else
                                    <div class="info-badge" style="min-height:60px;white-space:pre-line">{{ $statement->bonuses ?: '—' }}</div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- БЛОК 3: Требования --}}
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header py-3">
                            <h6 class="mb-0 fw-semibold"><i class="bi bi-person-check me-2 text-primary"></i>Требования к кандидату</h6>
                        </div>
                        <div class="card-body">
                            <p class="section-title">Основные данные</p>
                            <div class="row g-3 mb-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Возраст</label>
                                    @if($hrCanEdit)
                                        <input type="text" class="form-control hr-editable" name="age_category" value="{{ $statement->age_category }}" placeholder="25–40">
                                    @else
                                        <div class="info-badge">{{ $statement->age_category ?? '—' }}</div>
                                    @endif
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Пол</label>
                                    @if($hrCanEdit)
                                        <select class="form-select hr-editable" name="gender">
                                            <option value="">— Не важно —</option>
                                            <option value="male"   {{ $statement->gender === 'male'   ? 'selected' : '' }}>Мужской</option>
                                            <option value="female" {{ $statement->gender === 'female' ? 'selected' : '' }}>Женский</option>
                                        </select>
                                    @else
                                        <div class="info-badge">{{ $statement->gender === 'male' ? 'Мужской' : ($statement->gender === 'female' ? 'Женский' : 'Не важно') }}</div>
                                    @endif
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Образование</label>
                                    @if($hrCanEdit)
                                        <select class="form-select hr-editable" name="education">
                                            <option value="">—</option>
                                            @foreach(['Среднее','Среднее специальное','Высшее','Магистратура'] as $edu)
                                                <option value="{{ $edu }}" {{ $statement->education === $edu ? 'selected' : '' }}>{{ $edu }}</option>
                                            @endforeach
                                        </select>
                                    @else
                                        <div class="info-badge">{{ $statement->education ?? '—' }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Опыт работы</label>
                                @if($hrCanEdit)
                                    <input type="text" class="form-control hr-editable" name="experience" value="{{ $statement->experience }}">
                                @else
                                    <div class="info-badge">{{ $statement->experience ?? '—' }}</div>
                                @endif
                            </div>

                            <hr>
                            <p class="section-title mt-3">Языки</p>
                            @if($hrCanEdit)
                                <div id="languages_container">
                                    @foreach(($statement->languages ?? [['lang'=>'','level'=>'']]) as $i => $lang)
                                    <div class="lang-row d-flex align-items-center gap-2 mb-2">
                                        <select class="form-select hr-editable" name="languages[{{ $i }}][lang]" style="max-width:160px">
                                            <option value="">— Язык —</option>
                                            @foreach(['Русский','Английский','Узбекский','Другой'] as $l)
                                                <option value="{{ $l }}" {{ ($lang['lang'] ?? '') === $l ? 'selected' : '' }}>{{ $l }}</option>
                                            @endforeach
                                        </select>
                                        <select class="form-select hr-editable" name="languages[{{ $i }}][level]" style="max-width:180px">
                                            <option value="">— Уровень —</option>
                                            @foreach(['Начальный','Средний','Свободный'] as $lv)
                                                <option value="{{ $lv }}" {{ ($lang['level'] ?? '') === $lv ? 'selected' : '' }}>{{ $lv }}</option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-sm btn-outline-danger remove-lang"><i class="bi bi-x"></i></button>
                                    </div>
                                    @endforeach
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary mt-1" id="add_lang">
                                    <i class="bi bi-plus me-1"></i>Добавить язык
                                </button>
                            @else
                                @if($statement->languages)
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach($statement->languages as $lang)
                                            <span class="info-badge"><i class="bi bi-translate me-1 text-primary"></i>{{ $lang['lang'] }} — {{ $lang['level'] }}</span>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-muted small">—</div>
                                @endif
                            @endif

                            <hr class="mt-4">
                            <p class="section-title mt-3">Профессиональные требования</p>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Специализированные знания</label>
                                @if($hrCanEdit)
                                    <textarea class="form-control hr-editable" name="specialized_knowledge" rows="3">{{ $statement->specialized_knowledge }}</textarea>
                                @else
                                    <div class="info-badge" style="min-height:60px;white-space:pre-line">{{ $statement->specialized_knowledge ?: '—' }}</div>
                                @endif
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Должностные обязанности</label>
                                @if($hrCanEdit)
                                    <textarea class="form-control hr-editable" name="job_responsibilities" rows="4">{{ $statement->job_responsibilities }}</textarea>
                                @else
                                    <div class="info-badge" style="min-height:80px;white-space:pre-line">{{ $statement->job_responsibilities ?: '—' }}</div>
                                @endif
                            </div>
                            <div>
                                <label class="form-label fw-semibold">Дополнительные требования</label>
                                @if($hrCanEdit)
                                    <textarea class="form-control hr-editable" name="additional_requirements" rows="3">{{ $statement->additional_requirements }}</textarea>
                                @else
                                    <div class="info-badge" style="min-height:60px;white-space:pre-line">{{ $statement->additional_requirements ?: '—' }}</div>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if($hrCanEdit)
                    <div class="d-grid mb-4">
                        <button type="submit" class="btn btn-outline-secondary btn-lg">
                            <i class="bi bi-save me-2"></i>Сохранить изменения
                        </button>
                    </div>
                    @endif
                </form>
                {{-- ════ КОНЕЦ ФОРМЫ РЕДАКТИРОВАНИЯ ════ --}}

                {{-- БЛОК 4: История --}}
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
                                    <span class="small" style="color:#d1d5db">{{ $log->user->name }}</span>
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

            {{-- ПРАВАЯ КОЛОНКА --}}
            <div class="col-lg-4">

                {{-- Детали --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header py-3">
                        <h6 class="mb-0 fw-semibold"><i class="bi bi-info-circle me-2 text-primary"></i>Детали</h6>
                    </div>
                    <div class="card-body d-flex flex-column gap-3">
                        <div>
                            <label class="form-label fw-semibold">Причина открытия</label>
                            <div class="info-badge">{{ \App\Models\VacancyRequest::OPENING_REASON_LABELS[$statement->opening_reason] ?? '—' }}</div>
                        </div>
                        <div>
                            <label class="form-label fw-semibold">Место работы</label>
                            <div class="info-badge"><i class="bi bi-geo-alt me-1 text-warning"></i>{{ $statement->workplace ?? '—' }}</div>
                        </div>
                        <div>
                            <label class="form-label fw-semibold">Срок закрытия</label>
                            <div class="info-badge">{{ $statement->vacancy_close_deadline?->format('d.m.Y') ?? '—' }}</div>
                        </div>
                        <div>
                            <label class="form-label fw-semibold">Создана</label>
                            <div class="info-badge">{{ $statement->created_at->format('d.m.Y H:i') }}</div>
                        </div>
                        @if($statement->submitted_at)
                        <div>
                            <label class="form-label fw-semibold">Отправлена заявителем</label>
                            <div class="info-badge">{{ $statement->submitted_at->format('d.m.Y H:i') }}</div>
                        </div>
                        @endif
                        @if($statement->editedBy)
                        <div>
                            <label class="form-label fw-semibold">Последнее изменение</label>
                            <div class="info-badge"><i class="bi bi-person-gear me-1 text-warning"></i>{{ $statement->editedBy->name }}</div>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- ════ ОТПРАВИТЬ РУКОВОДИТЕЛЮ — отдельная форма (POST → send-supervisor) ════ --}}
                @if($hrCanEdit)
                <div class="card border-0 shadow-sm mb-4" style="border:1px solid rgba(99,102,241,0.35) !important">
                    <div class="card-header py-3" style="background:rgba(99,102,241,0.08) !important">
                        <h6 class="mb-0 fw-semibold" style="color:#818cf8">
                            <i class="bi bi-send me-2"></i>Отправить руководителю
                        </h6>
                    </div>
                    <div class="card-body">
                        {{-- ВАЖНО: эта форма отдельная, не вложена в форму редактирования! --}}
                        <form method="POST" action="{{ route('hr.statements.send-supervisor', $statement) }}">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label fw-semibold">
                                    Руководитель <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('supervisor_id') is-invalid @enderror"
                                    name="supervisor_id" required>
                                    <option value="">— Выберите руководителя —</option>
                                    @foreach($supervisors as $sup)
                                        <option value="{{ $sup->id }}">{{ $sup->name }}</option>
                                    @endforeach
                                </select>
                                @error('supervisor_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                @if($supervisors->isEmpty())
                                    <div class="form-text text-warning mt-1">
                                        <i class="bi bi-exclamation-triangle me-1"></i>
                                        Нет пользователей с ролью super_admin
                                    </div>
                                @endif
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary"
                                    onclick="return confirm('Отправить заявку на согласование руководителю?')">
                                    <i class="bi bi-send me-2"></i>Отправить на согласование
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                @endif

                {{-- Статус: ожидает решения руководителя --}}
                @if($statement->isSupervisorReview())
                <div class="card border-0 shadow-sm mb-4" style="border:1px solid rgba(234,179,8,0.3) !important">
                    <div class="card-body text-center py-3">
                        <i class="bi bi-hourglass-split text-warning fs-3 mb-2 d-block"></i>
                        <div class="fw-semibold" style="color:#fbbf24">Ожидает решения руководителя</div>
                        @if($statement->supervisor)
                            <div class="text-muted small mt-1">{{ $statement->supervisor->name }}</div>
                        @endif
                        @if($statement->sent_to_supervisor_at)
                            <div class="text-muted small">{{ $statement->sent_to_supervisor_at->format('d.m.Y H:i') }}</div>
                        @endif
                    </div>
                </div>
                @endif

                {{-- Комментарий руководителя после решения --}}
                @if(in_array($statement->status, ['approved','rejected','on_hold']))
                <div class="card border-0 shadow-sm">
                    <div class="card-header py-3">
                        <h6 class="mb-0 fw-semibold"><i class="bi bi-chat-quote me-2 text-primary"></i>Решение руководителя</h6>
                    </div>
                    <div class="card-body">
                        <span class="badge bg-{{ \App\Models\VacancyRequest::STATUS_COLORS[$statement->status] ?? 'secondary' }} mb-2">
                            {{ \App\Models\VacancyRequest::STATUS_LABELS[$statement->status] ?? $statement->status }}
                        </span>
                        @if($statement->supervisor_comment)
                            <p class="mb-1 mt-2" style="white-space:pre-line;color:#f9fafb">{{ $statement->supervisor_comment }}</p>
                        @endif
                        @if($statement->supervisor_reviewed_at)
                            <div class="text-muted small">{{ $statement->supervisor_reviewed_at->format('d.m.Y H:i') }}</div>
                        @endif
                    </div>
                </div>
                @endif

            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    $(function () {
        let langIdx = {{ count($statement->languages ?? [[]]) }};
        $('#add_lang').on('click', function () {
            $('#languages_container').append(`
                <div class="lang-row d-flex align-items-center gap-2 mb-2">
                    <select class="form-select hr-editable" name="languages[${langIdx}][lang]" style="max-width:160px">
                        <option value="">— Язык —</option>
                        <option>Русский</option><option>Английский</option><option>Узбекский</option><option>Другой</option>
                    </select>
                    <select class="form-select hr-editable" name="languages[${langIdx}][level]" style="max-width:180px">
                        <option value="">— Уровень —</option>
                        <option>Начальный</option><option>Средний</option><option>Свободный</option>
                    </select>
                    <button type="button" class="btn btn-sm btn-outline-danger remove-lang"><i class="bi bi-x"></i></button>
                </div>`);
            langIdx++;
        });
        $(document).on('click', '.remove-lang', function () {
            if ($('#languages_container .lang-row').length > 1) $(this).closest('.lang-row').remove();
        });
    });
    </script>
    @endpush
</x-app-layout>