<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }}</title>
    @fonts
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="bg-surface min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full mx-auto p-6">
        <div class="card p-8 text-center">
            <div class="w-16 h-16 bg-accent-600 rounded-2xl flex items-center justify-center mx-auto mb-6">
                <span class="text-white font-bold text-2xl">V</span>
            </div>
            <h1 class="text-2xl font-bold text-ink-900 mb-2">{{ config('app.name') }}</h1>
            <p class="text-ink-600 mb-1">Customer Journey Intelligence Platform</p>
            <p class="text-ink-400 text-sm mb-8">Every Visitor. Every Journey. One Smart Platform.</p>
            <a href="{{ route('dashboard') }}" class="btn btn-primary">
                Enter Application
            </a>
            <p class="text-ink-300 text-xs mt-6">Foundation Prototype v0.1.0</p>
        </div>
    </div>
</body>
</html>
