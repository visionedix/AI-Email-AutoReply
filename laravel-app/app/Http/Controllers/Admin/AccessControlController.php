<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class AccessControlController extends Controller
{
    public function index()
    {
        $roles = Role::with('permissions')
            ->withCount('users')
            ->orderBy('name')
            ->get();

        $permissions = Permission::withCount('roles')
            ->orderBy('name')
            ->get();

        return view('admin.access_control.index', compact('roles', 'permissions'));
    }

    public function storePermission(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'permission_name' => ['required', 'string', 'max:100', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', 'unique:permissions,name'],
        ]);

        Permission::create([
            'name' => $data['permission_name'],
            'guard_name' => config('auth.defaults.guard', 'web'),
        ]);

        $this->forgetPermissionCache();

        return back()->with('success', 'Permission created successfully.');
    }

    public function destroyPermission(Permission $permission): RedirectResponse
    {
        $permission->delete();

        $this->forgetPermissionCache();

        return back()->with('success', 'Permission deleted successfully.');
    }

    public function storeRole(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'role_name' => ['required', 'string', 'max:100', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', 'unique:roles,name'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ]);

        DB::transaction(function () use ($data): void {
            $role = Role::create([
                'name' => $data['role_name'],
                'guard_name' => config('auth.defaults.guard', 'web'),
            ]);

            $role->syncPermissions($data['permissions'] ?? []);
        });

        $this->forgetPermissionCache();

        return back()->with('success', 'Role created successfully.');
    }

    public function updateRole(Request $request, Role $role): RedirectResponse
    {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('roles', 'name')->ignore($role->id),
            ],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ]);

        $role->update(['name' => $data['name']]);
        $role->syncPermissions($data['permissions'] ?? []);

        $this->forgetPermissionCache();

        return back()->with('success', 'Role updated successfully.');
    }

    public function destroyRole(Role $role): RedirectResponse
    {
        if ($role->users()->exists()) {
            return back()->withErrors(['role' => 'This role cannot be deleted while users are assigned to it.']);
        }

        $role->delete();

        $this->forgetPermissionCache();

        return back()->with('success', 'Role deleted successfully.');
    }

    private function forgetPermissionCache(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
