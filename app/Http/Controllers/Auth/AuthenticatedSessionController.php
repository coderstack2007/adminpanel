<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $user = Auth::user();

        // Блокировка деактивированных пользователей
        if (!$user->is_active) {
            Auth::logout();
            return back()->withErrors([
                'email' => 'Ваш аккаунт деактивирован. Обратитесь к администратору.',
            ]);
        }

        $request->session()->regenerate();

        // Обновляем время последнего входа
        $user->update(['last_login_at' => now()]);

        // Редирект по роли
        return redirect()->intended($this->redirectTo($user));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    private function redirectTo($user): string
    {
        return match(true) {
            $user->hasRole('super_admin')     => route('dashboard'),
            $user->hasRole('hr_manager')      => route('dashboard'),
            $user->hasRole('hr_staff')        => route('dashboard'),
            $user->hasRole('department_head') => route('dashboard'),
            $user->hasRole('requester')       => route('dashboard'),
            default                           => route('dashboard'),
        };
    }
}