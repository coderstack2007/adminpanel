{{-- resources/views/supervisor/branches.blade.php --}}

@push('styles')
<style>
    /* Карточки */
    .card {
        background-color: rgb(31, 41, 55) !important;
        color: #f9fafb !important;
    }

    .card-header {
        background-color: rgb(31, 41, 55) !important;
        border-bottom-color: rgba(255,255,255,0.1) !important;
        color: #f9fafb !important;
    }

    /* Таблица */
    .table {
        color: #f9fafb !important;
    }

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

    /* Инпуты */
    .form-control {
        background-color: rgb(17, 24, 39) !important;
        border-color: rgba(255,255,255,0.15) !important;
        color: #f9fafb !important;
    }

    .form-control::placeholder {
        color: #6b7280 !important;
    }

    .form-control:focus {
        background-color: rgb(17, 24, 39) !important;
        border-color: #6366f1 !important;
        color: #f9fafb !important;
        box-shadow: 0 0 0 0.25rem rgba(99, 102, 241, 0.25) !important;
    }

    .form-label {
        color: #d1d5db !important;
    }

    .form-text {
        color: #6b7280 !important;
    }

    /* code тег */
    code {
        background-color: rgb(17, 24, 39) !important;
        color: #a78bfa !important;
    }

    /* Badge вторичный */
    .badge.bg-secondary {
        background-color: rgb(55, 65, 81) !important;
    }
</style>
@endpush

<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center justify-content-between">
            <h2 class="h5 fw-semibold mb-0">
                <i class="bi bi-building me-2"></i>Управление филиалами
            </h2>
            <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Назад
            </a>
        </div>
    </x-slot>

    <div class="container-fluid py-4">

        {{-- Уведомления --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row g-4">

            {{-- ЛЕВАЯ КОЛОНКА — Форма создания --}}
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="mb-0 fw-semibold">
                            <i class="bi bi-plus-circle me-2 text-primary"></i>Добавить филиал
                        </h6>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('supervisor.branches.store') }}" id="branchForm">
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
                                    placeholder="Главный офис"
                                    required
                                >
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="code" class="form-label fw-semibold">
                                    Код <span class="text-danger">*</span>
                                </label>
                                <input
                                    type="text"
                                    class="form-control @error('code') is-invalid @enderror"
                                    id="code"
                                    name="code"
                                    value="{{ old('code') }}"
                                    placeholder="HQ"
                                    style="text-transform: uppercase"
                                    required
                                >
                                @error('code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Уникальный код, например: HQ, BRANCH-01</div>
                            </div>

                            <div class="mb-4">
                                <label for="address" class="form-label fw-semibold">Адрес</label>
                                <textarea
                                    class="form-control @error('address') is-invalid @enderror"
                                    id="address"
                                    name="address"
                                    rows="3"
                                    placeholder="г. Ташкент, ул. Примерная 1"
                                >{{ old('address') }}</textarea>
                                @error('address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-plus-lg me-1"></i>Создать филиал
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- ПРАВАЯ КОЛОНКА — Список филиалов --}}
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                        <h6 class="mb-0 fw-semibold">
                            <i class="bi bi-list-ul me-2 text-primary"></i>Список филиалов
                        </h6>
                        <span class="badge bg-primary rounded-pill">{{ $branches->count() }}</span>
                    </div>
                    <div class="card-body p-0">
                        @if($branches->isEmpty())
                            <div class="text-center py-5 text-muted">
                                <i class="bi bi-building fs-1 d-block mb-2 opacity-25"></i>
                                Филиалы ещё не добавлены
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="ps-3">#</th>
                                            <th>Название</th>
                                            <th>Код</th>
                                            <th>Отделов</th>
                                            <th>Статус</th>
                                            <th class="text-end pe-3">Действия</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($branches as $branch)
                                        <tr>
                                            <td class="ps-3 text-muted small">{{ $loop->iteration }}</td>
                                            <td>
                                                <div class="fw-semibold" style="color: #fff;">{{ $branch->name }}</div>
                                                @if($branch->address)
                                                    <small class="text-muted">{{ Str::limit($branch->address, 40) }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <code class="bg-light px-2 py-1 rounded">{{ $branch->code }}</code>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary rounded-pill">
                                                    {{ $branch->departments_count }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($branch->is_active)
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
                                                    action="{{ route('supervisor.branches.destroy', $branch) }}"
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
        // Подтверждение удаления
        $('.delete-form').on('submit', function (e) {
            e.preventDefault();
            const form = this;
            if (confirm('Удалить этот филиал? Это действие нельзя отменить.')) {
                form.submit();
            }
        });

        // Код в uppercase автоматически
        $('#code').on('input', function () {
            $(this).val($(this).val().toUpperCase());
        });
    });
    </script>
    @endpush

</x-app-layout>