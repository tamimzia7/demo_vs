<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name'))</title>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="bg-surface min-h-screen">
    <div class="flex min-h-screen">
        @include('components.sidebar')
        <main class="flex-1 ml-64">
            <header class="h-16 border-b border-hairline bg-raised flex items-center px-6">
                <h1 class="text-lg font-semibold text-ink-900">@yield('header', config('app.name'))</h1>
            </header>
            <div class="p-6">
                @yield('content')
            </div>
        </main>
    </div>
</body>
</html>
