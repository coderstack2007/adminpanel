<?php
// ═══════════════════════════════════════════════════════════════
// 3. routes/supervisor.php  — ЗАМЕНИТЬ ПОЛНОСТЬЮ
// ═══════════════════════════════════════════════════════════════

use App\Http\Controllers\Supervisor\BranchController;
use App\Http\Controllers\Supervisor\DepartmentController;
use App\Http\Controllers\Supervisor\SubdivisionController;
use App\Http\Controllers\Supervisor\PositionController;
use App\Http\Controllers\Supervisor\StatementController;
use Illuminate\Support\Facades\Route;

Route::prefix('supervisor')
    ->name('supervisor.')
    ->middleware(['auth', 'super_admin_only'])
    ->group(function () {

        Route::get('/dashboard', fn() => view('supervisor.dashboard'))
            ->name('dashboard');

        // ── Структура компании ────────────────────────────────
        Route::resource('branches', BranchController::class)
            ->only(['index', 'store', 'destroy'])
            ->names('branches');

        Route::prefix('branches/{branch}')->name('branches.')->group(function () {
            Route::get('departments', [DepartmentController::class, 'index'])->name('departments.index');
            Route::post('departments', [DepartmentController::class, 'store'])->name('departments.store');
            Route::delete('departments/{department}', [DepartmentController::class, 'destroy'])->name('departments.destroy');
        });

        Route::prefix('departments/{department}')->name('departments.')->group(function () {
            Route::get('subdivisions', [SubdivisionController::class, 'index'])->name('subdivisions.index');
            Route::post('subdivisions', [SubdivisionController::class, 'store'])->name('subdivisions.store');
            Route::delete('subdivisions/{subdivision}', [SubdivisionController::class, 'destroy'])->name('subdivisions.destroy');
        });

        Route::prefix('subdivisions/{subdivision}')->name('subdivisions.')->group(function () {
            Route::get('positions', [PositionController::class, 'index'])->name('positions.index');
            Route::post('positions', [PositionController::class, 'store'])->name('positions.store');
            Route::delete('positions/{position}', [PositionController::class, 'destroy'])->name('positions.destroy');
        });

        // ── Заявки на подбор персонала ────────────────────────
        Route::get('/statements', [StatementController::class, 'index'])
            ->name('statements.index');
        Route::get('/statements/{statement}', [StatementController::class, 'show'])
            ->name('statements.show');
        Route::post('/statements/{statement}/approve', [StatementController::class, 'approve'])
            ->name('statements.approve');
        Route::post('/statements/{statement}/reject', [StatementController::class, 'reject'])
            ->name('statements.reject');

        // ВАЖНО: в твоём старом коде был 'hold', в новом контроллере метод называется 'onHold'
        // Роут оставляем 'hold' для совместимости — метод в контроллере переименуй или добавь alias
        Route::post('/statements/{statement}/hold', [StatementController::class, 'onHold'])
            ->name('statements.on-hold');
    });