@extends('layouts.app')

@section('title', 'Visitors - ' . config('app.name'))
@section('header', 'Visitors')

@section('content')
<div class="space-y-6">
    <div class="card p-6">
        <div class="eyebrow mb-3">Prototype Area</div>
        <h2 class="text-lg font-semibold text-ink-900 mb-2">Visitor Management</h2>
        <p class="text-ink-600">The Visitor Workspace is the primary workspace for managing visitor relationships and journeys.</p>
        <p class="text-ink-400 text-sm mt-2">Visitor functionality (MOD-001) will be implemented in a separate feature.</p>
    </div>

    <div class="card p-6">
        <div class="eyebrow mb-3">Core Concept</div>
        <p class="text-ink-600">Every Visitor. Every Journey. One Smart Platform.</p>
        <p class="text-ink-400 text-sm mt-2">The visitor is the center of the platform. Projects and offerings organize visitors; relationships belong to marketers; but the visitor — identity, history, journey — is permanent, central, and never deleted.</p>
    </div>
</div>
@endsection
