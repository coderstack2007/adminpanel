<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    protected $connection = 'resume_bot';

    public function up(): void
    {
       

        // Добавляем vacancy_id
        DB::connection('resume_bot')->statement("
            ALTER TABLE resumes
            ADD COLUMN vacancy_id INT NULL AFTER city_id,
            ADD INDEX idx_vacancy_id (vacancy_id)
        ");
    }

    public function down(): void
    {
        DB::connection('resume_bot')->statement("
            ALTER TABLE resumes
            DROP INDEX idx_vacancy_id,
            DROP COLUMN vacancy_id
        ");

      
    }
};