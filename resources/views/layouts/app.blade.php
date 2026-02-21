<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body { background-color: rgb(17,24,39) !important; color: #e5e7eb; font-family: 'Figtree', sans-serif; }
        .page-header { background-color: rgb(31,41,55); border-bottom: 1px solid rgb(55,65,81); padding: 1rem 1.5rem; }
        .card { background-color: rgb(31,41,55) !important; border: 1px solid rgb(55,65,81) !important; color: #e5e7eb !important; }
        .card-header { background-color: rgb(37,49,65) !important; border-bottom: 1px solid rgb(55,65,81) !important; color: #e5e7eb !important; }
        .text-muted { color: #9ca3af !important; }
        hr { border-color: rgb(55,65,81) !important; opacity: 1; }
        .btn-outline-primary { color: #60a5fa; border-color: #60a5fa; }
        .btn-outline-primary:hover { background-color: #60a5fa; color: #111827; }
        a.card:hover { border-color: #4f8ef7 !important; transition: border-color 0.2s; }
    </style>
</head>
<body>
    <div style="min-height: 100vh; background-color: rgb(17,24,39);">
        @include('layouts.navigation')

        @isset($header)
        <div class="page-header">
            {{ $header }}
        </div>
        @endisset

        <main>
            {{ $slot }}
        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>