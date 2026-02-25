{{-- resources/views/department_head/statements.blade.php --}}

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
</style>
@endpush

<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center justify-content-between">
            <h2 class="h5 fw-semibold mb-0">
                <i class="bi bi-file-earmark-text me-2"></i>Мои заявки
            </h2>
            <a href="{{ route('department_head.statements.create') }}"
               class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg me-1"></i>Новая заявка
            </a>
        </div>
    </x-slot>

    <div class="container-fluid py-4">

        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <div class="card border-0 shadow-sm">
            <div class="card-header py-3 d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-list-ul me-2 text-primary"></i>Список заявок
                </h6>
                <span class="badge bg-primary rounded-pill">{{ $statements->count() }}</span>
            </div>
            <div class="card-body p-0">
                @if($statements->isEmpty())
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-file-earmark-x fs-1 opacity-25 d-block mb-3"></i>
                        <p class="fs-5 mb-1">Заявок нет</p>
                        <small>Создайте первую заявку на подбор персонала</small>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">#</th>
                                    <th>Должность</th>
                                    <th>Подразделение</th>
                                    <th>Статус</th>
                                    <th>Дата</th>
                                    <th class="text-end pe-3">Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($statements as $s)
                                <tr onclick="window.location='{{ route('department_head.statements.show', $s) }}'">
                                    <td class="ps-3 text-muted small">{{ $loop->iteration }}</td>
                                    <td class="fw-semibold" style="color:#fff">
                                        {{ $s->position?->name ?? '—' }}
                                    </td>
                                    <td class="text-muted small">
                                        {{ $s->subdivision?->name ?? '—' }}
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $s->status_color }}">
                                            {{ $s->status_label }}
                                        </span>
                                    </td>
                                    <td class="text-muted small">
                                        {{ $s->created_at->format('d.m.Y') }}
                                    </td>
                                    <td class="text-end pe-3" onclick="event.stopPropagation()">
                                        <a href="{{ route('department_head.statements.show', $s) }}"
                                           class="btn btn-sm btn-outline-secondary">
                                            <i class="bi bi-eye"></i>
                                        </a>
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
</x-app-layout>