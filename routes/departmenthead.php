<?php
// ═══════════════════════════════════════════════════════════════
// 1. routes/departmenthead.php  — ЗАМЕНИТЬ ПОЛНОСТЬЮ
// ═══════════════════════════════════════════════════════════════

use App\Http\Controllers\DepartmentHead\StatementsController;
use Illuminate\Support\Facades\Route;

Route::prefix('department-head')
    ->name('department_head.')
    ->middleware(['auth', 'role:department_head|super_admin'])
    ->group(function () {

        Route::get('/statements', [StatementsController::class, 'index'])
            ->name('statements.index');
        Route::get('/statements/create', [StatementsController::class, 'create'])
            ->name('statements.create');
        Route::post('/statements', [StatementsController::class, 'store'])
            ->name('statements.store');
        Route::get('/statements/{statement}', [StatementsController::class, 'show'])
            ->name('statements.show');

        // Редактирование черновика
        Route::get('/statements/{statement}/edit', [StatementsController::class, 'edit'])
            ->name('statements.edit');
        Route::put('/statements/{statement}', [StatementsController::class, 'update'])
            ->name('statements.update');

        // Отправить в HR
        Route::post('/statements/{statement}/submit', [StatementsController::class, 'submit'])
            ->name('statements.submit');

        // Подтвердить закрытие вакансии
        Route::post('/statements/{statement}/confirm-close', [StatementsController::class, 'confirmClose'])
            ->name('statements.confirm-close');
    });