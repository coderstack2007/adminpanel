{{-- resources/views/supervisor/departments.blade.php --}}

@push('styles')
<style>
    .card {
        background-color: rgb(31, 41, 55) !important;
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
        cursor: pointer;
    }
    .table > :not(caption) > * > * {
        background-color: transparent !important;
        border-bottom-color: rgba(255,255,255,0.07) !important;
    }
    .form-control {
        background-color: rgb(17, 24, 39) !important;
        border-color: rgba(255,255,255,0.15) !important;
        color: #f9fafb !important;
    }
    .form-control::placeholder { color: #6b7280 !important; }
    .form-control:focus {
        background-color: rgb(17, 24, 39) !important;
        border-color: #6366f1 !important;
        color: #f9fafb !important;
        box-shadow: 0 0 0 0.25rem rgba(99,102,241,0.25) !important;
    }
    .form-label { color: #d1d5db !important; }
    .form-text  { color: #6b7280 !important; }
    code {
        background-color: rgb(17, 24, 39) !important;
        color: #a78bfa !important;
    }
    .badge.bg-secondary { background-color: rgb(55, 65, 81) !important; }
    .breadcrumb-item a  { color: #6366f1; text-decoration: none; }
    .breadcrumb-item a:hover { color: #818cf8; }
    .breadcrumb-item.active { color: #9ca3af; }
    .breadcrumb-item + .breadcrumb-item::before { color: #6b7280; }
</style>
@endpush

<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h2 class="h5 fw-semibold mb-1">
                    <i class="bi bi-diagram-3 me-2"></i>Отделы
                </h2>
                {{-- Breadcrumb --}}
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0" style="font-size: 0.8rem;">
                        <li class="breadcrumb-item">
                            <a href="{{ route('supervisor.branches.index') }}">
                                <i class="bi bi-building me-1"></i>Филиалы
                            </a>
                        </li>
                        <li class="breadcrumb-item active">{{ $branch->name }}</li>
                    </ol>
                </nav>
            </div>
            <a href="{{ route('supervisor.branches.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Назад
            </a>
        </div>
    </x-slot>

    <div class="container-fluid py-4">

        {{-- Уведомления --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Инфо-бейдж филиала --}}
        <div class="mb-4 d-flex align-items-center gap-2">
            <i class="bi bi-building text-primary fs-5"></i>
            <span class="fw-semibold fs-6">{{ $branch->name }}</span>
            <code class="px-2 py-1 rounded" style="background:rgb(17,24,39);color:#a78bfa;">
                {{ $branch->code }}
            </code>
            @if($branch->address)
                <span class="text-muted small">
                    <i class="bi bi-geo-alt me-1"></i>{{ $branch->address }}
                </span>
            @endif
        </div>

        <div class="row g-4">

            {{-- ЛЕВАЯ КОЛОНКА — Форма --}}
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header py-3">
                        <h6 class="mb-0 fw-semibold">
                            <i class="bi bi-plus-circle me-2 text-primary"></i>Добавить отдел
                        </h6>
                    </div>
                    <div class="card-body">
                        <form
                            method="POST"
                            action="{{ route('supervisor.branches.departments.store', $branch) }}"
                            id="deptForm"
                        >
                            @csrf

                            <div class="mb-3">
                                <label for="name" class="form-label fw-semibold">
                                    Название <span class="text-danger">*</span>
                                </label>
                                <input
                                    type="text"
                                    class="form-control @error('name') is-invalid @enderror"
                                    id="name"
                                    name="name"
                                    value="{{ old('name') }}"
                                    placeholder="Бухгалтерия"
                                    required
                                >
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label for="code" class="form-label fw-semibold">
                                    Код <span class="text-danger">*</span>
                                </label>
                                <input
                                    type="text"
                                    class="form-control @error('code') is-invalid @enderror"
                                    id="code"
                                    name="code"
                                    value="{{ old('code') }}"
                                    placeholder="BUCH"
                                    required
                                >
                                @error('code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Уникальный код отдела</div>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-plus-lg me-1"></i>Создать отдел
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- ПРАВАЯ КОЛОНКА — Список --}}
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header py-3 d-flex align-items-center justify-content-between">
                        <h6 class="mb-0 fw-semibold">
                            <i class="bi bi-list-ul me-2 text-primary"></i>Список отделов
                        </h6>
                        <span class="badge bg-primary rounded-pill">{{ $departments->count() }}</span>
                    </div>
                    <div class="card-body p-0">
                        @if($departments->isEmpty())
                            <div class="text-center py-5 text-muted">
                                <i class="bi bi-diagram-3 fs-1 d-block mb-2 opacity-25"></i>
                                Отделы ещё не добавлены
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="ps-3">#</th>
                                            <th>Название</th>
                                            <th>Код</th>
                                            <th>Подразделений</th>
                                            <th>Статус</th>
                                            <th class="text-end pe-3">Действия</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($departments as $dept)
                                        <tr
                                            style="cursor: pointer;"
                                            onclick="window.location='{{ route('supervisor.departments.subdivisions.index', $dept) }}'"
                                        >
                                            <td class="ps-3 text-muted small">{{ $loop->iteration }}</td>
                                            <td onclick="window.location='#'">
                                                <div class="fw-semibold" style="color:#fff">{{ $dept->name }}</div>
                                            </td>
                                            <td onclick="window.location='#'">
                                                <code class="px-2 py-1 rounded">{{ $dept->code }}</code>
                                            </td>
                                            <td onclick="window.location='#'">
                                                <span class="badge bg-secondary rounded-pill">
                                                    {{ $dept->subdivisions_count }}
                                                </span>
                                            </td>
                                            <td onclick="window.location='#'">
                                                @if($dept->is_active)
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle">
                                                        Активен
                                                    </span>
                                                @else
                                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                                        Неактивен
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="text-end pe-3">
                                                <form
                                                    method="POST"
                                                    action="{{ route('supervisor.branches.departments.destroy', [$branch, $dept]) }}"
                                                    class="d-inline delete-form"
                                                >
                                                    @csrf
                                                    @method('DELETE')
                                                    <button
                                                        type="submit"
                                                        class="btn btn-sm btn-outline-danger"
                                                        title="Удалить"
                                                    >
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

        </div>
    </div>

    @push('scripts')
    <script>
    $(function () {
        $('.delete-form').on('submit', function (e) {
            e.preventDefault();
            const form = this;
            if (confirm('Удалить этот отдел?')) {
                form.submit();
            }
        });

        $('#code').on('input', function () {
            $(this).val($(this).val().toUpperCase());
        });
    });
    </script>
    @endpush

</x-app-layout>