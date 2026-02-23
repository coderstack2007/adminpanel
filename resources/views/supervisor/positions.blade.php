{{-- resources/views/supervisor/positions.blade.php --}}

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
    }
    .table > :not(caption) > * > * {
        background-color: transparent !important;
        border-bottom-color: rgba(255,255,255,0.07) !important;
    }
    .form-control, .form-select {
        background-color: rgb(17, 24, 39) !important;
        border-color: rgba(255,255,255,0.15) !important;
        color: #f9fafb !important;
    }
    .form-control::placeholder { color: #6b7280 !important; }
    .form-control:focus, .form-select:focus {
        background-color: rgb(17, 24, 39) !important;
        border-color: #6366f1 !important;
        color: #f9fafb !important;
        box-shadow: 0 0 0 0.25rem rgba(99,102,241,0.25) !important;
    }
    .form-select option {
        background-color: rgb(17, 24, 39);
        color: #f9fafb;
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
    /* Бейджи категорий */
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
                    <i class="bi bi-person-badge me-2"></i>Должности
                </h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0" style="font-size:0.8rem;">
                        <li class="breadcrumb-item">
                            <a href="{{ route('supervisor.branches.index') }}">
                                <i class="bi bi-building me-1"></i>Филиалы
                            </a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('supervisor.branches.departments.index', $subdivision->department->branch_id) }}">
                                {{ $subdivision->department->branch->name }}
                            </a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('supervisor.departments.subdivisions.index', $subdivision->department_id) }}">
                                {{ $subdivision->department->name }}
                            </a>
                        </li>
                        <li class="breadcrumb-item active">{{ $subdivision->name }}</li>
                    </ol>
                </nav>
            </div>
            
               <a href="{{ route('supervisor.departments.subdivisions.index', $subdivision->department_id) }}"
                    class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Назад
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
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Инфо-бейдж подразделения --}}
        <div class="mb-4 d-flex align-items-center gap-2 flex-wrap">
            <i class="bi bi-diagram-2 text-primary fs-5"></i>
            <span class="fw-semibold fs-6">{{ $subdivision->name }}</span>
            <code class="px-2 py-1 rounded">{{ $subdivision->code }}</code>
            <span class="text-muted small">
                <i class="bi bi-diagram-3 me-1"></i>{{ $subdivision->department->name }}
            </span>
            <span class="text-muted small">
                <i class="bi bi-building me-1"></i>{{ $subdivision->department->branch->name }}
            </span>
        </div>

        <div class="row g-4">

            {{-- ЛЕВАЯ КОЛОНКА — Форма --}}
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header py-3">
                        <h6 class="mb-0 fw-semibold">
                            <i class="bi bi-plus-circle me-2 text-primary"></i>Добавить должность
                        </h6>
                    </div>
                    <div class="card-body">
                        <form
                            method="POST"
                            action="{{ route('supervisor.subdivisions.positions.store', $subdivision) }}"
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
                                    placeholder="Главный бухгалтер"
                                    required
                                >
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="category" class="form-label fw-semibold">
                                    Категория <span class="text-danger">*</span>
                                </label>
                                <select
                                    class="form-select @error('category') is-invalid @enderror"
                                    id="category"
                                    name="category"
                                    required
                                >
                                    <option value="">— Выберите —</option>
                                    @foreach(['A', 'B', 'C', 'D'] as $cat)
                                        <option value="{{ $cat }}" {{ old('category') === $cat ? 'selected' : '' }}>
                                            Категория {{ $cat }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="grade" class="form-label fw-semibold">
                                    Разряд <span class="text-danger">*</span>
                                </label>
                                <select
                                    class="form-select @error('grade') is-invalid @enderror"
                                    id="grade"
                                    name="grade"
                                    required
                                >
                                    <option value="">— Выберите —</option>
                                    @foreach(range(1, 5) as $g)
                                        <option value="{{ $g }}" {{ old('grade') == $g ? 'selected' : '' }}>
                                            {{ $g }}-й разряд
                                        </option>
                                    @endforeach
                                </select>
                                @error('grade')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <div class="form-check form-switch">
                                    <input
                                        class="form-check-input"
                                        type="checkbox"
                                        id="is_vacant"
                                        name="is_vacant"
                                        {{ old('is_vacant') ? 'checked' : '' }}
                                    >
                                    <label class="form-check-label" for="is_vacant" style="color:#d1d5db;">
                                        Вакантная должность
                                    </label>
                                </div>
                                <div class="form-text">Отметьте если должность открыта для найма</div>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-plus-lg me-1"></i>Создать должность
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
                            <i class="bi bi-list-ul me-2 text-primary"></i>Список должностей
                        </h6>
                        <span class="badge bg-primary rounded-pill">{{ $positions->count() }}</span>
                    </div>
                    <div class="card-body p-0">
                        @if($positions->isEmpty())
                            <div class="text-center py-5 text-muted">
                                <i class="bi bi-person-badge fs-1 d-block mb-2 opacity-25"></i>
                                Должности ещё не добавлены
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="ps-3">#</th>
                                            <th>Название</th>
                                            <th>Категория</th>
                                            <th>Разряд</th>
                                            <th>Вакансия</th>
                                            <th>Статус</th>
                                            <th class="text-end pe-3">Действия</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($positions as $position)
                                        <tr>
                                            <td class="ps-3 text-muted small">{{ $loop->iteration }}</td>
                                            <td style="color:#fff" class="fw-semibold">{{ $position->name }}</td>
                                            <td>
                                                <span class="badge badge-cat-{{ $position->category }} text-white">
                                                    Кат. {{ $position->category }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary">
                                                    {{ $position->grade }}-й разряд
                                                </span>
                                            </td>
                                            <td>
                                                @if($position->is_vacant)
                                                    <span class="badge bg-warning text-dark">
                                                        <i class="bi bi-door-open me-1"></i>Вакантна
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary">Занята</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($position->is_active)
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle">
                                                        Активна
                                                    </span>
                                                @else
                                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                                        Неактивна
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="text-end pe-3">
                                                <form
                                                    method="POST"
                                                    action="{{ route('supervisor.subdivisions.positions.destroy', [$subdivision, $position]) }}"
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
            if (confirm('Удалить эту должность?')) {
                this.submit();
            }
        });
    });
    </script>
    @endpush

</x-app-layout>