@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-ink-900">Offerings Catalog</h1>
            <p class="text-ink-600 text-sm mt-1">Manage your offerings and products</p>
        </div>
        <a href="{{ route('offerings.create') }}" class="btn btn-primary">
            Add Offering
        </a>
    </div>

    @if(session('success'))
        <div class="alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if($offerings->isEmpty())
        <div class="card p-12 text-center">
            <p class="text-ink-600 mb-4">No offerings defined yet.</p>
            <a href="{{ route('offerings.create') }}" class="btn btn-primary">
                Add an offering
            </a>
        </div>
    @else
        <div class="card overflow-hidden">
            <table class="table w-full">
                <thead>
                    <tr>
                        <th class="text-left">OFF</th>
                        <th class="text-left">Name</th>
                        <th class="text-left">Metadata</th>
                        <th class="text-left">Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($offerings as $offering)
                        <tr>
                            <td class="font-medium text-ink-900">{{ $offering->off }}</td>
                            <td class="text-ink-700">{{ $offering->name }}</td>
                            <td class="text-ink-600 text-sm">
                                @if($offering->metadata)
                                    <pre class="text-xs">{{ json_encode($offering->metadata, JSON_PRETTY_PRINT) }}</pre>
                                @else
                                    <span class="text-ink-400">-</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $offering->active ? 'badge-success' : 'badge-danger' }}">
                                    {{ $offering->active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="text-right">
                                <a href="{{ route('offerings.edit', $offering->off) }}"
                                   class="btn btn-ghost btn-sm">
                                    Edit
                                </a>
                                <form action="{{ route('offerings.destroy', $offering->off) }}" method="POST"
                                      class="inline"
                                      onsubmit="return confirm('Are you sure you want to delete this offering?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-ghost btn-sm text-danger">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
