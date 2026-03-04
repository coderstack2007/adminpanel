{{-- resources/views/hr/subdivisions.blade.php --}}

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
    }
    .table > :not(caption) > * > * {
        background-color: transparent !important;
        border-bottom-color: rgba(255,255,255,0.07) !important;
    }
    code { background-color: rgb(17, 24, 39) !important; color: #a78bfa !important; }
    .badge-cat-A { background-color: #7c3aed; }
    .badge-cat-B { background-color: #1d4ed8; }
    .badge-cat-C { background-color: #065f46; }
    .badge-cat-D { background-color: #92400e; }
</style>
@endpush

<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h2 class="h5 fw-semibold mb-1">
                    <i class="bi bi-diagram-2 me-2"></i>Подразделения
                </h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0" style="font-size:0.8rem;">
                        <li class="breadcrumb-item">
                            <a href="{{ route('hr.departments.index') }}" style="color:#6366f1">
                                <i class="bi bi-diagram-3 me-1"></i>Отделы
                            </a>
                        </li>
                        <li class="breadcrumb-item" style="color:#9ca3af">
                            {{ $department->branch->name }}
                        </li>
                        <li class="breadcrumb-item active" style="color:#9ca3af">
                            {{ $department->name }}
                        </li>
                    </ol>
                </nav>
            </div>
            <a href="{{ route('hr.departments.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Назад
            </a>
        </div>
    </x-slot>

    <div class="container-fluid py-4">

        {{-- Инфо отдела --}}
        <div class="mb-4 d-flex align-items-center gap-2 flex-wrap">
            <i class="bi bi-diagram-3 text-primary fs-5"></i>
            <span class="fw-semibold">{{ $department->name }}</span>
            <code class="px-2 py-1 rounded">{{ $department->code }}</code>
            <span class="text-muted small">
                <i class="bi bi-building me-1"></i>{{ $department->branch->name }}
            </span>
            
        </div>

        @foreach($subdivisions as $subdivision)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header py-3 d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-diagram-2 me-2 text-primary"></i>{{ $subdivision->name }}
                    <code class="ms-2">{{ $subdivision->code }}</code>
                </h6>
              
                <span class="badge bg-primary rounded-pill">{{ $subdivision->positions_count }}</span>
            </div>
            <div class="card-body p-0">
                @if($subdivision->positions->isEmpty())
                    <div class="text-center py-3 text-muted">
                        <i class="bi bi-person-badge opacity-25 me-1"></i>Должностей нет
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
                                @foreach($subdivision->positions as $pos)
                                <tr>
                                    <td class="ps-3 text-muted small">{{ $loop->iteration }}</td>
                                    <td class="fw-semibold" style="color:#fff">{{ $pos->name }}</td>
                                    <td>
                                        @if($pos->users->isNotEmpty())
                                            @foreach($pos->users as $emp)
                                                <div class="small" style="color:#f9fafb">
                                                    <i class="bi bi-person-fill me-1 text-muted"></i>{{ $emp->name }}
                                                </div>
                                            @endforeach
                                        @else
                                            <span class="text-muted small">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-cat-{{ $pos->category }} text-white">
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
        @endforeach

    </div>
</x-app-layout>