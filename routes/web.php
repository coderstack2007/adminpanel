<?php
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BranchController;
Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


// Branches 
Route::prefix('supervisor')
    ->name('supervisor.')
    ->middleware(['auth', 'role:super_admin'])
    ->group(function () {

        Route::get('/dashboard', fn() => view('supervisor.dashboard'))
            ->name('dashboard');

        Route::resource('branches', BranchController::class)
            ->only(['index', 'store', 'destroy'])
            ->names('branches');
    });

// Back to dashboard
Route::get('/dashboard', fn() => view('dashboard'))
    ->middleware(['auth', 'role:super_admin'])
    ->name('dashboard');
require __DIR__.'/auth.php';