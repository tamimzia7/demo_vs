@extends('layouts.app')

@section('title', 'Edit Visitor - ' . config('app.name'))
@section('header', 'Edit Visitor')

@section('content')
<div class="max-w-2xl">
    <form method="POST" action="{{ route('visitors.update', $visitor->vin) }}">
        @csrf
        @method('PUT')

        <div class="card p-6 space-y-6">
            <div class="eyebrow mb-3">Visitor Information</div>

            <div>
                <label for="name" class="label">Name</label>
                <input type="text" name="name" id="name" value="{{ old('name', $visitor->name) }}" class="input" required>
                @error('name')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="channel" class="label">Channel (optional)</label>
                <input type="text" name="channel" id="channel" value="{{ old('channel', $visitor->channel) }}" class="input">
                @error('channel')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="p-4 bg-surface rounded-lg">
                <p class="text-sm text-ink-600">
                    <strong>VIN:</strong>
                    <span class="font-mono">{{ $visitor->vin }}</span>
                    <span class="text-ink-400 ml-2">(cannot be changed - BDR-018)</span>
                </p>
            </div>
        </div>

        <div class="flex justify-end gap-3 mt-6">
            <a href="{{ route('visitors.workspace', $visitor->vin) }}" class="btn btn-ghost">
                Cancel
            </a>
            <button type="submit" class="btn btn-primary">
                Update Visitor
            </button>
        </div>
    </form>
</div>
@endsection
