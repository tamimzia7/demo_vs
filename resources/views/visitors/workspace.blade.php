@extends('layouts.app')

@section('title', $visitor->name . ' - ' . config('app.name'))
@section('header', $visitor->name)

@section('content')
<div class="space-y-6">
    <div class="card p-6">
        <div class="flex items-start justify-between">
            <div class="space-y-4">
                <div>
                    <h2 class="text-2xl font-bold text-ink-900">{{ $visitor->name }}</h2>
                    <div class="flex items-center gap-3 mt-2">
                        <span class="font-mono text-sm bg-accent-50 text-accent-700 px-3 py-1 rounded-lg">
                            {{ $visitor->vin }}
                        </span>
                        <span class="badge badge-{{ $visitor->lifecycle_state === 'Archived' ? 'default' : ($visitor->lifecycle_state === 'VIP' ? 'success' : 'info') }}">
                            {{ $visitor->lifecycle_state }}
                        </span>
                    </div>
                </div>

                <div class="flex gap-4 text-sm text-ink-600">
                    @if($visitor->channel)
                        <div>
                            <span class="text-ink-400">Channel:</span>
                            {{ $visitor->channel }}
                        </div>
                    @endif
                    <div>
                        <span class="text-ink-400">Created:</span>
                        {{ $visitor->created_at->format('M d, Y') }}
                    </div>
                </div>
            </div>

            <div class="flex gap-2">
                <a href="{{ route('visitors.edit', $visitor->vin) }}" class="btn btn-ghost btn-sm">
                    Edit
                </a>
                @if($visitor->isArchived())
                    <form method="POST" action="{{ route('visitors.restore', $visitor->vin) }}">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-sm">
                            Restore
                        </button>
                    </form>
                @else
                    <form method="POST" action="{{ route('visitors.archive', $visitor->vin) }}">
                        @csrf
                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to archive this visitor?')">
                            Archive
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <div class="card p-6">
        <div class="eyebrow mb-3">Timeline</div>
        <div class="text-center py-8">
            <p class="text-ink-400 mb-2">No activity yet.</p>
            <p class="text-ink-400 text-sm">Timeline functionality (MOD-002) will be implemented in a separate feature.</p>
        </div>
    </div>

    @include('relationships._panel', ['visitor' => $visitor, 'relationship' => $relationship, 'marketers' => $marketers])

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="card p-4 text-center">
            <div class="text-2xl font-bold text-ink-900">0</div>
            <div class="text-sm text-ink-600">Timeline Events</div>
        </div>
        <div class="card p-4 text-center">
            <div class="text-2xl font-bold text-ink-900">0</div>
            <div class="text-sm text-ink-600">Relationships</div>
        </div>
        <div class="card p-4 text-center">
            <div class="text-2xl font-bold text-ink-900">0</div>
            <div class="text-sm text-ink-600">Purchases</div>
        </div>
        <div class="card p-4 text-center">
            <div class="text-2xl font-bold text-ink-900">0</div>
            <div class="text-sm text-ink-600">Visits</div>
        </div>
    </div>
</div>
@endsection
