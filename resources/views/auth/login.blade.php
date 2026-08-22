<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign in · {{ config('app.name') }}</title>
    @fonts
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="bg-surface min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full mx-auto p-6">
        <div class="card p-8">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 bg-accent-600 rounded-xl flex items-center justify-center">
                    <span class="text-white font-bold">V</span>
                </div>
                <div>
                    <h1 class="font-semibold text-ink-900 leading-tight">Sign in to VisiCore</h1>
                    <p class="text-xs text-ink-400">Customer Journey Intelligence Platform</p>
                </div>
            </div>

            @if ($errors->any())
                <div class="alert-danger mb-4" role="alert">
                    <span class="font-medium">These credentials do not match our records.</span>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" novalidate>
                @csrf

                <div class="mb-4">
                    <label for="email" class="label">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}"
                           class="field-input w-full" required autofocus autocomplete="username">
                    @error('email')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="password" class="label">Password</label>
                    <input id="password" type="password" name="password"
                           class="field-input w-full" required autocomplete="current-password">
                    @error('password')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-between mb-6">
                    <label class="inline-flex items-center gap-2 text-sm text-ink-600">
                        <input type="checkbox" name="remember" class="rounded border-hairline">
                        Remember me
                    </label>
                </div>

                <button type="submit" class="btn btn-primary w-full">Sign in</button>
            </form>
        </div>
    </div>
</body>
</html>
