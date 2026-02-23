<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SuperAdminOnly
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Не авторизован — на логин
        if (!$user) {
            return redirect()->route('login');
        }

        // Проверяем роль super_admin
        if (!$user->hasRole('super_admin')) {
            abort(403, 'Доступ запрещён.');
        }

        return $next($request);
    }
}