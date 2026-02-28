{{-- resources/views/supervisor/statements.blade.php --}}

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
    .status-badge {
        padding: 0.35rem 0.75rem;
        border-radius: 999px;
        font-size: 0.75rem;
        font-weight: 600;
    }
</style>
@endpush

<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>
                <h2 class="h5 fw-semibold mb-1">
                    <i class="bi bi-file-earmark-check me-2"></i>Заявки на согласование
                </h2>
                <p class="text-muted small mb-0">
                    Заявки, требующие вашего решения
                </p>
            </div>
            <span class="badge bg-primary rounded-pill" style="font-size:0.9rem; padding:0.5rem 1rem">
                {{ $statements->count() }} заявок
            </span>
        </div>
    </x-slot>

    <div class="container-fluid py-4">

        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <div class="card border-0 shadow-sm">
            <div class="card-header py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-list-ul me-2"></i>Все заявки
                </h6>
            </div>
            <div class="card-body p-0">
                @if($statements->isEmpty())
                    <div class="text-center py-5">
                        <i class="bi bi-inbox text-muted mb-3" style="font-size:3rem"></i>
                        <p class="text-muted mb-0">Нет заявок на согласование</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:60px">#</th>
                                    <th>Должность</th>
                                    <th>Заявитель</th>
                                    <th>Подразделение</th>
                                    <th>Статус</th>
                                    <th>Отправлено</th>
                                    <th style="width:100px"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($statements as $s)
                                <tr onclick="window.location='{{ route('supervisor.statements.show', $s) }}'" 
                                    style="cursor:pointer">
                                    <td class="text-muted small">{{ $s->id }}</td>
                                    <td class="fw-semibold" style="color:#fff">
                                        {{ $s->position?->name ?? '—' }}
                                    </td>
                                    <td class="text-muted small">
                                        {{ $s->requester?->name ?? '—' }}
                                    </td>
                                    <td class="text-muted small">
                                        {{ $s->subdivision?->name ?? '—' }}
                                    </td>
                                    <td>
                                        <span class="status-badge bg-{{ $s->status_color }} bg-opacity-20 "  style="color: #fff;" >
                                            {{ $s->status_label }}
                                        </span>
                                    </td>
                                    <td class="text-muted small">
                                        {{ $s->sent_to_supervisor_at?->format('d.m.Y H:i') ?? '—' }}
                                    </td>
                                    <td>
                                        @if($s->isSupervisorReview())
                                            <span class="badge bg-warning text-dark">
                                                <i class="bi bi-exclamation-circle me-1"></i>Требует решения
                                            </span>
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

        {{-- Фильтры по статусам (опционально) --}}
        <div class="row g-3 mt-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm text-center">
                    <div class="card-body">
                        <h3 class="mb-1 fw-bold text-warning">
                            {{ $statements->where('status', 'supervisor_review')->count() }}
                        </h3>
                        <p class="text-muted small mb-0">На рассмотрении</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm text-center">
                    <div class="card-body">
                        <h3 class="mb-1 fw-bold text-success">
                            {{ $statements->where('status', 'approved')->count() }}
                        </h3>
                        <p class="text-muted small mb-0">Одобрено</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm text-center">
                    <div class="card-body">
                        <h3 class="mb-1 fw-bold text-danger">
                            {{ $statements->where('status', 'rejected')->count() }}
                        </h3>
                        <p class="text-muted small mb-0">Отклонено</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm text-center">
                    <div class="card-body">
                        <h3 class="mb-1 fw-bold text-info">
                            {{ $statements->where('status', 'on_hold')->count() }}
                        </h3>
                        <p class="text-muted small mb-0">Приостановлено</p>
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>