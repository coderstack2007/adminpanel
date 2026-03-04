<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('employee_code')->unique()->nullable()->after('id');
            $table->foreignId('branch_id')->nullable()->after('email')->constrained()->nullOnDelete();
            $table->foreignId('department_id')->nullable()->after('branch_id')->constrained()->nullOnDelete();
            $table->foreignId('subdivision_id')->nullable()->after('department_id')->constrained()->nullOnDelete();
            $table->foreignId('position_id')->nullable()->after('subdivision_id')->constrained()->nullOnDelete();
            $table->boolean('is_active')->default(true)->after('position_id');
            $table->timestamp('last_login_at')->nullable()->after('is_active');
        });
    }
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('branch_id');
            $table->dropConstrainedForeignId('department_id');
            $table->dropConstrainedForeignId('subdivision_id');
            $table->dropConstrainedForeignId('position_id');
            $table->dropColumn(['employee_code', 'is_active', 'last_login_at']);
        });
    }
};