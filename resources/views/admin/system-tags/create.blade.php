@extends('layouts.app')

@section('title', 'Add System Tag - ' . config('app.name'))
@section('header', 'Add System Tag')

@section('content')
<div class="max-w-2xl">
    <form method="POST" action="{{ route('admin.system-tags.store') }}">
        @csrf

        <div class="card p-6 space-y-6">
            <div class="eyebrow mb-3">System Tag Information</div>

            <div>
                <label for="name" class="label">Name</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" class="input" required>
                @error('name')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="color" class="label">Color (optional)</label>
                <input type="color" name="color" id="color" value="{{ old('color', '#6366f1') }}" class="input h-10">
                @error('color')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="description" class="label">Description (optional)</label>
                <textarea name="description" id="description" rows="3" class="textarea">{{ old('description') }}</textarea>
                @error('description')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="p-4 bg-surface rounded-lg">
                <p class="text-sm text-ink-600">
                    <strong>Note:</strong> System tags are immutable and cannot be deleted once created (BDR-015, REQ-020).
                </p>
            </div>
        </div>

        <div class="flex justify-end gap-3 mt-6">
            <a href="{{ route('admin.system-tags.index') }}" class="btn btn-ghost">
                Cancel
            </a>
            <button type="submit" class="btn btn-primary">
                Create System Tag
            </button>
        </div>
    </form>
</div>
@endsection
