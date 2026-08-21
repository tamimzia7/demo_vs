<?php

namespace App\Http\Controllers\Admin;

use App\Admin\Services\AdminService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SystemTagController extends Controller
{
    public function __construct(
        private AdminService $adminService
    ) {}

    public function index()
    {
        $systemTags = $this->adminService->getSystemTags();

        return view('admin.system-tags.index', compact('systemTags'));
    }

    public function create()
    {
        return view('admin.system-tags.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:system_tags,name',
            'color' => 'nullable|string|max:7',
            'description' => 'nullable|string|max:500',
        ]);

        $validated['slug'] = str($validated['name'])->slug();

        $this->adminService->createSystemTag($validated);

        return redirect()->route('admin.system-tags.index')
            ->with('success', 'System tag created successfully.');
    }

    public function destroy($id)
    {
        return redirect()->route('admin.system-tags.index')
            ->withErrors(['error' => 'System tags cannot be deleted (BDR-015).']);
    }
}
