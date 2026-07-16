<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Inertia\Inertia;

class RoleController extends Controller
{
    public function index()
    {
        $roles       = Role::with('permissions')->withCount('users')->get();
        $permissions = Permission::all()->groupBy(fn($p) => explode('.', $p->name)[0] ?? 'other');

        return Inertia::render('Roles/Index', [
            'roles'       => $roles,
            'permissions' => $permissions,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:100|unique:roles,name',
            'permissions' => 'array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        $role = Role::create(['name' => $data['name'], 'guard_name' => 'web']);
        if (!empty($data['permissions'])) {
            $role->givePermissionTo($data['permissions']);
        }

        return back()->with('success', 'Role berhasil ditambahkan.');
    }

    public function update(Request $request, Role $role)
    {
        $data = $request->validate([
            'name'          => ['required', 'string', 'max:100', \Illuminate\Validation\Rule::unique('roles', 'name')->ignore($role->id)],
            'permissions'   => 'array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        // Protect built-in roles from renaming
        if (in_array($role->name, ['super-admin', 'admin']) && $role->name !== $data['name']) {
            return back()->with('error', 'Role bawaan tidak dapat diubah namanya.');
        }

        $role->update(['name' => $data['name']]);
        $role->syncPermissions($data['permissions'] ?? []);

        return back()->with('success', 'Role berhasil diperbarui.');
    }

    public function destroy(Role $role)
    {
        if (in_array($role->name, ['super-admin', 'admin', 'staff'])) {
            return back()->with('error', 'Role bawaan tidak dapat dihapus.');
        }
        if ($role->users()->count() > 0) {
            return back()->with('error', 'Role masih digunakan oleh ' . $role->users()->count() . ' user.');
        }
        $role->delete();
        return back()->with('success', 'Role berhasil dihapus.');
    }
}
