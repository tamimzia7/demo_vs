<div class="card p-6" x-data="{ showForm: false, offeringId: '', amount: '', purchasedAt: '{{ now()->format('Y-m-d') }}' }">
    <div class="flex items-center justify-between mb-3">
        <div class="eyebrow">Purchase Management</div>
        <button @click="showForm = !showForm" class="btn btn-primary btn-sm">
            <span x-text="showForm ? 'Cancel' : 'Record Purchase'"></span>
        </button>
    </div>

    <div x-show="showForm" x-transition class="mb-6 p-4 bg-surface rounded-lg border border-hairline">
        <form x-ref="purchaseForm" @submit.prevent="
            fetch('{{ route('visitors.purchases.store', $visitor->vin) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    offering_id: offeringId || null,
                    amount: amount || null,
                    purchased_at: purchasedAt
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
                <label class="block text-sm font-medium text-ink-700 mb-1">Offering ID (optional)</label>
                <input type="number" x-model="offeringId" placeholder="Enter offering ID" class="w-full rounded-lg border-hairline bg-raised px-3 py-2 text-sm">
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-ink-700 mb-1">Amount (optional)</label>
                <input type="number" step="0.01" x-model="amount" placeholder="0.00" class="w-full rounded-lg border-hairline bg-raised px-3 py-2 text-sm">
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-ink-700 mb-1">Purchase Date *</label>
                <input type="date" x-model="purchasedAt" required class="w-full rounded-lg border-hairline bg-raised px-3 py-2 text-sm">
            </div>

            <button type="submit" class="btn btn-primary">
                Record Purchase
            </button>
        </form>
    </div>

    @if($purchases->isEmpty())
        <div class="text-center py-8">
            <p class="text-ink-400 mb-2">No purchases yet.</p>
            <p class="text-ink-400 text-sm">Record the first purchase to start tracking conversions.</p>
        </div>
    @else
        <div class="space-y-4">
            @foreach($purchases as $purchase)
                <div class="p-4 bg-raised rounded-lg border border-hairline">
                    <div class="flex items-start justify-between">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="badge badge-success">Purchase</span>
                                <span class="text-xs text-ink-400">{{ $purchase->purchased_at->format('M d, Y') }}</span>
                            </div>
                            @if($purchase->offering)
                                <p class="text-sm text-ink-600 mt-2">Offering: {{ $purchase->offering->name }}</p>
                            @endif
                            @if($purchase->amount)
                                <p class="text-sm text-ink-600 mt-1">Amount: ${{ number_format($purchase->amount, 2) }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
