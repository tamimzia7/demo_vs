@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-ink-900">{{ $offering ? 'Edit Offering' : 'Add Offering' }}</h1>
        <a href="{{ route('offerings.index') }}" class="text-accent-600 hover:text-accent-700 text-sm mt-1 inline-block">
            Back to Catalog
        </a>
    </div>

    @if($errors->any())
        <div class="alert-danger">
            <ul class="list-disc pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ $offering ? route('offerings.update', $offering->off) : route('offerings.store') }}"
          method="{{ $offering ? 'PUT' : 'POST' }}" class="card p-6 space-y-4">
        @csrf
        @if($offering)
            @method('PUT')
        @endif

        <div>
            <label class="label" for="name">Name</label>
            <input class="field-input w-full"
                   id="name" type="text" name="name"
                   value="{{ old('name', $offering ? $offering->name : '') }}"
                   required>
        </div>

        <div>
            <label class="label" for="metadata">Metadata (JSON)</label>
            <textarea class="textarea w-full"
                      id="metadata" name="metadata"
                      rows="4">{{ old('metadata', $offering && $offering->metadata ? json_encode($offering->metadata, JSON_PRETTY_PRINT) : '') }}</textarea>
            <p class="hint">Enter valid JSON or leave empty</p>
        </div>

        <div>
            <label class="label" for="active">Status</label>
            <div class="flex items-center gap-2">
                <input class="switch"
                       id="active" type="checkbox" name="active" value="1"
                       {{ old('active', $offering ? $offering->active : true) ? 'checked' : '' }}>
                <span class="text-ink-700 text-sm">Active</span>
            </div>
        </div>

        <div class="flex items-center justify-between pt-4 border-t border-hairline">
            <button type="submit" class="btn btn-primary">
                {{ $offering ? 'Update Offering' : 'Add Offering' }}
            </button>
            @if($offering)
                <a href="{{ route('offerings.index') }}" class="btn btn-ghost">
                    Cancel
                </a>
            @endif
        </div>
    </form>
</div>
@endsection
