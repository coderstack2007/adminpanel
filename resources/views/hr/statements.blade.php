{{-- resources/views/hr/statements.blade.php --}}

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
    .filter-btn {
        background: rgb(17,24,39);
        border: 1px solid rgba(255,255,255,0.12);
        color: #9ca3af;
        border-radius: 999px;
        padding: 0.25rem 0.9rem;
        font-size: 0.78rem;
        cursor: pointer;
        transition: all .2s;
    }
    .filter-btn.active, .filter-btn:hover {
        background: #6366f1;
        border-color: #6366f1;
        color: #fff;
    }
</style>
@endpush

<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center justify-content-between">
            <h2 class="h5 fw-semibold mb-0">
                <i class="bi bi-files me-2"></i>HR — Заявки на подбор
            </h2>
            <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Назад
            </a>
        </div>
    </x-slot>

    <div class="container-fluid py-4">

        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        {{-- Фильтры --}}
        <div class="d-flex align-items-center gap-2 flex-wrap mb-4">
            <button class="filter-btn active" data-filter="all">Все</button>
           
        </div>

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
                        <i class="bi bi-inbox fs-1 opacity-25 d-block mb-3"></i>
                        <p class="fs-5 mb-1">Заявок нет</p>
                        <small>Здесь появятся заявки от руководителей подразделений</small>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="statementsTable">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">#</th>
                                    <th>Должность</th>
                                    <th>Заявитель</th>
                                    <th>Подразделение</th>
                                    <th>Статус</th>
                                    <th>Отправлена</th>
                                    <th>Срок закрытия</th>
                                    <th class="text-end pe-3">Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($statements as $s)
                                <tr
                                    data-status="{{ $s->status }}"
                                    onclick="window.location='{{ route('hr.statements.show', $s) }}'"
                                >
                                    <td class="ps-3 text-muted small">{{ $loop->iteration }}</td>
                                    <td class="fw-semibold" style="color:#fff">
                                        {{ $s->position?->name ?? '—' }}
                                        @if($s->position_category)
                                            @php $colors = ['A'=>'#7c3aed','B'=>'#1d4ed8','C'=>'#065f46','D'=>'#92400e']; @endphp
                                            <span class="badge text-white ms-1"
                                                style="background:{{ $colors[$s->position_category] ?? '#6b7280' }};font-size:0.65rem">
                                                Кат. {{ $s->position_category }}
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="small" style="color:#f9fafb">{{ $s->requester?->name ?? '—' }}</div>
                                        <div class="text-muted" style="font-size:0.72rem">{{ $s->department?->name }}</div>
                                    </td>
                                    <td class="text-muted small">{{ $s->subdivision?->name ?? '—' }}</td>
                                    <td>
                                        <span class="badge bg-{{ $s->status_color }}">
                                            {{ $s->status_label }}
                                        </span>
                                        @if($s->hrEditor)
                                            <div class="text-muted" style="font-size:0.7rem">
                                                <i class="bi bi-pencil me-1"></i>{{ $s->hrEditor->name }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="text-muted small">
                                        {{ $s->submitted_at?->format('d.m.Y') ?? '—' }}
                                    </td>
                                    <td>
                                        @if($s->vacancy_close_deadline)
                                            @php
                                                $daysLeft = now()->diffInDays($s->vacancy_close_deadline, false);
                                            @endphp
                                            <span class="badge bg-{{ $daysLeft < 3 ? 'danger' : ($daysLeft < 7 ? 'warning text-dark' : 'secondary') }}">
                                                {{ $s->vacancy_close_deadline->format('d.m.Y') }}
                                            </span>
                                        @else
                                            <span class="text-muted small">—</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-3" onclick="event.stopPropagation()">
                                        <a href="{{ route('hr.statements.show', $s) }}"
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye me-1"></i>Открыть
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

    @push('scripts')
    <script>
    $(function () {
        $('.filter-btn').on('click', function () {
            $('.filter-btn').removeClass('active');
            $(this).addClass('active');

            const filter = $(this).data('filter');
            $('#statementsTable tbody tr').each(function () {
                if (filter === 'all' || $(this).data('status') === filter) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });
    });
    </script>
    @endpush

</x-app-layout>