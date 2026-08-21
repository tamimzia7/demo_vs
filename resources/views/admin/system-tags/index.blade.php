@extends('layouts.app')

@section('title', 'System Tags - ' . config('app.name'))
@section('header', 'System Tags')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-lg font-semibold text-ink-900">System Tags</h2>
            <p class="text-ink-600 text-sm">Platform classification tags (immutable - cannot be deleted)</p>
        </div>
        <a href="{{ route('admin.system-tags.create') }}" class="btn btn-primary">
            Add System Tag
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->has('error'))
        <div class="alert alert-danger">
            {{ $errors->first('error') }}
        </div>
    @endif

    <div class="card">
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Slug</th>
                        <th>Color</th>
                        <th>Description</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($systemTags as $tag)
                        <tr>
                            <td class="font-medium text-ink-900">{{ $tag->name }}</td>
                            <td class="text-ink-600">{{ $tag->slug }}</td>
                            <td>
                                @if($tag->color)
                                    <div class="flex items-center gap-2">
                                        <div class="w-4 h-4 rounded" style="background-color: {{ $tag->color }}"></div>
                                        <span class="text-ink-600">{{ $tag->color }}</span>
                                    </div>
                                @else
                                    <span class="text-ink-400">-</span>
                                @endif
                            </td>
                            <td class="text-ink-600">{{ $tag->description ?: '-' }}</td>
                            <td class="text-right">
                                <span class="text-ink-400 text-sm">Immutable (BDR-015)</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-8">
                                <p class="text-ink-400">No system tags yet.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
