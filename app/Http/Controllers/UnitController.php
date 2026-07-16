<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UnitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $search = $request->input('search');
        $status = $request->input('status');

        $query = Unit::query();

        if ($search) {
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('symbol', 'like', "%{$search}%");
        }

        if ($status) {
            $query->where('status', $status);
        }

        $units = $query->orderBy('name')->paginate(10)->withQueryString();

        return Inertia::render('Units/Index', [
            'units' => $units,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:units,name',
            'symbol' => 'required|string|max:20|unique:units,symbol',
            'status' => 'required|in:active,inactive',
        ], [
            'name.unique' => 'Nama satuan sudah digunakan.',
            'symbol.unique' => 'Simbol satuan sudah digunakan.',
        ]);

        Unit::create($validated);

        return redirect()->route('units.index')->with('success', 'Satuan berhasil ditambahkan.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Unit $unit): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:units,name,' . $unit->id,
            'symbol' => 'required|string|max:20|unique:units,symbol,' . $unit->id,
            'status' => 'required|in:active,inactive',
        ], [
            'name.unique' => 'Nama satuan sudah digunakan.',
            'symbol.unique' => 'Simbol satuan sudah digunakan.',
        ]);

        $unit->update($validated);

        return redirect()->route('units.index')->with('success', 'Satuan berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Unit $unit): RedirectResponse
    {
        // Check if there are active products using this unit as base/purchase/sale unit
        $inUse = \App\Models\Product::where('base_unit_id', $unit->id)
            ->orWhere('purchase_unit_id', $unit->id)
            ->orWhere('sale_unit_id', $unit->id)
            ->exists();

        if ($inUse) {
            return redirect()->route('units.index')->with('error', 'Satuan tidak dapat dihapus karena sedang digunakan oleh produk.');
        }

        $unit->delete();

        return redirect()->route('units.index')->with('success', 'Satuan berhasil dihapus.');
    }
}
