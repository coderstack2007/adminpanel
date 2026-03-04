<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }} — Вход</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body {
            background-color: rgb(17, 24, 39);
            color: #e5e7eb;
            font-family: 'Figtree', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background-color: rgb(31, 41, 55);
            border: 1px solid rgb(55, 65, 81);
            border-radius: 12px;
            width: 100%;
            max-width: 420px;
            padding: 2.5rem;
        }
        .form-control {
            background-color: rgb(17, 24, 39);
            border: 1px solid rgb(55, 65, 81);
            color: #e5e7eb;
        }
        .form-control:focus {
            background-color: rgb(17, 24, 39);
            border-color: #60a5fa;
            color: #e5e7eb;
            box-shadow: 0 0 0 0.2rem rgba(96, 165, 250, 0.15);
        }
        .form-label { color: #9ca3af; font-size: 0.875rem; }
        .btn-primary {
            background-color: #3b82f6;
            border-color: #3b82f6;
            width: 100%;
            padding: 0.6rem;
        }
        .btn-primary:hover { background-color: #2563eb; border-color: #2563eb; }
        .logo-wrap { text-align: center; margin-bottom: 2rem; }
        .logo-wrap svg { height: 48px; width: auto; fill: #e5e7eb; }
        .app-name { font-size: 1.4rem; font-weight: 600; margin-top: 0.5rem; color: #e5e7eb; }
        a { color: #60a5fa; }
        a:hover { color: #93c5fd; }
        .invalid-feedback { display: block; }
        .form-check-input {
            background-color: rgb(17,24,39);
            border-color: rgb(55,65,81);
        }
        .form-check-label { color: #9ca3af; font-size: 0.875rem; }
    </style>
</head>
<body>
    <div class="login-card">

        <div class="logo-wrap">
            <x-application-logo />
            <div class="app-name">{{ config('app.name') }}</div>
            <small class="text-secondary">Войдите в свой аккаунт</small>
        </div>

        {{-- Session Status --}}
        @if (session('status'))
            <div class="alert alert-success mb-3" style="background: rgba(34,197,94,0.1); border-color: rgba(34,197,94,0.3); color: #86efac;">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            {{-- Email --}}
            <div class="mb-3">
                <label class="form-label">Email</label>
                <div class="input-group">
                    <span class="input-group-text" style="background: rgb(17,24,39); border-color: rgb(55,65,81); color: #6b7280;">
                        <i class="bi bi-envelope"></i>
                    </span>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                           value="{{ old('email') }}" required autofocus autocomplete="username"
                           placeholder="you@example.com">
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            {{-- Password --}}
            <div class="mb-3">
                <div class="d-flex justify-content-between">
                    <label class="form-label">Пароль</label>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="small">Забыли пароль?</a>
                    @endif
                </div>
                <div class="input-group">
                    <span class="input-group-text" style="background: rgb(17,24,39); border-color: rgb(55,65,81); color: #6b7280;">
                        <i class="bi bi-lock"></i>
                    </span>
                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                           required autocomplete="current-password" placeholder="••••••••">
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            {{-- Remember me --}}
            <div class="mb-4 form-check">
                <input type="checkbox" class="form-check-input" id="remember_me" name="remember">
                <label class="form-check-label" for="remember_me">Запомнить меня</label>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="bi bi-box-arrow-in-right me-2"></i>Войти
            </button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>