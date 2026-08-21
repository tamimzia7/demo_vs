@extends('layouts.app')

@section('title', 'Edit User - ' . config('app.name'))
@section('header', 'Edit User')

@section('content')
<div class="max-w-2xl">
    <form method="POST" action="{{ route('admin.users.update', $user->id) }}">
        @csrf
        @method('PUT')

        <div class="card p-6 space-y-6">
            <div class="eyebrow mb-3">User Information</div>

            <div>
                <label for="name" class="label">Name</label>
                <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" class="input" required>
                @error('name')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email" class="label">Email</label>
                <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" class="input" required>
                @error('email')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="role" class="label">Role</label>
                <select name="role" id="role" class="select" required>
                    <option value="super_admin" {{ old('role', $user->role) === 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                    <option value="company_owner" {{ old('role', $user->role) === 'company_owner' ? 'selected' : '' }}>Company Owner</option>
                    <option value="sales_executive" {{ old('role', $user->role) === 'sales_executive' ? 'selected' : '' }}>Sales Executive</option>
                    <option value="marketing_officer" {{ old('role', $user->role) === 'marketing_officer' ? 'selected' : '' }}>Marketing Officer</option>
                </select>
                @error('role')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="flex justify-end gap-3 mt-6">
            <a href="{{ route('admin.users.index') }}" class="btn btn-ghost">
                Cancel
            </a>
            <button type="submit" class="btn btn-primary">
                Update User
            </button>
        </div>
    </form>
</div>
@endsection
