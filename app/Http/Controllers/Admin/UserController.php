<?php

namespace App\Http\Controllers\Admin;

use App\Admin\Services\AdminService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(
        private AdminService $adminService
    ) {}

    public function index()
    {
        $users = $this->adminService->getUsers();

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:super_admin,company_owner,sales_executive,marketing_officer',
        ]);

        $validated['password'] = bcrypt($validated['password']);
        $validated['tenant_id'] = auth()->user()->tenant_id;

        $this->adminService->createUser($validated);

        return redirect()->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }

    public function edit($id)
    {
        $user = $this->adminService->getUsers()->findOrFail($id);

        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, $id)
    {
        $user = $this->adminService->getUsers()->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$id,
            'role' => 'required|in:super_admin,company_owner,sales_executive,marketing_officer',
        ]);

        $this->adminService->updateUser($user, $validated);

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }
}
