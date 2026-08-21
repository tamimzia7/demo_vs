@extends('layouts.app')

@section('title', 'Administration - ' . config('app.name'))
@section('header', 'Administration')

@section('content')
<div class="space-y-6">
    <div class="card p-6">
        <div class="eyebrow mb-3">Administration</div>
        <h2 class="text-lg font-semibold text-ink-900 mb-2">Platform Administration</h2>
        <p class="text-ink-600">Manage users, roles, and system tags for your organization.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <a href="{{ route('admin.users.index') }}" class="card p-6 hover:shadow-lg transition-shadow">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-accent-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-accent-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-ink-900">User Management</h3>
                    <p class="text-sm text-ink-600">Manage marketers and their roles</p>
                </div>
            </div>
        </a>

        <a href="{{ route('admin.system-tags.index') }}" class="card p-6 hover:shadow-lg transition-shadow">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-accent-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-accent-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-ink-900">System Tags</h3>
                    <p class="text-sm text-ink-600">Manage platform classification tags</p>
                </div>
            </div>
        </a>
    </div>

    <div class="card p-6">
        <div class="eyebrow mb-3">Access Control</div>
        <p class="text-ink-600 text-sm">
            <strong>V1 Access Model (BDR-020):</strong> Super Admin (platform) + Company Owner/Marketer (company).
            Team roles (Manager, Sales Executive, Marketing Officer) activate in future editions.
        </p>
    </div>
</div>
@endsection
