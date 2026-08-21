<div class="card p-6" x-data="{ showForm: false, category: '', amount: '', expenseDate: '{{ now()->format('Y-m-d') }}' }">
    <div class="flex items-center justify-between mb-3">
        <div class="eyebrow">Relationship Investment</div>
        <button @click="showForm = !showForm" class="btn btn-primary btn-sm">
            <span x-text="showForm ? 'Cancel' : 'Log Expense'"></span>
        </button>
    </div>

    <div x-show="showForm" x-transition class="mb-6 p-4 bg-surface rounded-lg border border-hairline">
        <form x-ref="expenseForm" @submit.prevent="
            fetch('{{ route('visitors.expenses.store', $visitor->vin) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    category: category,
                    amount: amount || null,
                    expense_date: expenseDate
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
                <label class="block text-sm font-medium text-ink-700 mb-1">Category *</label>
                <input type="text" x-model="category" required placeholder="e.g. Travel, Meeting, Gift" class="w-full rounded-lg border-hairline bg-raised px-3 py-2 text-sm">
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-ink-700 mb-1">Amount (optional)</label>
                <input type="number" step="0.01" x-model="amount" placeholder="0.00" class="w-full rounded-lg border-hairline bg-raised px-3 py-2 text-sm">
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-ink-700 mb-1">Expense Date *</label>
                <input type="date" x-model="expenseDate" required class="w-full rounded-lg border-hairline bg-raised px-3 py-2 text-sm">
            </div>

            <button type="submit" class="btn btn-primary">
                Log Expense
            </button>
        </form>
    </div>

    @if($expenses->isEmpty())
        <div class="text-center py-8">
            <p class="text-ink-400 mb-2">No investments logged yet.</p>
            <p class="text-ink-400 text-sm">Log an expense to start tracking relationship investments.</p>
        </div>
    @else
        <div class="space-y-4">
            @foreach($expenses as $expense)
                <div class="p-4 bg-raised rounded-lg border border-hairline">
                    <div class="flex items-start justify-between">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="badge badge-success">Expense</span>
                                <span class="text-xs text-ink-400">{{ $expense->expense_date->format('M d, Y') }}</span>
                            </div>
                            <p class="text-sm text-ink-600 mt-2">{{ $expense->category }}</p>
                            @if($expense->amount)
                                <p class="text-sm text-ink-600 mt-1">Amount: ${{ number_format($expense->amount, 2) }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
