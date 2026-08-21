@extends('layouts.app')

@section('title', 'Knowledge Center - ' . config('app.name'))
@section('header', 'Knowledge Center')

@section('content')
<div class="space-y-6">
    <div class="card p-6">
        <div class="flex items-center justify-between mb-4">
            <div class="eyebrow">Knowledge Items</div>
            <button onclick="document.getElementById('createModal').classList.remove('hidden')" class="btn btn-primary btn-sm">
                Create Item
            </button>
        </div>

        @if($items->isEmpty())
            <div class="text-center py-8">
                <p class="text-ink-400 mb-2">No knowledge items yet.</p>
                <p class="text-ink-400 text-sm">Create the first item to start sharing knowledge.</p>
            </div>
        @else
            <div class="space-y-4">
                @foreach($items as $item)
                    <div class="p-4 bg-raised rounded-lg border border-hairline">
                        <div class="flex items-start justify-between">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="font-mono text-sm bg-accent-50 text-accent-700 px-2 py-0.5 rounded">
                                        {{ $item->knw }}
                                    </span>
                                    <span class="font-medium text-ink-900">{{ $item->title }}</span>
                                    <span class="text-xs text-ink-400">v{{ $item->version }}</span>
                                </div>
                                @if($item->description)
                                    <p class="text-sm text-ink-600 mt-1">{{ $item->description }}</p>
                                @endif
                                <div class="mt-2">
                                    <a href="{{ $item->link }}" target="_blank" class="text-sm text-accent-600 hover:text-accent-700">
                                        {{ Str::limit($item->link, 50) }}
                                    </a>
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <span class="badge badge-info">{{ $item->activeSharings->count() }} shared</span>
                            </div>
                        </div>

                        @if($item->sharings->count())
                            <div class="mt-3 pt-3 border-t border-hairline">
                                <div class="text-xs text-ink-400 mb-2">Sharing History</div>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($item->sharings as $sharing)
                                        <div class="flex items-center gap-1">
                                            <span class="badge badge-{{ $sharing->isGranted() ? 'success' : 'default' }}">
                                                {{ $sharing->visitor_vin }}
                                            </span>
                                            @if($sharing->isGranted())
                                                <form method="POST" action="{{ route('knowledge-items.revoke', ['itemId' => $item->id, 'vin' => $sharing->visitor_vin]) }}" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-xs text-danger-600 hover:text-danger-700">
                                                        Revoke
                                                    </button>
                                                </form>
                                            @else
                                                <span class="text-xs text-ink-400">Revoked</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <div id="createModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-raised rounded-lg p-6 w-full max-w-md mx-4">
            <h3 class="text-lg font-semibold text-ink-900 mb-4">Create Knowledge Item</h3>
            <form x-data="{ title: '', description: '', link: '' }" @submit.prevent="
                fetch('{{ route('knowledge-items.store') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        title: title,
                        description: description,
                        link: link
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.data) {
                        window.location.reload();
                    }
                })
            ">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-ink-700 mb-1">Title *</label>
                        <input type="text" x-model="title" required class="w-full rounded-lg border-hairline bg-surface px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-ink-700 mb-1">Description</label>
                        <textarea x-model="description" rows="3" class="w-full rounded-lg border-hairline bg-surface px-3 py-2 text-sm"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-ink-700 mb-1">Link *</label>
                        <input type="url" x-model="link" required placeholder="https://" class="w-full rounded-lg border-hairline bg-surface px-3 py-2 text-sm">
                    </div>
                </div>
                <div class="flex justify-end gap-2 mt-6">
                    <button type="button" onclick="document.getElementById('createModal').classList.add('hidden')" class="btn btn-ghost">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        Create
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
