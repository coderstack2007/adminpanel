<?php

use App\Http\Controllers\BranchController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\SubdivisionController;
use App\Http\Controllers\PositionController;
use Illuminate\Support\Facades\Route;




Route::prefix('supervisor')
    ->name('supervisor.')
    ->middleware(['auth', 'role:super_admin'])
    ->group(function () {

        Route::get('/dashboard', fn() => view('supervisor.dashboard'))
            ->name('dashboard');

        // Branches
        Route::resource('branches', BranchController::class)
            ->only(['index', 'store', 'destroy'])
            ->names('branches');

        // Departments → вложен в branch
        Route::prefix('branches/{branch}')->name('branches.')->group(function () {
            Route::get('departments', [DepartmentController::class, 'index'])
                ->name('departments.index');
            Route::post('departments', [DepartmentController::class, 'store'])
                ->name('departments.store');
            Route::delete('departments/{department}', [DepartmentController::class, 'destroy'])
                ->name('departments.destroy');
        });

        // Subdivisions → вложен в department
        Route::prefix('departments/{department}')->name('departments.')->group(function () {
            Route::get('subdivisions', [SubdivisionController::class, 'index'])
                ->name('subdivisions.index');
            Route::post('subdivisions', [SubdivisionController::class, 'store'])
                ->name('subdivisions.store');
            Route::delete('subdivisions/{subdivision}', [SubdivisionController::class, 'destroy'])
                ->name('subdivisions.destroy');
        });

        // Positions → вложен в subdivision
        Route::prefix('subdivisions/{subdivision}')->name('subdivisions.')->group(function () {
            Route::get('positions', [PositionController::class, 'index'])
                ->name('positions.index');
            Route::post('positions', [PositionController::class, 'store'])
                ->name('positions.store');
            Route::delete('positions/{position}', [PositionController::class, 'destroy'])
                ->name('positions.destroy');
        });
    });