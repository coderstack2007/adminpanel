<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ─── Справочник статусов ──────────────────────────────
        Schema::create('states', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();          // 'draft', 'submitted', etc.
            $table->string('label_ru');               // 'Черновик', 'Отправлена'
            $table->string('color')->default('secondary'); // Bootstrap color
            $table->unsignedTinyInteger('order')->default(0);
            $table->timestamps();
        });

        // ─── Уведомления (bell icon) ──────────────────────────
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vacancy_request_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('type');                   // 'submitted', 'approved', 'rejected', etc.
            $table->text('message');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        // ─── Изменения в vacancy_requests ─────────────────────
        Schema::table('vacancy_requests', function (Blueprint $table) {
            // Связь с таблицей states
            $table->foreignId('state_id')->nullable()->after('status')
                  ->constrained('states')->nullOnDelete();

            // Кто последний редактировал (HR)
            $table->foreignId('edited_by')->nullable()->after('hr_editor_id')
                  ->constrained('users')->nullOnDelete();

            // Supervisor поля
            $table->foreignId('supervisor_id')->nullable()->after('edited_by')
                  ->constrained('users')->nullOnDelete();
            $table->text('supervisor_comment')->nullable()->after('supervisor_id');
        $table->timestamp('supervisor_reviewed_at')->nullable()->after('supervisor_comment');
            $table->timestamp('sent_to_supervisor_at')->nullable()->after('supervisor_reviewed_at');
        });
    }

    public function down(): void
    {
        Schema::table('vacancy_requests', function (Blueprint $table) {
            $table->dropForeign(['state_id']);
            $table->dropForeign(['edited_by']);
            $table->dropForeign(['supervisor_id']);
            $table->dropColumn(['state_id', 'edited_by', 'supervisor_id', 'supervisor_comment', 'ed_at', 'sent_to_supervisor_at']);
        });

        Schema::dropIfExists('notifications');
        Schema::dropIfExists('states');
    }
};