<x-app-layout>

@push('styles')
<style>
    .card { background-color: rgb(31, 41, 65) !important; color: #f9fafb !important; }
    .card-header {
        background-color: rgb(31, 41, 55) !important;
        border-bottom-color: rgba(255,255,255,0.1) !important;
        color: #f9fafb !important;
    }
    .table { color: #f9fafb !important; }
    .table-light th {
        background-color: rgb(17, 24, 39) !important;
        color: #9ca3af !important;
        border-bottom-color: rgba(255,255,255,0.1) !important;
    }
    .table-hover tbody tr:hover {
        background-color: rgb(55, 65, 81) !important;
        cursor: pointer;
    }
    .table > :not(caption) > * > * {
        background-color: transparent !important;
        border-bottom-color: rgba(255,255,255,0.07) !important;
    }
    code { background-color: rgb(17, 24, 39) !important; color: #a78bfa !important; }
</style>
@endpush
    <x-slot name="header">
        <h2 class="h5 fw-semibold mb-0" style="color: var(--text-main)">Dashboard</h2>
    </x-slot>

    <div class="container-fluid py-4">
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="row g-3">

                    {{-- ── SUPER ADMIN ─────────────────────────────── --}}
                    @role('super_admin')
                    <div class="col-md-6">
                        <a href="{{ route('supervisor.branches.index') }}" class="card border-0 shadow-sm text-decoration-none h-100">
                            <div class="card-body text-center py-4">
                                <i class="bi bi-building fs-1 text-warning"></i>
                                <p class="mt-2 mb-0 fw-semibold">Филиалы</p>
                                <small class="text-muted">Управление филиалами</small>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-6">
                        <a href="{{ route('hr.statements.index') }}" class="card border-0 shadow-sm text-decoration-none h-100">
                            <div class="card-body text-center py-4">
                                <i class="bi bi-files fs-1 text-info"></i>
                                <p class="mt-2 mb-0 fw-semibold">Все заявки</p>
                                <small class="text-muted">Управление заявками</small>
                            </div>
                        </a>
                    </div>

                    {{-- Список заявок для super_admin --}}
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header fw-semibold d-flex align-items-center justify-content-between">
                                <span><i class="bi bi-list-ul me-2"></i>Последние заявки</span>
                                @php $adminStatements = \App\Models\VacancyRequest::with(['position','requester'])->latest()->take(5)->get(); @endphp
                                <span class="badge bg-primary rounded-pill">{{ \App\Models\VacancyRequest::count() }}</span>
                            </div>
                            <div class="card-body" style="min-height:200px;">
                                @if($adminStatements->isEmpty())
                                    <div class="d-flex flex-column align-items-center justify-content-center py-4">
                                        <i class="bi bi-inbox text-muted mb-2" style="font-size:2.5rem"></i>
                                        <p class="text-muted mb-0">Заявок нет</p>
                                    </div>
                                @else
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle mb-0">
                                            <tbody>
                                                @foreach($adminStatements as $s)
                                                <tr onclick="window.location='{{ route('supervisor.statements.show', $s) }}'" style="cursor:pointer">
                                                    <td style="color:#fff" class="fw-semibold">{{ $s->position?->name ?? '—' }}</td>
                                                    <td class="text-muted small">{{ $s->requester?->name ?? '—' }}</td>
                                                    <td><span class="badge bg-{{ $s->status_color }}">{{ $s->status_label }}</span></td>
                                                    <td class="text-muted small">{{ $s->created_at->format('d.m.Y') }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endrole

                    
                   {{-- ── HR MANAGER ───────────────────────────────── --}}
@role('hr_manager')
<div class="col-md-6">
    <a href="{{ route('hr.departments.index') }}" class="card border-0 shadow-sm text-decoration-none h-100">
        <div class="card-body text-center py-4">
            <i class="bi bi-people-fill fs-1 text-primary"></i>
            <p class="mt-2 mb-0 fw-semibold">HR Панель</p>
            <small class="text-muted">Все отделы и подразделения</small>
        </div>
    </a>
</div>
<div class="col-md-6">
    <a href="{{ route('hr.statements.index') }}" class="card border-0 shadow-sm text-decoration-none h-100">
        <div class="card-body text-center py-4">
            <i class="bi bi-files fs-1 text-info"></i>
            <p class="mt-2 mb-0 fw-semibold">Заявки HR</p>
            <small class="text-muted">Все поступившие заявки</small>
        </div>
    </a>
</div>

{{-- ── Поступившие заявки ────────────────────────────── --}}
<div class="col-12">
    <div class="card border-0 shadow-sm">
        <div class="card-header fw-semibold d-flex align-items-center justify-content-between">
            <span><i class="bi bi-list-ul me-2"></i>Поступившие заявки</span>
            @php
                $hrStatements = \App\Models\VacancyRequest::with(['position','requester'])
                    ->whereIn('status', ['approved','hr_reviewed'])
                    ->latest()->take(5)->get();
            @endphp
            <span class="badge bg-primary rounded-pill">{{ $hrStatements->count() }}</span>
        </div>
        <div class="card-body" style="min-height:200px;">
            @if($hrStatements->isEmpty())
                <div class="d-flex flex-column align-items-center justify-content-center py-4">
                    <i class="bi bi-inbox text-muted mb-2" style="font-size:2.5rem"></i>
                    <p class="text-muted mb-0">Новых заявок нет</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <tbody>
                            @foreach($hrStatements as $s)
                            <tr>
                                <td onclick="window.location='{{ route('hr.statements.show', $s) }}'" 
                                    style="cursor:pointer; color:#fff" class="fw-semibold">
                                    {{ $s->position?->name ?? '—' }}
                                </td>
                                <td onclick="window.location='{{ route('hr.statements.show', $s) }}'" 
                                    style="cursor:pointer" class="text-muted small">
                                    {{ $s->requester?->name ?? '—' }}
                                </td>
                                <td onclick="window.location='{{ route('hr.statements.show', $s) }}'" 
                                    style="cursor:pointer">
                                    <span class="badge bg-{{ $s->status_color }}">{{ $s->status_label }}</span>
                                </td>
                                <td onclick="window.location='{{ route('hr.statements.show', $s) }}'" 
                                    style="cursor:pointer" class="text-muted small">
                                    {{ $s->created_at->format('d.m.Y') }}
                                </td>
                                <td class="text-end">
                                    <form action="{{ route('hr.statements.delete', $s) }}" 
                                          method="POST" 
                                          onsubmit="return confirm('Удалить вакансию и все связанные резюме?')"
                                          class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="btn btn-sm btn-outline-danger" 
                                                title="Удалить вакансию">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- ── Резюме из Telegram-бота ────────────────────────── --}}
<div class="col-12">
    <div class="card border-0 shadow-sm">
        <div class="card-header fw-semibold d-flex align-items-center justify-content-between">
            <span>
                <i class="bi bi-person-lines-fill me-2 text-success"></i>Резюме из Telegram-бота
            </span>
            @php
                try {
                    $botResumes = \Illuminate\Support\Facades\DB::connection('resume_bot')
                        ->table('resumes as r')
                        ->join('regions as reg', 'r.region_id', '=', 'reg.id')
                        ->join('cities as c', 'r.city_id', '=', 'c.id')
                        ->select(
                            'r.id',
                            'r.name',
                            'r.age',
                            'r.phone',
                            'r.vacancy_id',
                            'r.language',
                            'r.created_at',
                            'reg.name_ru as region_name',
                            'c.name_ru as city_name'
                        )
                        ->orderByDesc('r.created_at')
                        ->take(10)
                        ->get();

                    $vacancyIds = $botResumes->pluck('vacancy_id')->filter()->unique()->values();
                    $vacancies  = \App\Models\VacancyRequest::with('position')
                        ->whereIn('id', $vacancyIds)
                        ->get()
                        ->keyBy('id');

                    $totalResumes = \Illuminate\Support\Facades\DB::connection('resume_bot')
                        ->table('resumes')
                        ->count();
                } catch (\Exception $e) {
                    $botResumes   = collect();
                    $vacancies    = collect();
                    $totalResumes = 0;
                }
            @endphp
            <span class="badge bg-success rounded-pill">{{ $totalResumes }}</span>
        </div>
        <div class="card-body" style="min-height:200px;">
            @if($botResumes->isEmpty())
                <div class="d-flex flex-column align-items-center justify-content-center py-4">
                    <i class="bi bi-inbox text-muted mb-2" style="font-size:2.5rem"></i>
                    <p class="text-muted mb-0">Резюме ещё не поступали</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr style="background:rgb(17,24,39)">
                                <th style="color:#9ca3af;font-size:0.78rem;font-weight:600;border:none">Имя</th>
                                <th style="color:#9ca3af;font-size:0.78rem;font-weight:600;border:none">Возраст</th>
                                <th style="color:#9ca3af;font-size:0.78rem;font-weight:600;border:none">Телефон</th>
                                <th style="color:#9ca3af;font-size:0.78rem;font-weight:600;border:none">Город</th>
                                <th style="color:#9ca3af;font-size:0.78rem;font-weight:600;border:none">Вакансия</th>
                                <th style="color:#9ca3af;font-size:0.78rem;font-weight:600;border:none">Язык</th>
                                <th style="color:#9ca3af;font-size:0.78rem;font-weight:600;border:none">Дата</th>
                                <th style="color:#9ca3af;font-size:0.78rem;font-weight:600;border:none"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($botResumes as $r)
                            @php
                                $vacancy = $r->vacancy_id ? ($vacancies[$r->vacancy_id] ?? null) : null;
                            @endphp
                            <tr>
                                <td class="fw-semibold" style="color:#fff">{{ $r->name }}</td>
                                <td class="text-muted small">{{ $r->age }}</td>
                                <td class="text-muted small">
                                    <a href="tel:{{ $r->phone }}" style="color:#6366f1">+{{ $r->phone }}</a>
                                </td>
                                <td class="text-muted small">
                                    {{ $r->city_name }}, {{ $r->region_name }}
                                </td>
                                <td>
                                    @if($vacancy)
                                        <span class="badge bg-success bg-opacity-20">
                                            {{ $vacancy->position?->name ?? '—' }}
                                        </span>
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-secondary">
                                        {{ $r->language === 'ru' ? '🇷🇺 RU' : '🇺🇿 UZ' }}
                                    </span>
                                </td>
                                <td class="text-muted small">
                                    {{ \Carbon\Carbon::parse($r->created_at)->format('d.m.Y H:i') }}
                                </td>
                                <td class="text-end">
                                    <form action="{{ route('hr.resumes.delete', $r->id) }}" 
                                          method="POST" 
                                          onsubmit="return confirm('Удалить это резюме?')"
                                          class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="btn btn-sm btn-outline-danger" 
                                                title="Удалить резюме">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>

@endrole

                    {{-- ── DEPARTMENT HEAD ──────────────────────────── --}}
                    @role('department_head')
                    <div class="col-md-6">
                        @if(auth()->user()->subdivision_id)
                            <a href="{{ route('employee.subdivision.index') }}" class="card border-0 shadow-sm text-decoration-none h-100">
                                <div class="card-body text-center py-4">
                                    <i class="bi bi-diagram-2 fs-1 text-warning"></i>
                                    <p class="mt-2 mb-0 fw-semibold">Моё подразделение</p>
                                    <small class="text-muted">Управление сотрудниками</small>
                                </div>
                            </a>
                        @else
                            <div class="card border-0 shadow-sm h-100 opacity-50">
                                <div class="card-body text-center py-4">
                                    <i class="bi bi-diagram-2 fs-1 text-muted"></i>
                                    <p class="mt-2 mb-0 fw-semibold text-muted">Подразделение не назначено</p>
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <a href="{{ route('department_head.statements.index') }}" class="card border-0 shadow-sm text-decoration-none h-100">
                            <div class="card-body text-center py-4">
                                <i class="bi bi-file-earmark-plus fs-1 text-success"></i>
                                <p class="mt-2 mb-0 fw-semibold">Мои заявки</p>
                                <small class="text-muted">Создать или просмотреть</small>
                            </div>
                        </a>
                    </div>

                    {{-- Список заявок для department_head --}}
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header fw-semibold d-flex align-items-center justify-content-between">
                                <span><i class="bi bi-list-ul me-2"></i>Мои заявки</span>
                                @php
                                    $myStatements = \App\Models\VacancyRequest::with(['position'])
                                        ->where('requester_id', auth()->id())
                                        ->latest()->take(5)->get();
                                @endphp
                                <a href="{{ route('department_head.statements.index') }}"
                                   class="badge bg-primary rounded-pill text-decoration-none">
                                    {{ \App\Models\VacancyRequest::where('requester_id', auth()->id())->count() }}
                                </a>
                            </div>
                            <div class="card-body" style="min-height:200px;">
                                @if($myStatements->isEmpty())
                                    <div class="d-flex flex-column align-items-center justify-content-center py-4">
                                        <i class="bi bi-inbox text-muted mb-2" style="font-size:2.5rem"></i>
                                        <p class="text-muted mb-0">Заявок нет</p>
                                        <small class="text-muted">Создайте первую заявку</small>
                                    </div>
                                @else
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle mb-0">
                                            <tbody>
                                                @foreach($myStatements as $s)
                                                <tr onclick="window.location='{{ route('department_head.statements.show', $s) }}'" style="cursor:pointer">
                                                    <td style="color:#fff" class="fw-semibold">{{ $s->position?->name ?? '—' }}</td>
                                                    <td><span class="badge bg-{{ $s->status_color }}">{{ $s->status_label }}</span></td>
                                                    <td class="text-muted small">{{ $s->created_at->format('d.m.Y') }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endrole

                    {{-- ── EMPLOYEE ─────────────────────────────────── --}}
                    @role('employee')
                    <div class="col-md-6">
                        <a href="{{ route('employee.subdivision.index') }}" class="card border-0 shadow-sm text-decoration-none h-100">
                            <div class="card-body text-center py-4">
                                <i class="bi bi-diagram-2 fs-1 text-info"></i>
                                <p class="mt-2 mb-0 fw-semibold">Моё подразделение</p>
                                <small class="text-muted">Просмотр должностей</small>
                            </div>
                        </a>
                    </div>
                    @endrole

                </div>
            </div>

            {{-- ── ПРАВАЯ ЧАСТЬ: личные данные ─────────────────── --}}
            <div class="col-lg-4">
                <div class="card border-0 h-100">
                    <div class="card-header fw-semibold">
                        <i class="bi bi-person-circle me-2"></i>Личные данные
                    </div>
                    <div class="card-body d-flex flex-column gap-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle p-3 flex-shrink-0 d-flex align-items-center justify-content-center"
                                style="background: rgba(96,165,250,0.15); width: 56px; height: 56px;">
                                <i class="bi bi-person-fill fs-4 text-primary"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-semibold">{{ auth()->user()->name }}</h6>
                                <small class="text-muted">{{ auth()->user()->email }}</small>
                            </div>
                        </div>
                        <hr class="my-1">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted small">Роль</span>
                            <span class="badge bg-secondary">{{ auth()->user()->display_role }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted small">Код сотрудника</span>
                            <span class="fw-semibold small">{{ auth()->user()->employee_code ?? '—' }}</span>
                        </div>
                        @if(auth()->user()->position)
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted small">Должность</span>
                            <span class="fw-semibold small text-end" style="max-width:60%">
                                {{ auth()->user()->position->name }}
                            </span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted small">Категория / Разряд</span>
                            <span class="badge bg-info text-dark">
                                Кат. {{ auth()->user()->position_category }},
                                {{ auth()->user()->position_grade }}-й разряд
                            </span>
                        </div>
                        @endif
                        @if(auth()->user()->branch)
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted small">Филиал</span>
                            <span class="fw-semibold small">{{ auth()->user()->branch->name }}</span>
                        </div>
                        @endif
                        @if(auth()->user()->department)
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted small">Отдел</span>
                            <span class="fw-semibold small">{{ auth()->user()->department->name }}</span>
                        </div>
                        @endif
                        @if(auth()->user()->subdivision)
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted small">Подразделение</span>
                            <span class="fw-semibold small">{{ auth()->user()->subdivision->name }}</span>
                        </div>
                        @endif
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted small">Последний вход</span>
                            <span class="small text-muted">
                                {{ auth()->user()->last_login_at?->format('d.m.Y H:i') ?? '—' }}
                            </span>
                        </div>
                        <hr class="my-1">
                        <a href="#" class="btn btn-outline-primary btn-sm w-100">
                            <i class="bi bi-pencil-square me-1"></i>Редактировать профиль
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>