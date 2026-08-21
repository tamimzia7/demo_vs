<div class="card p-6" x-data="{ showShareForm: false, shareVin: '' }">
    <div class="flex items-center justify-between mb-3">
        <div class="eyebrow">Knowledge Center</div>
    </div>

    @if($knowledgeItems->isEmpty())
        <div class="text-center py-8">
            <p class="text-ink-400 mb-2">No knowledge items shared with this visitor.</p>
            <p class="text-ink-400 text-sm">Share knowledge items from the Knowledge Center.</p>
        </div>
    @else
        <div class="space-y-4">
            @foreach($knowledgeItems as $item)
                <div class="p-4 bg-raised rounded-lg border border-hairline">
                    <div class="flex items-start justify-between">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="font-mono text-sm bg-accent-50 text-accent-700 px-2 py-0.5 rounded">
                                    {{ $item->knw }}
                                </span>
                                <span class="font-medium text-ink-900">{{ $item->title }}</span>
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
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
