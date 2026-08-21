<div class="card p-6" x-data="{ showForm: false, channel: 'sms', content: '' }">
    <div class="flex items-center justify-between mb-3">
        <div class="eyebrow">Communication Center</div>
        <button @click="showForm = !showForm" class="btn btn-primary btn-sm">
            <span x-text="showForm ? 'Cancel' : 'Send/Log Communication'"></span>
        </button>
    </div>

    <div x-show="showForm" x-transition class="mb-6 p-4 bg-surface rounded-lg border border-hairline">
        <form x-ref="commForm" @submit.prevent="
            fetch('{{ route('visitors.communications.store', $visitor->vin) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    channel: channel,
                    content: content
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.data) {
                    window.location.reload();
                }
            })
        ">
            <div class="mb-4">
                <label class="block text-sm font-medium text-ink-700 mb-2">Channel *</label>
                <div class="flex flex-wrap gap-2">
                    <button type="button" @click="channel = 'sms'" :class="channel === 'sms' ? 'btn-primary' : 'btn-ghost'" class="btn btn-sm">SMS</button>
                    <button type="button" @click="channel = 'email'" :class="channel === 'email' ? 'btn-primary' : 'btn-ghost'" class="btn btn-sm">Email</button>
                    <button type="button" @click="channel = 'notice'" :class="channel === 'notice' ? 'btn-primary' : 'btn-ghost'" class="btn btn-sm">Notice</button>
                    <button type="button" @click="channel = 'call'" :class="channel === 'call' ? 'btn-primary' : 'btn-ghost'" class="btn btn-sm">Call</button>
                    <button type="button" @click="channel = 'meeting'" :class="channel === 'meeting' ? 'btn-primary' : 'btn-ghost'" class="btn btn-sm">Meeting</button>
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-ink-700 mb-1">Content</label>
                <textarea x-model="content" rows="4" placeholder="Message content or notes..." class="w-full rounded-lg border-hairline bg-raised px-3 py-2 text-sm"></textarea>
            </div>

            <button type="submit" class="btn btn-primary">
                <span x-text="channel === 'call' || channel === 'meeting' ? 'Log' : 'Send'"></span>
            </button>
        </form>
    </div>

    @if($communications->isEmpty())
        <div class="text-center py-8">
            <p class="text-ink-400 mb-2">No communications yet.</p>
            <p class="text-ink-400 text-sm">Send the first message to start tracking outreach.</p>
        </div>
    @else
        <div class="space-y-4">
            @foreach($communications as $communication)
                <div class="p-4 bg-raised rounded-lg border border-hairline">
                    <div class="flex items-start justify-between">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="badge badge-{{ $communication->isSystemGenerated() ? 'info' : 'success' }}">
                                    {{ strtoupper($communication->channel) }}
                                </span>
                                <span class="text-xs text-ink-400">{{ $communication->sent_at->format('M d, Y g:i A') }}</span>
                            </div>
                            @if($communication->content)
                                <p class="text-sm text-ink-600 mt-2">{{ $communication->content }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
