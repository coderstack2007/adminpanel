{{-- resources/views/department_head/statement_create.blade.php --}}

@push('styles')
<style>
    .card { background-color: rgb(31, 41, 65) !important; color: #f9fafb !important; }
    .card-header {
        background-color: rgb(31, 41, 55) !important;
        border-bottom-color: rgba(255,255,255,0.1) !important;
        color: #f9fafb !important;
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
    .form-select option { background-color: rgb(17, 24, 39); color: #f9fafb; }
    .form-label { color: #d1d5db !important; }
    .form-text  { color: #6b7280 !important; }
    .section-title {
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #6366f1;
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 1px solid rgba(99,102,241,0.3);
    }
    .info-badge {
        background-color: rgb(17, 24, 39);
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 8px;
        padding: 0.5rem 1rem;
        color: #f9fafb;
        font-size: 0.85rem;
    }
    .lang-row { background: rgb(17,24,39); border-radius: 8px; padding: 0.75rem; margin-bottom: 0.5rem; }
    hr { border-color: rgba(255,255,255,0.08) !important; }
    code { background-color: rgb(17, 24, 39) !important; color: #a78bfa !important; }
</style>
@endpush


<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h2 class="h5 fw-semibold mb-1">
                    <i class="bi bi-file-earmark-plus me-2"></i>Новая заявка на подбор персонала
                </h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0" style="font-size:0.8rem;">
                        <li class="breadcrumb-item">
                            <a href="{{ route('department_head.statements.index') }}" style="color:#6366f1">
                                Мои заявки
                            </a>
                        </li>
                        <li class="breadcrumb-item active" style="color:#9ca3af">Создать</li>
                    </ol>
                </nav>
            </div>
            <a href="{{ route('department_head.statements.index') }}"
               class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Назад
            </a>
        </div>
    </x-slot>

    <div class="container-fluid py-4">

        @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show mb-4">
            <i class="bi bi-exclamation-circle me-2"></i>
            Пожалуйста, исправьте ошибки в форме.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <form method="POST" action="{{ route('department_head.statements.store') }}">
            @csrf

            <div class="row g-4">

                {{-- ─── ЛЕВАЯ КОЛОНКА ──────────────────────────── --}}
                <div class="col-lg-8">

                    {{-- БЛОК 1: Основная информация --}}
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header py-3">
                            <h6 class="mb-0 fw-semibold">
                                <i class="bi bi-person-badge me-2 text-primary"></i>Основная информация
                            </h6>
                        </div>
                        <div class="card-body">

                            <p class="section-title">Заявитель</p>

                            {{-- ФИО автоматически --}}
                            <div class="mb-3 d-flex align-items-center gap-3">
                                <span class="text-muted small" style="min-width:120px">Ф.И.О.</span>
                                <div class="info-badge flex-grow-1">
                                    <i class="bi bi-person me-2 text-primary"></i>{{ $user->name }}
                                </div>
                            </div>

                            <div class="mb-3 d-flex align-items-center gap-3">
                                <span class="text-muted small" style="min-width:120px">Отдел</span>
                                <div class="info-badge flex-grow-1">
                                    <i class="bi bi-diagram-3 me-2 text-primary"></i>
                                    {{ $user->department?->name ?? '—' }}
                                </div>
                            </div>

                            <div class="mb-4 d-flex align-items-center gap-3">
                                <span class="text-muted small" style="min-width:120px">Подразделение</span>
                                <div class="info-badge flex-grow-1">
                                    <i class="bi bi-diagram-2 me-2 text-primary"></i>
                                    {{ $user->subdivision?->name ?? '—' }}
                                </div>
                            </div>

                            <hr>
                            <p class="section-title mt-3">Вакантная должность</p>

                            {{-- Должность --}}
                            <div class="mb-3">
                                <label class="form-label fw-semibold">
                                    Должность <span class="text-danger">*</span>
                                </label>
                                <select
                                    class="form-select @error('position_id') is-invalid @enderror"
                                    name="position_id"
                                    id="position_select"
                                    required
                                >
                                    <option value="">— Выберите вакантную должность —</option>
                                    @foreach($vacantPositions as $pos)
                                        <option
                                            value="{{ $pos->id }}"
                                            data-category="{{ $pos->category }}"
                                            data-grade="{{ $pos->grade }}"
                                            {{ old('position_id') == $pos->id ? 'selected' : '' }}
                                        >
                                            {{ $pos->name }} (Кат. {{ $pos->category }}, {{ $pos->grade }}-й разряд)
                                        </option>
                                    @endforeach
                                </select>
                                @error('position_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                @if($vacantPositions->isEmpty())
                                    <div class="form-text text-warning">
                                        <i class="bi bi-exclamation-triangle me-1"></i>
                                        Нет вакантных должностей в вашем подразделении
                                    </div>
                                @endif
                            </div>

                            {{-- Категория (авто) + Разряд --}}
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Категория</label>
                                    <div class="info-badge" id="category_display">
                                        — выберите должность —
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Разряд</label>
                                    <select class="form-select @error('grade') is-invalid @enderror" name="grade">
                                        <option value="">— Выберите —</option>
                                        @foreach(range(1,5) as $g)
                                            <option value="{{ $g }}" {{ old('grade') == $g ? 'selected' : '' }}>
                                                {{ $g }}-й разряд
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('grade') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            {{-- Кому подчиняется --}}
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Кому подчиняется</label>
                                <div class="info-badge">
                                    @if($user->subdivision?->head)
                                        <i class="bi bi-person-check me-2 text-success"></i>
                                        {{ $user->subdivision->head->name }}
                                        @if($user->subdivision->head->position)
                                            — <span class="text-muted">{{ $user->subdivision->head->position->name }}</span>
                                        @endif
                                    @else
                                        <span class="text-muted">Руководитель не назначен</span>
                                    @endif
                                </div>
                                <input type="hidden" name="reports_to"
                                    value="{{ $user->subdivision?->head?->position?->name ?? '' }}">
                            </div>

                            {{-- Кто подчиняется --}}
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Кто подчиняется (если есть)</label>
                                <div class="row g-2">
                                    @foreach($allPositions as $pos)
                                    <div class="col-md-6">
                                        <div class="form-check" style="color:#d1d5db">
                                            <input
                                                class="form-check-input"
                                                type="checkbox"
                                                name="subordinates[]"
                                                value="{{ $pos->name }}"
                                                id="sub_{{ $pos->id }}"
                                                {{ in_array($pos->name, old('subordinates', [])) ? 'checked' : '' }}
                                            >
                                            <label class="form-check-label" for="sub_{{ $pos->id }}">
                                                {{ $pos->name }}
                                            </label>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                @if($allPositions->isEmpty())
                                    <div class="form-text text-muted">Нет других должностей в подразделении</div>
                                @endif
                            </div>

                        </div>
                    </div>

                    {{-- БЛОК 2: График и зарплата --}}
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header py-3">
                            <h6 class="mb-0 fw-semibold">
                                <i class="bi bi-clock me-2 text-primary"></i>График и зарплата
                            </h6>
                        </div>
                        <div class="card-body">

                            <p class="section-title">Рабочий график</p>

                            <div class="row g-3 mb-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">График</label>
                                    <select class="form-select @error('work_schedule') is-invalid @enderror" name="work_schedule">
                                        <option value="">— Выберите —</option>
                                        @foreach(['5/2','6/1','7/0','2/2'] as $sch)
                                            <option value="{{ $sch }}" {{ old('work_schedule') === $sch ? 'selected' : '' }}>
                                                {{ $sch }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Начало работы</label>
                                    <input type="time" class="form-control" name="work_start"
                                        value="{{ old('work_start', '09:00') }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Конец работы</label>
                                    <input type="time" class="form-control" name="work_end"
                                        value="{{ old('work_end', '18:00') }}">
                                </div>
                            </div>

                            <hr>
                            <p class="section-title mt-3">Заработная плата</p>

                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">На испытательный срок</label>
                                    <div class="input-group">
                                        <input
                                            type="number"
                                            class="form-control @error('salary_probation') is-invalid @enderror"
                                            name="salary_probation"
                                            value="{{ old('salary_probation') }}"
                                            placeholder="0"
                                            min="0"
                                        >
                                        <span class="input-group-text" style="background:rgb(17,24,39);color:#9ca3af;border-color:rgba(255,255,255,0.15)">
                                            сум
                                        </span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">После испытательного срока</label>
                                    <div class="input-group">
                                        <input
                                            type="number"
                                            class="form-control @error('salary_after_probation') is-invalid @enderror"
                                            name="salary_after_probation"
                                            value="{{ old('salary_after_probation') }}"
                                            placeholder="0"
                                            min="0"
                                        >
                                        <span class="input-group-text" style="background:rgb(17,24,39);color:#9ca3af;border-color:rgba(255,255,255,0.15)">
                                            сум
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Бонусы и льготы</label>
                                <textarea
                                    class="form-control"
                                    name="bonuses"
                                    rows="3"
                                    placeholder="Опишите бонусы, льготы и дополнительные условия..."
                                >{{ old('bonuses') }}</textarea>
                            </div>

                        </div>
                    </div>

                    {{-- БЛОК 3: Требования к кандидату --}}
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header py-3">
                            <h6 class="mb-0 fw-semibold">
                                <i class="bi bi-person-check me-2 text-primary"></i>Требования к кандидату
                            </h6>
                        </div>
                        <div class="card-body">

                            <p class="section-title">Основные данные</p>

                            <div class="row g-3 mb-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Возраст</label>
                                    <input type="text" class="form-control" name="age_category"
                                        value="{{ old('age_category') }}" placeholder="н-р 25–40">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Пол</label>
                                    <select class="form-select" name="gender">
                                        <option value="">— Не важно —</option>
                                        <option value="male"   {{ old('gender') === 'male'   ? 'selected' : '' }}>Мужской</option>
                                        <option value="female" {{ old('gender') === 'female' ? 'selected' : '' }}>Женский</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Образование</label>
                                    <select class="form-select" name="education">
                                        <option value="">— Выберите —</option>
                                        @foreach(['Среднее','Среднее специальное','Высшее','Магистратура'] as $edu)
                                            <option value="{{ $edu }}" {{ old('education') === $edu ? 'selected' : '' }}>
                                                {{ $edu }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Опыт работы</label>
                                <input type="text" class="form-control" name="experience"
                                    value="{{ old('experience') }}" placeholder="н-р от 2 лет в сфере...">
                            </div>

                          <hr>
                        <p class="section-title mt-3">Знание языков</p>

                            <div id="languages_container">
                                @php
                                    $defaultLanguages = ['Русский', 'Английский', 'Узбекский'];
                                    $oldLanguages = old('languages', []);
                                @endphp
                                
                                @foreach($defaultLanguages as $index => $langName)
                                <div class="lang-row d-flex align-items-center gap-2 mb-2">
                                    {{-- Скрытый input с названием языка --}}
                                    <input type="hidden" name="languages[{{ $index }}][lang]" value="{{ $langName }}">
                                    
                                    {{-- Отображение названия языка --}}
                                    <div class="info-badge" style="min-width:140px; max-width:140px;">
                                        <i class="bi bi-translate me-1 text-primary"></i>
                                        {{ $langName }}
                                    </div>
                                    
                                    {{-- Выбор уровня --}}
                                    <select 
                                        class="form-select" 
                                        name="languages[{{ $index }}][level]" 
                                        style="max-width:180px"
                                    >
                                        <option value="">— Уровень не указан —</option>
                                        @foreach(['Начальный', 'Средний', 'Свободный', 'Родной'] as $lv)
                                            <option 
                                                value="{{ $lv }}" 
                                                {{ (isset($oldLanguages[$index]) && ($oldLanguages[$index]['level'] ?? '') === $lv) ? 'selected' : '' }}
                                            >
                                                {{ $lv }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @endforeach
                            </div>

                            <div class="form-text text-muted mt-2">
                                <i class="bi bi-info-circle me-1"></i>
                                Выберите уровень для тех языков, которыми владеет кандидат. Если язык не требуется, оставьте "Уровень не указан".
                            </div>

                  

                            <hr class="mt-4">
                            <p class="section-title mt-3">Профессиональные требования</p>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Специализированные знания</label>
                                <textarea class="form-control" name="specialized_knowledge" rows="3"
                                    placeholder="1С, Excel, знание налогового законодательства...">{{ old('specialized_knowledge') }}</textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Должностные обязанности</label>
                                <textarea class="form-control" name="job_responsibilities" rows="4"
                                    placeholder="Подробно опишите обязанности сотрудника...">{{ old('job_responsibilities') }}</textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Дополнительные требования</label>
                                <textarea class="form-control" name="additional_requirements" rows="3"
                                    placeholder="Любые другие требования...">{{ old('additional_requirements') }}</textarea>
                            </div>

                        </div>
                    </div>

                </div>

                {{-- ─── ПРАВАЯ КОЛОНКА ──────────────────────────── --}}
                <div class="col-lg-4">

                    {{-- Причина открытия --}}
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header py-3">
                            <h6 class="mb-0 fw-semibold">
                                <i class="bi bi-question-circle me-2 text-primary"></i>Причина открытия
                            </h6>
                        </div>
                        <div class="card-body">
                            <label class="form-label fw-semibold">Причина <span class="text-danger">*</span></label>
                            <select class="form-select @error('opening_reason') is-invalid @enderror" name="opening_reason" required>
                                <option value="">— Выберите причину —</option>
                                @foreach(\App\Models\VacancyRequest::OPENING_REASON_LABELS as $val => $label)
                                    <option value="{{ $val }}" {{ old('opening_reason') === $val ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('opening_reason')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Место работы --}}
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header py-3">
                            <h6 class="mb-0 fw-semibold">
                                <i class="bi bi-building me-2 text-primary"></i>Место работы
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="info-badge">
                                <i class="bi bi-geo-alt me-2 text-warning"></i>
                                {{ $user->branch?->name ?? '—' }}
                            </div>
                            <input type="hidden" name="workplace" value="{{ $user->branch?->name ?? '' }}">
                        </div>
                    </div>

                    {{-- Срок закрытия --}}
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header py-3">
                            <h6 class="mb-0 fw-semibold">
                                <i class="bi bi-calendar me-2 text-primary"></i>Срок закрытия вакансии
                            </h6>
                        </div>
                        <div class="card-body">
                            <input type="date" class="form-control" name="vacancy_close_deadline"
                                value="{{ old('vacancy_close_deadline') }}"
                                min="{{ now()->addDay()->format('Y-m-d') }}">
                        </div>
                    </div>

                    {{-- Кнопки --}}
                    <div class="card border-0 shadow-sm">
                        <div class="card-body d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-2"></i>Сохранить черновик
                            </button>
                            <a href="{{ route('department_head.statements.index') }}"
                               class="btn btn-outline-secondary">
                                <i class="bi bi-x me-1"></i>Отмена
                            </a>
                        </div>
                        <div class="card-footer" style="background:transparent;border-color:rgba(255,255,255,0.08)">
                            <small class="text-muted">
                                <i class="bi bi-info-circle me-1"></i>
                                После сохранения вы сможете проверить и отправить заявку
                            </small>
                        </div>
                    </div>

                </div>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
    $(function () {
        // ─── Автозаполнение категории при выборе должности ───
        $('#position_select').on('change', function () {
            const cat = $(this).find(':selected').data('category');
            $('#category_display').html(
                cat
                    ? `<span class="badge text-white" style="background:${{ "{'A':'#7c3aed','B':'#1d4ed8','C':'#065f46','D':'#92400e'}" }}[cat] || '#6b7280'"}>Категория ${cat}</span>`
                    : '— выберите должность —'
            );
        });

        // ─── Добавление языка ────────────────────────────────
        let langIdx = {{ count(old('languages', [[]])) }};

        $('#add_lang').on('click', function () {
            const tpl = `
                <div class="lang-row d-flex align-items-center gap-2 mb-2">
                    <select class="form-select" name="languages[${langIdx}][lang]" style="max-width:160px">
                        <option value="">— Язык —</option>
                        <option value="Русский">Русский</option>
                        <option value="Английский">Английский</option>
                        <option value="Узбекский">Узбекский</option>
                        <option value="Другой">Другой</option>
                    </select>
                    <select class="form-select" name="languages[${langIdx}][level]" style="max-width:180px">
                        <option value="">— Уровень —</option>
                        <option value="Начальный">Начальный</option>
                        <option value="Средний">Средний</option>
                        <option value="Свободный">Свободный</option>
                    </select>
                    <button type="button" class="btn btn-sm btn-outline-danger remove-lang" style="flex-shrink:0">
                        <i class="bi bi-x"></i>
                    </button>
                </div>`;
            $('#languages_container').append(tpl);
            langIdx++;
        });

        // ─── Удаление строки языка ───────────────────────────
        $(document).on('click', '.remove-lang', function () {
            const rows = $('#languages_container .lang-row');
            if (rows.length > 1) {
                $(this).closest('.lang-row').remove();
            }
        });

        // ─── Авто категория при загрузке (если old()) ────────
        const colors = {A:'#7c3aed',B:'#1d4ed8',C:'#065f46',D:'#92400e'};
        const initCat = $('#position_select').find(':selected').data('category');
        if (initCat) {
            $('#category_display').html(
                `<span class="badge text-white" style="background:${colors[initCat] || '#6b7280'}">Категория ${initCat}</span>`
            );
        }
    });
    </script>
    @endpush

</x-app-layout>