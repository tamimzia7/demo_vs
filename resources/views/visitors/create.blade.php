@extends('layouts.app')

@section('title', 'Add Visitor - ' . config('app.name'))
@section('header', 'Add Visitor')

@section('content')
<div class="max-w-2xl">
    <form method="POST" action="{{ route('visitors.store') }}">
        @csrf

        <div class="card p-6 space-y-6">
            <div class="eyebrow mb-3">Visitor Information</div>

            <div>
                <label for="name" class="label">Name</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" class="input" required>
                @error('name')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="channel" class="label">Channel (optional)</label>
                <input type="text" name="channel" id="channel" value="{{ old('channel') }}" class="input" placeholder="e.g., Website, Call, Referral">
                @error('channel')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="referrer_vin" class="label">Referrer VIN (optional)</label>
                <input type="text" name="referrer_vin" id="referrer_vin" value="{{ old('referrer_vin') }}" class="input" placeholder="VC-YYYY-NNNNNN">
                @error('referrer_vin')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="p-4 bg-surface rounded-lg">
                <p class="text-sm text-ink-600">
                    <strong>Note:</strong> A VIN (Visitor Identity Number) will be automatically assigned in the format VC-YYYY-NNNNNN (BDR-018).
                </p>
            </div>
        </div>

        <div class="flex justify-end gap-3 mt-6">
            <a href="{{ route('visitors.index') }}" class="btn btn-ghost">
                Cancel
            </a>
            <button type="submit" class="btn btn-primary">
                Create Visitor
            </button>
        </div>
    </form>
</div>
@endsection
