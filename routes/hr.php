<?php

use App\Http\Controllers\HR\HrDepartmentController;
use App\Http\Controllers\HR\HrSubdivisionController;
use Illuminate\Support\Facades\Route;

Route::prefix('hr')
  
    ->middleware(['auth', 'role:hr_manager|department_head'])
    ->group(function () {

        Route::get('/', function () {
            return redirect()->route('hr.departments.index');
        })->name('dashboard');

        // Отделы
        Route::get('/departments', [HrDepartmentController::class, 'index'])
            ->name('hr.departments.index');

        // Подразделения отдела
        Route::get('/departments/{department}/subdivisions', [HrSubdivisionController::class, 'index'])
            ->name('hr.departments.subdivisions.index');
    });