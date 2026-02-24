<?php

use App\Http\Controllers\Supervisor\SubdivisionController;
use Illuminate\Support\Facades\Route;

Route::prefix('employee')
    ->name('employee.')
    ->middleware(['auth', 'role:employee|department_head'])
    ->group(function () {
        Route::get('/subdivision', [SubdivisionController::class, 'employeeView'])
            ->name('subdivision.index');
    });