@extends('layouts.app')

@section('title', 'Users - ' . config('app.name'))
@section('header', 'User Management')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-lg font-semibold text-ink-900">Users</h2>
            <p class="text-ink-600 text-sm">Manage marketers and their access levels</p>
        </div>
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
            Add User
        </a>
    </div>

    <div class="card">
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Created</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td class="font-medium text-ink-900">{{ $user->name }}</td>
                            <td class="text-ink-600">{{ $user->email }}</td>
                            <td>
                                <span class="badge badge-{{ $user->role === 'super_admin' ? 'info' : ($user->role === 'company_owner' ? 'success' : 'default') }}">
                                    {{ str($user->role)->replace('_', ' ')->title() }}
                                </span>
                            </td>
                            <td class="text-ink-600">{{ $user->created_at->format('M d, Y') }}</td>
                            <td class="text-right">
                                <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-ghost btn-sm">
                                    Edit
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-8">
                                <p class="text-ink-400">No users yet.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
