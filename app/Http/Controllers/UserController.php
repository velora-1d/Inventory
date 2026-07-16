<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with(['warehouse', 'roles'])
            ->when($request->search, fn($q) => $q->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            }))
            ->when($request->role, fn($q) => $q->whereHas('roles', fn($r) => $r->where('name', $request->role)))
            ->latest();

        $users      = $query->paginate(15)->withQueryString();
        $warehouses = Warehouse::where('status', 'active')->get(['id', 'name']);
        $allRoles   = \Spatie\Permission\Models\Role::all(['id', 'name']);

        return Inertia::render('Users/Index', [
            'users'      => $users,
            'warehouses' => $warehouses,
            'allRoles'   => $allRoles,
            'filters'    => $request->only(['search', 'role']),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'         => 'required|string|max:255',
            'email'        => 'required|email|unique:users,email',
            'password'     => 'required|string|min:8|confirmed',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'role'         => 'required|string|exists:roles,name',
        ]);

        $user = User::create([
            'name'         => $data['name'],
            'email'        => $data['email'],
            'password'     => Hash::make($data['password']),
            'warehouse_id' => $data['warehouse_id'] ?? null,
        ]);

        $user->assignRole($data['role']);

        return back()->with('success', 'User berhasil ditambahkan.');
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name'         => 'required|string|max:255',
            'email'        => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'password'     => 'nullable|string|min:8|confirmed',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'role'         => 'required|string|exists:roles,name',
        ]);

        $user->update([
            'name'         => $data['name'],
            'email'        => $data['email'],
            'warehouse_id' => $data['warehouse_id'] ?? null,
            ...($data['password'] ? ['password' => Hash::make($data['password'])] : []),
        ]);

        $user->syncRoles([$data['role']]);

        return back()->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Tidak bisa menghapus akun sendiri.');
        }
        $user->delete();
        return back()->with('success', 'User berhasil dihapus.');
    }
}
