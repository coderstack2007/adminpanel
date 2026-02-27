<?php
// ═══════════════════════════════════════════════════════════════
// 4. routes/web.php  — ЗАМЕНИТЬ ПОЛНОСТЬЮ
// ═══════════════════════════════════════════════════════════════

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect()->route('login'));

Route::get('/dashboard', fn() => view('dashboard'))
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ── Уведомления (bell icon) — доступны всем авторизованным ──
    Route::get('/notifications', [NotificationController::class, 'index'])
        ->name('notifications.index');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markRead'])
        ->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])
        ->name('notifications.read-all');
});

require __DIR__.'/auth.php';
require __DIR__.'/supervisor.php';
require __DIR__.'/employee.php';
require __DIR__.'/hr.php';
require __DIR__.'/departmenthead.php';