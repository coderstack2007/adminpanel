<?php
// ═══════════════════════════════════════════════════════════════
// 2. routes/hr.php  — ЗАМЕНИТЬ ПОЛНОСТЬЮ
// ═══════════════════════════════════════════════════════════════

use App\Http\Controllers\HR\HrDepartmentController;
use App\Http\Controllers\HR\HrSubdivisionController;
use App\Http\Controllers\HR\HrStatementController;
use Illuminate\Support\Facades\Route;

Route::prefix('hr')
    ->name('hr.')
    ->middleware(['auth', 'role:hr_manager|super_admin'])
    ->group(function () {

        Route::get('/', fn() => redirect()->route('hr.departments.index'))->name('dashboard');

        // Структура
        Route::get('/departments', [HrDepartmentController::class, 'index'])
            ->name('departments.index');
        Route::get('/departments/{department}/subdivisions', [HrSubdivisionController::class, 'index'])
            ->name('departments.subdivisions.index');

        // Заявки
        Route::get('/statements', [HrStatementController::class, 'index'])
            ->name('statements.index');
        Route::get('/statements/{statement}', [HrStatementController::class, 'show'])
            ->name('statements.show');

        // HR редактирует заявку (пока не отправлена supervisor'у)
        Route::put('/statements/{statement}', [HrStatementController::class, 'update'])
            ->name('statements.update');

        // Отправить на подпись supervisor'у (super_admin)
        Route::post('/statements/{statement}/send-supervisor', [HrStatementController::class, 'sendToSupervisor'])
            ->name('statements.send-supervisor');

        // Закрыть вакансию (после решения supervisor'а) — оставлено на потом
        // Route::post('/statements/{statement}/close', [HrStatementController::class, 'close'])
        //     ->name('statements.close');
    });