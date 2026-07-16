<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use App\Models\Warehouse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $search = $request->input('search');
        $categoryId = $request->input('category_id');
        $warehouseId = $request->input('warehouse_id');
        $status = $request->input('status');

        $query = Product::query()->with(['category', 'baseUnit', 'defaultWarehouse']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        if ($warehouseId) {
            $query->where('default_warehouse_id', $warehouseId);
        }

        if ($status) {
            $query->where('status', $status);
        }

        $products = $query->orderBy('name')->paginate(10)->withQueryString();

        // Get options for filters and dropdowns
        $categories = Category::where('status', 'active')->orderBy('name')->get(['id', 'name']);
        $units = Unit::where('status', 'active')->orderBy('name')->get(['id', 'name', 'symbol']);
        $warehouses = Warehouse::where('status', 'active')->orderBy('name')->get(['id', 'name']);

        return Inertia::render('Products/Index', [
            'products' => $products,
            'categories' => $categories,
            'units' => $units,
            'warehouses' => $warehouses,
            'filters' => $request->only(['search', 'category_id', 'warehouse_id', 'status']),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'sku' => 'required|string|max:50|unique:products,sku',
            'name' => 'required|string|max:150',
            'category_id' => 'required|exists:categories,id',
            'base_unit_id' => 'required|exists:units,id',
            'purchase_unit_id' => 'nullable|exists:units,id',
            'sale_unit_id' => 'nullable|exists:units,id',
            'purchase_price' => 'required|numeric|min:0',
            'sale_price' => 'required|numeric|min:0',
            'min_stock' => 'required|numeric|min:0',
            'default_warehouse_id' => 'nullable|exists:warehouses,id',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
            'photo_file' => 'nullable|image|max:2048', // Max 2MB image
        ], [
            'sku.unique' => 'SKU / Kode barang sudah digunakan.',
        ]);

        // Handle photo upload
        if ($request->hasFile('photo_file')) {
            $path = $request->file('photo_file')->store('products', 'public');
            $validated['photo'] = Storage::url($path);
        }

        // Set average price equals purchase price initially
        $validated['avg_price'] = $validated['purchase_price'];

        Product::create($validated);

        return redirect()->route('products.index')->with('success', 'Barang berhasil ditambahkan.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product): RedirectResponse
    {
        // For multipart form-data PUT request workaround in Laravel (often parsed as POST with _method=PUT)
        $validated = $request->validate([
            'sku' => 'required|string|max:50|unique:products,sku,' . $product->id,
            'name' => 'required|string|max:150',
            'category_id' => 'required|exists:categories,id',
            'base_unit_id' => 'required|exists:units,id',
            'purchase_unit_id' => 'nullable|exists:units,id',
            'sale_unit_id' => 'nullable|exists:units,id',
            'purchase_price' => 'required|numeric|min:0',
            'sale_price' => 'required|numeric|min:0',
            'min_stock' => 'required|numeric|min:0',
            'default_warehouse_id' => 'nullable|exists:warehouses,id',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
            'photo_file' => 'nullable|image|max:2048',
        ], [
            'sku.unique' => 'SKU / Kode barang sudah digunakan.',
        ]);

        // Handle photo upload
        if ($request->hasFile('photo_file')) {
            // Delete old photo if it exists
            if ($product->photo) {
                $oldPath = str_replace('/storage/', '', $product->photo);
                Storage::disk('public')->delete($oldPath);
            }

            $path = $request->file('photo_file')->store('products', 'public');
            $validated['photo'] = Storage::url($path);
        }

        $product->update($validated);

        return redirect()->route('products.index')->with('success', 'Barang berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product): RedirectResponse
    {
        // Check if there are active stocks for this product
        $hasStock = $product->stocks()->where('qty', '>', 0)->exists();
        if ($hasStock) {
            return redirect()->route('products.index')->with('error', 'Barang tidak dapat dihapus karena masih memiliki stok aktif di gudang.');
        }

        // Delete photo from storage if exists
        if ($product->photo) {
            $path = str_replace('/storage/', '', $product->photo);
            Storage::disk('public')->delete($path);
        }

        $product->delete();

        return redirect()->route('products.index')->with('success', 'Barang berhasil dihapus.');
    }
}
