@props(['visitor', 'relationship', 'marketers'])

<div class="card p-6">
    <div class="eyebrow mb-3">Relationship Center</div>

    @if($relationship && $relationship->status !== 'unassigned')
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm text-ink-400">Current Owner</div>
                    <div class="font-medium text-ink-900">{{ $relationship->marketer->name ?? 'Unknown' }}</div>
                </div>
                <span class="badge badge-{{ $relationship->status === 'assigned' ? 'success' : ($relationship->status === 'transfer_requested' ? 'warning' : 'info') }}">
                    {{ str_replace('_', ' ', ucfirst($relationship->status)) }}
                </span>
            </div>

            @if($relationship->status === 'assigned')
                <form method="POST" action="{{ route('visitors.relationships.transfer', $visitor->vin) }}">
                    @csrf
                    <div class="space-y-3">
                        <div>
                            <label for="target_marketer_id" class="label">Transfer to</label>
                            <select name="target_marketer_id" id="target_marketer_id" class="select w-full">
                                <option value="">Select marketer...</option>
                                @foreach($marketers as $marketer)
                                    @if($marketer->id !== $relationship->marketer_id)
                                        <option value="{{ $marketer->id }}">{{ $marketer->name }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-ghost btn-sm" onclick="return confirm('Transfer will preserve all history. Continue?')">
                            Request Transfer
                        </button>
                    </div>
                </form>
            @endif

            @if($relationship->status === 'transfer_requested')
                <div class="text-sm text-ink-600">
                    <p>Transfer pending approval from Company Owner.</p>
                    <div class="flex gap-2 mt-3">
                        <form method="POST" action="{{ route('visitors.relationships.approve', $visitor->vin) }}">
                            @csrf
                            <button type="submit" class="btn btn-primary btn-sm">
                                Approve Transfer
                            </button>
                        </form>
                        <form method="POST" action="{{ route('visitors.relationships.reject', $visitor->vin) }}">
                            @csrf
                            <button type="submit" class="btn btn-ghost btn-sm">
                                Reject
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    @else
        <div class="text-center py-4">
            <p class="text-ink-400 mb-3">No relationship assigned.</p>
            <form method="POST" action="{{ route('visitors.relationships.store', $visitor->vin) }}">
                @csrf
                <div class="space-y-3">
                    <div>
                        <label for="marketer_id" class="label">Assign to</label>
                        <select name="marketer_id" id="marketer_id" class="select w-full">
                            <option value="">Select marketer...</option>
                            @foreach($marketers as $marketer)
                                <option value="{{ $marketer->id }}">{{ $marketer->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm">
                        Assign Relationship
                    </button>
                </div>
            </form>
        </div>
    @endif
</div>
