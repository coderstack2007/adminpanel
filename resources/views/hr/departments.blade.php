{{-- resources/views/hr/departments.blade.php --}}

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

<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center justify-content-between">
            <h2 class="h5 fw-semibold mb-0">
                <i class="bi bi-diagram-3 me-2"></i>Отделы
            </h2>
            <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Назад
            </a>
        </div>
    </x-slot>

    <div class="container-fluid py-4">

        @foreach($branches as $branch)
        <div class="mb-4">
            <div class="d-flex align-items-center gap-2 mb-3">
                <i class="bi bi-building text-warning fs-5"></i>
                <span class="fw-semibold fs-6">{{ $branch->name }}</span>
                <code class="px-2 py-1 rounded">{{ $branch->code }}</code>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header py-3 d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-semibold">
                        <i class="bi bi-list-ul me-2 text-primary"></i>Отделы филиала
                    </h6>
                    <span class="badge bg-primary rounded-pill">{{ $branch->departments->count() }}</span>
                </div>
                <div class="card-body p-0">
                    @if($branch->departments->isEmpty())
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-diagram-3 fs-1 opacity-25 d-block mb-2"></i>
                            Отделов нет
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-3">#</th>
                                        <th>Название</th>
                                        <th>Код</th>
                                        <th>Статус</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($branch->departments as $dept)
                                    <tr
                                        style="cursor:pointer"
                                        onclick="window.location='{{ route('hr.departments.subdivisions.index', $dept) }}'"
                                    >
                                        <td class="ps-3 text-muted small">{{ $loop->iteration }}</td>
                                        <td class="fw-semibold" style="color:#fff">{{ $dept->name }}</td>
                                        <td><code>{{ $dept->code }}</code></td>
                                        <td>
                                            @if($dept->is_active)
                                                <span class="badge bg-success-subtle text-success border border-success-subtle">Активен</span>
                                            @else
                                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Неактивен</span>
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
        @endforeach

    </div>
</x-app-layout>