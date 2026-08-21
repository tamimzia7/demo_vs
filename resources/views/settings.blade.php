@extends('layouts.app')

@section('title', 'Settings - ' . config('app.name'))
@section('header', 'Settings')

@section('content')
<div class="space-y-6">
    <div class="card p-6">
        <div class="eyebrow mb-3">Prototype Area</div>
        <h2 class="text-lg font-semibold text-ink-900 mb-2">Settings</h2>
        <p class="text-ink-600">Configure your preferences and customize how {{ config('app.name') }} works.</p>
        <p class="text-ink-400 text-sm mt-2">Settings (MOD-013) will be implemented in a separate feature.</p>
    </div>
</div>
@endsection
