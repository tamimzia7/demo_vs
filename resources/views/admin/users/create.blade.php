@extends('layouts.app')

@section('title', 'Add User - ' . config('app.name'))
@section('header', 'Add User')

@section('content')
<div class="max-w-2xl">
    <form method="POST" action="{{ route('admin.users.store') }}">
        @csrf

        <div class="card p-6 space-y-6">
            <div class="eyebrow mb-3">User Information</div>

            <div>
                <label for="name" class="label">Name</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" class="input" required>
                @error('name')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email" class="label">Email</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" class="input" required>
                @error('email')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="label">Password</label>
                <input type="password" name="password" id="password" class="input" required>
                @error('password')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password_confirmation" class="label">Confirm Password</label>
                <input type="password" name="password_confirmation" id="password_confirmation" class="input" required>
            </div>

            <div>
                <label for="role" class="label">Role</label>
                <select name="role" id="role" class="select" required>
                    <option value="">Select a role</option>
                    <option value="super_admin" {{ old('role') === 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                    <option value="company_owner" {{ old('role') === 'company_owner' ? 'selected' : '' }}>Company Owner</option>
                    <option value="sales_executive" {{ old('role') === 'sales_executive' ? 'selected' : '' }}>Sales Executive</option>
                    <option value="marketing_officer" {{ old('role') === 'marketing_officer' ? 'selected' : '' }}>Marketing Officer</option>
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
                Create User
            </button>
        </div>
    </form>
</div>
@endsection
