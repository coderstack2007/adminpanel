<?php

use App\Http\Controllers\HR\HrDepartmentController;
use App\Http\Controllers\HR\HrSubdivisionController;
use App\Http\Controllers\HR\HrStatementController;
use App\Http\Controllers\DepartmentHead\StatementsController;
use Illuminate\Support\Facades\Route;

Route::prefix('hr')
    ->name('hr.')
    ->middleware(['auth', 'role:hr_manager|super_admin'])
    ->group(function () {

        Route::get('/', function () {
            return redirect()->route('hr.departments.index');
        })->name('dashboard');

        Route::get('/departments', [HrDepartmentController::class, 'index'])
            ->name('departments.index');

        Route::get('/departments/{department}/subdivisions', [HrSubdivisionController::class, 'index'])
            ->name('departments.subdivisions.index');

        Route::get('/statements', [HrStatementController::class, 'index'])
            ->name('statements.index');

        Route::get('/statements/{statement}', [HrStatementController::class, 'show'])
            ->name('statements.show');

        Route::put('/statements/{statement}', [StatementsController::class, 'update'])
            ->name('statements.update');
    });