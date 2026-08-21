@extends('layouts.app')

@section('title', 'Dashboard - ' . config('app.name'))
@section('header', 'Dashboard')

@section('content')
<div class="space-y-6">
    <div class="card p-6">
        <h2 class="text-lg font-semibold text-ink-900 mb-2">Welcome to {{ config('app.name') }}</h2>
        <p class="text-ink-600">Customer Journey Intelligence Platform</p>
        <p class="text-ink-400 text-sm mt-2">Every Visitor. Every Journey. One Smart Platform.</p>
    </div>

    <div class="card p-6">
        <div class="eyebrow mb-3">Prototype Area</div>
        <p class="text-ink-600">This is the Dashboard navigation destination. Business functionality will be implemented when the Dashboard module is developed.</p>
    </div>
</div>
@endsection
