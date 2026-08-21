<div class="card p-6" x-data="{ showForm: false, visitDate: '', visitContext: '', visitOutcome: '', participants: [], newParticipant: '' }">
    <div class="flex items-center justify-between mb-3">
        <div class="eyebrow">Visit Management</div>
        <button @click="showForm = !showForm" class="btn btn-primary btn-sm">
            <span x-text="showForm ? 'Cancel' : 'Log Visit'"></span>
        </button>
    </div>

    <div x-show="showForm" x-transition class="mb-6 p-4 bg-surface rounded-lg border border-hairline">
        <form x-ref="visitForm" @submit.prevent="
            fetch('{{ route('visitors.visits.store', $visitor->vin) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    visit_date: visitDate,
                    context: visitContext,
                    outcome: visitOutcome,
                    participants: participants
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.data) {
                    window.location.reload();
                }
            })
        ">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-ink-700 mb-1">Visit Date *</label>
                    <input type="date" x-model="visitDate" required class="w-full rounded-lg border-hairline bg-raised px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-ink-700 mb-1">Context</label>
                    <input type="text" x-model="visitContext" placeholder="e.g., Office meeting, Phone call" class="w-full rounded-lg border-hairline bg-raised px-3 py-2 text-sm">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-ink-700 mb-1">Outcome</label>
                    <input type="text" x-model="visitOutcome" placeholder="e.g., Discussed pricing, Follow-up scheduled" class="w-full rounded-lg border-hairline bg-raised px-3 py-2 text-sm">
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-ink-700 mb-1">Participants</label>
                <div class="flex gap-2">
                    <input type="text" x-model="newParticipant" placeholder="Participant name" class="flex-1 rounded-lg border-hairline bg-raised px-3 py-2 text-sm">
                    <button type="button" @click="if (newParticipant.trim()) { participants.push(newParticipant.trim()); newParticipant = ''; }" class="btn btn-ghost btn-sm">
                        Add
                    </button>
                </div>
                <div class="flex flex-wrap gap-2 mt-2">
                    <template x-for="(p, index) in participants" :key="index">
                        <span class="badge badge-info flex items-center gap-1">
                            <span x-text="p"></span>
                            <button type="button" @click="participants.splice(index, 1)" class="text-ink-400 hover:text-ink-600">&times;</button>
                        </span>
                    </template>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">
                Log Visit
            </button>
        </form>
    </div>

    @if($visits->isEmpty())
        <div class="text-center py-8">
            <p class="text-ink-400 mb-2">No visits yet.</p>
            <p class="text-ink-400 text-sm">Log the first visit to start tracking engagement.</p>
        </div>
    @else
        <div class="space-y-4">
            @foreach($visits as $visit)
                <div class="p-4 bg-raised rounded-lg border border-hairline">
                    <div class="flex items-start justify-between">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="font-medium text-ink-900">{{ $visit->visit_date->format('M d, Y') }}</span>
                                @if($visit->context)
                                    <span class="badge badge-info">{{ $visit->context }}</span>
                                @endif
                            </div>
                            @if($visit->outcome)
                                <p class="text-sm text-ink-600 mt-1">{{ $visit->outcome }}</p>
                            @endif
                        </div>
                    </div>

                    @if($visit->participants->count())
                        <div class="mt-3 pt-3 border-t border-hairline">
                            <div class="text-xs text-ink-400 mb-2">Participants</div>
                            <div class="flex flex-wrap gap-2">
                                @foreach($visit->participants as $participant)
                                    <div class="flex items-center gap-1">
                                        <span class="badge badge-default">{{ $participant->name }}</span>
                                        @if($participant->promoted_to_vin)
                                            <span class="text-xs text-success-600">Promoted</span>
                                        @else
                                            <form method="POST" action="{{ route('participants.promote', $participant->id) }}" class="inline">
                                                @csrf
                                                <button type="submit" class="text-xs text-accent-600 hover:text-accent-700" onclick="return confirm('Promote this participant to a Visitor?')">
                                                    Promote
                                                </button>
                                            </form>
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
