<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vacancy_requests', function (Blueprint $table) {
            $table->id();

            // ─── Заказчик ─────────────────────────────────────
            $table->foreignId('requester_id')->constrained('users')->cascadeOnDelete();

            // ─── Организационная структура ────────────────────
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subdivision_id')->constrained()->cascadeOnDelete();
            $table->foreignId('position_id')->constrained()->cascadeOnDelete(); // вакантная должность

            // ─── Должностные связи ────────────────────────────
            $table->string('reports_to')->nullable();           // Кому подчиняется (должность руководителя)
            $table->json('subordinates')->nullable();           // Кто подчиняется (список должностей)

            // ─── График работы ────────────────────────────────
            $table->string('work_schedule')->nullable();        // 5/2, 6/1 и т.д.
            $table->time('work_start')->nullable();             // Время начала
            $table->time('work_end')->nullable();               // Время окончания

            // ─── Зарплата ─────────────────────────────────────
            $table->string('position_category')->nullable();    // Автоматически из должности
            $table->tinyInteger('grade')->nullable();           // Разряд (выбирается)
            $table->decimal('daily_rate', 10, 2)->nullable();   // Дневная ставка из тарифной таблицы
            $table->decimal('salary_probation', 10, 2)->nullable();   // На испытательный срок
            $table->decimal('salary_after_probation', 10, 2)->nullable(); // После испытания

            // ─── Доп. условия ─────────────────────────────────
            $table->text('bonuses')->nullable();                // Бонусы и льготы (текст)
            $table->string('workplace')->nullable();            // Место работы (филиал)

            // ─── Причина открытия ─────────────────────────────
            $table->enum('opening_reason', [
                'employee_resigned',
                'new_position',
                'workload_increased',
                'rotation',
                'handover_needed',
                'other',
            ])->nullable();

            // ─── Требования к кандидату ───────────────────────
            $table->string('age_category')->nullable();         // Возрастная категория
            $table->string('gender')->nullable();               // Пол
            $table->string('education')->nullable();            // Образование
            $table->string('experience')->nullable();           // Опыт работы
            $table->json('languages')->nullable();              // [{"lang":"ru","level":"advanced"}]
            $table->text('specialized_knowledge')->nullable();  // Специализированные знания
            $table->text('job_responsibilities')->nullable();   // Должностные обязанности
            $table->text('additional_requirements')->nullable();// Доп. требования

            // ─── Статус и согласование ────────────────────────
            $table->enum('status', [
                'draft',           // Черновик
                'submitted',       // Отправлена
                'hr_reviewed',     // HR просмотрел
                'approved',        // Руководитель одобрил
                'rejected',        // Отклонена
                'on_hold',         // Приостановлена
                'searching',       // Поиск начат
                'closed',          // HR закрыл
                'confirmed_closed',// Заказчик подтвердил закрытие
            ])->default('draft');

            $table->foreignId('hr_editor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->date('vacancy_close_deadline')->nullable(); // Срок закрытия вакансии

            $table->timestamps();
        });

        // ─── История статусов ─────────────────────────────────
        Schema::create('vacancy_request_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vacancy_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('status');
            $table->text('comment')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vacancy_request_logs');
        Schema::dropIfExists('vacancy_requests');
    }
};