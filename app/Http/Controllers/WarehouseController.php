<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WarehouseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $search = $request->input('search');
        $status = $request->input('status');

        $query = Warehouse::query()->with('pic');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%");
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        $warehouses = $query->orderBy('name')->paginate(10)->withQueryString();
        
        // Get active users for PIC dropdown selection
        $users = User::where('status', 'active')->orderBy('name')->get(['id', 'name', 'email']);

        return Inertia::render('Warehouses/Index', [
            'warehouses' => $warehouses,
            'users' => $users,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:warehouses,code',
            'name' => 'required|string|max:150',
            'address' => 'nullable|string',
            'pic_user_id' => 'nullable|exists:users,id',
            'status' => 'required|in:active,inactive',
        ], [
            'code.unique' => 'Kode gudang sudah digunakan.',
        ]);

        Warehouse::create($validated);

        return redirect()->route('warehouses.index')->with('success', 'Gudang berhasil ditambahkan.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Warehouse $warehouse): RedirectResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:warehouses,code,' . $warehouse->id,
            'name' => 'required|string|max:150',
            'address' => 'nullable|string',
            'pic_user_id' => 'nullable|exists:users,id',
            'status' => 'required|in:active,inactive',
        ], [
            'code.unique' => 'Kode gudang sudah digunakan.',
        ]);

        $warehouse->update($validated);

        return redirect()->route('warehouses.index')->with('success', 'Gudang berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Warehouse $warehouse): RedirectResponse
    {
        // Check if there are active stock items in this warehouse
        if ($warehouse->stocks()->where('qty', '>', 0)->count() > 0) {
            return redirect()->route('warehouses.index')->with('error', 'Gudang tidak dapat dihapus karena masih terdapat barang dengan stok aktif di dalamnya.');
        }

        // Check if there are any users assigned to this warehouse
        if ($warehouse->users()->count() > 0) {
            return redirect()->route('warehouses.index')->with('error', 'Gudang tidak dapat dihapus karena masih ada user staff yang ditugaskan di gudang ini.');
        }

        $warehouse->delete();

        return redirect()->route('warehouses.index')->with('success', 'Gudang berhasil dihapus.');
    }
}
