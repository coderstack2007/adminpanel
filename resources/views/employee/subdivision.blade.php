{{-- resources/views/employee/subdivision.blade.php --}}

@push('styles')
<style>
    .card {
        background-color: rgb(31, 41, 65) !important;
        color: #f9fafb !important;
    }
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
        color: #f9fafb !important;
    }
    .table > :not(caption) > * > * {
        background-color: transparent !important;
        border-bottom-color: rgba(255,255,255,0.07) !important;
    }
    .info-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 4px 0;
    }
    .info-row span.label { color: #9ca3af; font-size: 0.82rem; }
    .info-row span.value { font-weight: 600; font-size: 0.85rem; color: #f9fafb; }
    code {
        background-color: rgb(17, 24, 39) !important;
        color: #a78bfa !important;
    }
    hr { border-color: rgba(255,255,255,0.1) !important; }
</style>
@endpush

<x-app-layout>
    <x-slot name="header">
        <h2 class="h5 fw-semibold mb-0">
            <i class="bi bi-diagram-2 me-2"></i>Моё подразделение
        </h2>
    </x-slot>
<x-slot name="header">
    <div class="d-flex align-items-center justify-content-between">
        <h2 class="h5 fw-semibold mb-0">
            <i class="bi bi-diagram-2 me-2"></i>Моё подразделение
        </h2>
        <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Назад
        </a>
    </div>
</x-slot>
    <div class="container-fluid py-4">

        @if($subdivision)
        <div class="row g-4">

            {{-- Карточка: место в структуре --}}
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header py-3">
                        <h6 class="mb-0 fw-semibold">
                            <i class="bi bi-person-circle me-2 text-primary"></i>Ваше место в структуре
                        </h6>
                    </div>
                    <div class="card-body d-flex flex-column gap-2">

                        @if($user->branch)
                        <div class="info-row">
                            <span class="label"><i class="bi bi-building me-1"></i>Филиал</span>
                            <span class="value">{{ $user->branch->name }}</span>
                        </div>
                        @endif

                        @if($user->department)
                        <div class="info-row">
                            <span class="label"><i class="bi bi-diagram-3 me-1"></i>Отдел</span>
                            <span class="value">{{ $user->department->name }}</span>
                        </div>
                        @endif

                        <div class="info-row">
                            <span class="label"><i class="bi bi-diagram-2 me-1"></i>Подразделение</span>
                            <span class="value">{{ $subdivision->name }}</span>
                        </div>
                        {{-- Ответственный за подразделение --}}
                            @if($subdivision->head)
                            <div class="info-row">
                                <span class="label"><i class="bi bi-person-check me-1"></i>Ответственный</span>
                                <span class="value" style="color:#818cf8">{{ $subdivision->head->name }}</span>
                            </div>
                            @endif
                        <div class="info-row">
                            <span class="label">Код</span>
                            <code>{{ $subdivision->code }}</code>
                        </div>

                        <hr>

                        @if($user->position)
                        <div class="info-row">
                            <span class="label">Ваша должность</span>
                            <span class="value text-end" style="max-width:60%">{{ $user->position->name }}</span>
                        </div>
                        <div class="info-row">
                            <span class="label">Категория / Разряд</span>
                            <span class="badge bg-info text-dark">
                                Кат. {{ $user->position->category }},
                                {{ $user->position->grade }}-й разряд
                            </span>
                        </div>
                        @endif

                    </div>
                </div>
            </div>

            {{-- Карточка: должности с именами --}}
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header py-3 d-flex align-items-center justify-content-between">
                        <h6 class="mb-0 fw-semibold">
                            <i class="bi bi-person-badge me-2 text-primary"></i>Должности подразделения
                        </h6>
                        <span class="badge bg-primary rounded-pill">{{ $positions->count() }}</span>
                    </div>
                    <div class="card-body p-0">
                        @if($positions->isEmpty())
                            <div class="text-center py-4 text-muted">
                                <i class="bi bi-person-badge fs-1 opacity-25 d-block mb-2"></i>
                                Должности не найдены
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="ps-3">#</th>
                                            <th>Должность</th>
                                            <th>Сотрудник</th>
                                            <th>Категория</th>
                                            <th>Разряд</th>
                                            <th>Статус</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($positions as $pos)
                                        <tr @if($user->position_id === $pos->id) style="background:rgba(99,102,241,0.1)" @endif>
                                            <td class="ps-3 text-muted small">{{ $loop->iteration }}</td>
                                            <td class="fw-semibold" style="color: #fff;">
                                                {{ $pos->name }}
                                                @if($user->position_id === $pos->id)
                                                    <span class="badge bg-primary ms-1" style="font-size:0.65rem;">Ваша</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($pos->users->isNotEmpty())
                                                    @foreach($pos->users as $emp)
                                                        <div class="small" style="color: #fff;">
                                                            <i class="bi bi-person-fill me-1 text-muted " ></i> {{ $emp->name }}
                                                        </div>
                                                    @endforeach
                                                @else
                                                    <span class="text-muted small">—</span>
                                                @endif
                                            </td>
                                            <td>
                                                @php $colors = ['A'=>'#7c3aed','B'=>'#1d4ed8','C'=>'#065f46','D'=>'#92400e']; @endphp
                                                <span class="badge text-white" style="background:{{ $colors[$pos->category] ?? '#6b7280' }}">
                                                    Кат. {{ $pos->category }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary">{{ $pos->grade }}-й</span>
                                            </td>
                                            <td>
                                                @if($pos->is_vacant)
                                                    <span class="badge bg-warning text-dark">
                                                        <i class="bi bi-door-open me-1"></i>Вакантна
                                                    </span>
                                                @else
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle">Занята</span>
                                                @endif
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

        </div>
        @else
        <div class="text-center py-5 text-muted">
            <i class="bi bi-diagram-2 opacity-25 d-block mb-3" style="font-size:4rem;"></i>
            <p class="fs-5 mb-1">Вы не привязаны к подразделению</p>
            <small>Обратитесь к администратору системы</small>
        </div>
        @endif

    </div>
</x-app-layout>