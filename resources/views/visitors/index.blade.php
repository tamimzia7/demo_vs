@extends('layouts.app')

@section('title', 'Visitors - ' . config('app.name'))
@section('header', 'Visitors')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-lg font-semibold text-ink-900">Visitors</h2>
            <p class="text-ink-600 text-sm">Manage your visitor relationships</p>
        </div>
        <a href="{{ route('visitors.create') }}" class="btn btn-primary">
            Add Visitor
        </a>
    </div>

    <form method="GET" action="{{ route('visitors.index') }}" class="card p-4">
        <div class="flex gap-3">
            <input type="text" name="search" value="{{ $search }}" placeholder="Search by name or VIN..." class="input flex-1">
            <button type="submit" class="btn btn-primary">Search</button>
            @if($search)
                <a href="{{ route('visitors.index') }}" class="btn btn-ghost">Clear</a>
            @endif
        </div>
    </form>

    <div class="card">
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>VIN</th>
                        <th>Name</th>
                        <th>Channel</th>
                        <th>Lifecycle</th>
                        <th>Created</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($visitors as $visitor)
                        <tr>
                            <td>
                                <span class="font-mono text-sm bg-accent-50 text-accent-700 px-2 py-1 rounded">
                                    {{ $visitor->vin }}
                                </span>
                            </td>
                            <td class="font-medium text-ink-900">{{ $visitor->name }}</td>
                            <td class="text-ink-600">{{ $visitor->channel ?: '-' }}</td>
                            <td>
                                <span class="badge badge-{{ $visitor->lifecycle_state === 'Archived' ? 'default' : ($visitor->lifecycle_state === 'VIP' ? 'success' : 'info') }}">
                                    {{ $visitor->lifecycle_state }}
                                </span>
                            </td>
                            <td class="text-ink-600">{{ $visitor->created_at->format('M d, Y') }}</td>
                            <td class="text-right">
                                <a href="{{ route('visitors.workspace', $visitor->vin) }}" class="btn btn-ghost btn-sm">
                                    Open
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-8">
                                <p class="text-ink-400 mb-2">No visitors match.</p>
                                <a href="{{ route('visitors.create') }}" class="btn btn-primary btn-sm">
                                    Add a visitor
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
