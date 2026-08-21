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

        @if($timelineEvents->isEmpty())
            <div class="text-center py-8">
                <p class="text-ink-400 mb-2">No activity yet.</p>
                <p class="text-ink-400 text-sm">Timeline events will appear here as interactions are recorded.</p>
            </div>
        @else
            <div class="space-y-4">
                @foreach($timelineEvents as $event)
                    <div class="flex gap-4 p-4 rounded-lg {{ $event->isSystemGenerated() ? 'bg-surface' : 'bg-raised' }}">
                        <div class="flex-shrink-0">
                            @if($event->isSystemGenerated())
                                <div class="w-8 h-8 rounded-full bg-accent-100 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-accent-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                    </svg>
                                </div>
                            @else
                                <div class="w-8 h-8 rounded-full bg-success-100 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                </div>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="font-medium text-ink-900">{{ $event->source }}</span>
                                <span class="badge badge-{{ $event->isSystemGenerated() ? 'info' : 'success' }}" style="font-size: 0.65rem;">
                                    {{ $event->isSystemGenerated() ? 'System' : 'User' }}
                                </span>
                            </div>
                            <p class="text-sm text-ink-600 mt-1">{{ $event->summary }}</p>
                            <p class="text-xs text-ink-400 mt-2">{{ $event->created_at->format('M d, Y g:i A') }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
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
