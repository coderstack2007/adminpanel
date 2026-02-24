<x-app-layout>
    @php
        $grade = auth()->user()->position_grade;
        $canSeeRequests = auth()->user()->hasAnyRole(['super_admin', 'hr_manager', 'department_head'])
            || ($grade !== null && $grade >= 3);
    @endphp

    <x-slot name="header">
        <h2 class="h5 fw-semibold mb-0" style="color: var(--text-main)">Dashboard</h2>
    </x-slot>

    <div class="container-fluid py-4">
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="row g-3">

                    {{-- Super Admin: Филиалы --}}
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
                    @endrole

                    {{-- HR Manager: Панель --}}
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
                    @endrole

                    {{-- Department Head: только своё подразделение --}}
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
                    @endrole

                    {{-- Мои заявки --}}
                    @if($canSeeRequests)
                    <div class="col-md-6">
                        <a href="#" class="card border-0 shadow-sm text-decoration-none h-100">
                            <div class="card-body text-center py-4">
                                <i class="bi bi-file-earmark-plus fs-1 text-success"></i>
                                <p class="mt-2 mb-0 fw-semibold">Мои заявки</p>
                                <small class="text-muted">Создать или просмотреть</small>
                            </div>
                        </a>
                    </div>
                    @endif

                    {{-- Employee: моё подразделение --}}
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

                    {{-- Список заявок --}}
                    @if($canSeeRequests)
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header fw-semibold">
                                <i class="bi bi-list-ul me-2"></i>Мои заявки
                            </div>
                            <div class="card-body" style="min-height: 300px;">
                                <div class="d-flex flex-column align-items-center justify-content-center h-100 py-5" style="min-height: 250px;">
                                    <i class="bi bi-inbox text-muted mb-3" style="font-size: 3.5rem;"></i>
                                    <p class="text-muted mb-0 fs-5">Заявок нет</p>
                                    <small class="text-muted">Ваши заявки появятся здесь</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                </div>
            </div>

            {{-- Правая часть: личные данные --}}
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