<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Stock;
use App\Models\StockOpname;
use App\Models\StockOpnameItem;
use App\Models\StockLedger;
use App\Models\Warehouse;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class StockOpnameController extends Controller
{
    private function generateTransactionNo(): string
    {
        $prefix = 'OP-' . now()->format('Ymd') . '-';
        $last = StockOpname::where('transaction_no', 'like', $prefix . '%')
            ->orderBy('transaction_no', 'desc')
            ->value('transaction_no');
        $nextSeq = $last ? (int) substr($last, -4) + 1 : 1;
        return $prefix . str_pad($nextSeq, 4, '0', STR_PAD_LEFT);
    }

    public function index(Request $request)
    {
        $user = Auth::user();

        $query = StockOpname::with(['warehouse', 'creator'])->withCount('items');

        if ($user->warehouse_id) {
            $query->where('warehouse_id', $user->warehouse_id);
        }

        if ($request->search) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('transaction_no', 'like', "%{$s}%");
            });
        }
        if ($request->status)       $query->where('status', $request->status);
        if ($request->warehouse_id) $query->where('warehouse_id', $request->warehouse_id);
        if ($request->date_from)    $query->whereDate('opname_date', '>=', $request->date_from);
        if ($request->date_to)      $query->whereDate('opname_date', '<=', $request->date_to);

        $opnames = $query->orderBy('opname_date', 'desc')->orderBy('id', 'desc')
            ->paginate(15)->withQueryString();

        $warehouses = Warehouse::where('status', 'active')->get(['id', 'name', 'code']);

        // Products with current stock (preload for opname sheet generation)
        $products = Product::where('status', 'active')
            ->with(['baseUnit'])
            ->get(['id', 'name', 'sku', 'base_unit_id']);

        $stocks = Stock::all(['product_id', 'warehouse_id', 'qty']);
        $units  = Unit::all(['id', 'name', 'symbol']);

        return Inertia::render('Transactions/StockOpname', [
            'opnames'    => $opnames,
            'warehouses' => $warehouses,
            'products'   => $products,
            'stocks'     => $stocks,
            'units'      => $units,
            'filters'    => $request->only(['search', 'status', 'warehouse_id', 'date_from', 'date_to']),
        ]);
    }

    /**
     * Store a new opname sheet — pre-populated with system stock quantities.
     * User picks warehouse, system auto-fills system_qty per product.
     * User then enters actual physical qty (physical_qty).
     */
    public function store(Request $request)
    {
        $request->validate([
            'opname_date'  => 'required|date',
            'warehouse_id' => 'required|exists:warehouses,id',
            'notes'        => 'nullable|string|max:500',
            'items'        => 'required|array|min:1',
            'items.*.product_id'   => 'required|exists:products,id',
            'items.*.unit_id'      => 'required|exists:units,id',
            'items.*.system_qty'   => 'required|numeric|min:0',
            'items.*.physical_qty' => 'required|numeric|min:0',
            'items.*.notes'        => 'nullable|string|max:200',
        ]);

        DB::transaction(function () use ($request) {
            $opname = StockOpname::create([
                'transaction_no' => $this->generateTransactionNo(),
                'opname_date'    => $request->opname_date,
                'warehouse_id'   => $request->warehouse_id,
                'notes'          => $request->notes,
                'status'         => 'draft',
                'created_by'     => Auth::id(),
            ]);

            foreach ($request->items as $item) {
                $diff = $item['physical_qty'] - $item['system_qty'];
                $opname->items()->create([
                    'product_id'   => $item['product_id'],
                    'unit_id'      => $item['unit_id'],
                    'system_qty'   => $item['system_qty'],
                    'physical_qty' => $item['physical_qty'],
                    'difference'   => $diff,
                    'notes'        => $item['notes'] ?? null,
                ]);
            }
        });

        return back()->with('success', 'Stock opname berhasil dibuat (Draft).');
    }

    /**
     * Confirm opname: adjust actual stock to match physical count,
     * record adjustments in stock_ledgers.
     */
    public function confirm(Request $request, StockOpname $stockOpname)
    {
        if ($stockOpname->status !== 'draft') {
            return back()->with('error', 'Opname ini sudah dikonfirmasi sebelumnya.');
        }

        DB::transaction(function () use ($stockOpname) {
            foreach ($stockOpname->items as $item) {
                if ($item->difference == 0) continue; // No adjustment needed

                $stock = Stock::firstOrCreate(
                    ['product_id' => $item->product_id, 'warehouse_id' => $stockOpname->warehouse_id],
                    ['qty' => 0]
                );

                $oldBalance = $stock->qty;
                $stock->qty = $item->physical_qty; // Set to actual physical count
                $stock->save();

                // Determine adjustment direction
                $qtyIn  = $item->difference > 0 ? abs($item->difference) : 0;
                $qtyOut = $item->difference < 0 ? abs($item->difference) : 0;

                StockLedger::create([
                    'product_id'       => $item->product_id,
                    'warehouse_id'     => $stockOpname->warehouse_id,
                    'transaction_type' => 'adjustment',
                    'reference_type'   => 'stock_opname',
                    'reference_id'     => $stockOpname->id,
                    'unit_id'          => $item->unit_id,
                    'qty_in'           => $qtyIn,
                    'qty_out'          => $qtyOut,
                    'balance'          => $stock->qty,
                    'price'            => 0,
                    'notes'            => "Penyesuaian Opname #{$stockOpname->transaction_no} (Sistem: {$item->system_qty} → Fisik: {$item->physical_qty})",
                    'created_by'       => Auth::id(),
                    'transaction_date' => $stockOpname->opname_date,
                ]);
            }

            $stockOpname->status = 'completed';
            $stockOpname->save();
        });

        return back()->with('success', 'Stock opname dikonfirmasi. Stok sistem telah disesuaikan dengan stok fisik.');
    }

    public function destroy(StockOpname $stockOpname)
    {
        if ($stockOpname->status !== 'draft') {
            return back()->with('error', 'Hanya opname Draft yang dapat dihapus.');
        }
        $stockOpname->items()->delete();
        $stockOpname->delete();
        return back()->with('success', 'Opname berhasil dihapus.');
    }

    public function show(StockOpname $stockOpname)
    {
        $stockOpname->load(['warehouse', 'creator', 'items.product']);
        return response()->json($stockOpname);
    }

    /**
     * Revert completed transaction back to draft and rollback stock adjustments.
     */
    public function unconfirm(StockOpname $stockOpname)
    {
        if ($stockOpname->status !== 'completed') {
            return back()->with('error', 'Hanya opname berstatus Selesai yang dapat dibatalkan.');
        }

        // Check if rollback would result in negative stock (in case system_qty is somehow negative)
        foreach ($stockOpname->items as $item) {
            if ($item->system_qty < 0) {
                return back()->with('error', "Gagal membatalkan: Nilai stok sistem awal untuk {$item->product->name} bernilai negatif.");
            }
        }

        DB::transaction(function () use ($stockOpname) {
            foreach ($stockOpname->items as $item) {
                if ($item->difference == 0) continue;

                $stock = Stock::where('product_id', $item->product_id)
                    ->where('warehouse_id', $stockOpname->warehouse_id)
                    ->first();
                
                if ($stock) {
                    $stock->qty = $item->system_qty;
                    $stock->save();
                }
            }

            // Delete ledgers
            StockLedger::where('reference_type', 'stock_opname')
                ->where('reference_id', $stockOpname->id)
                ->delete();

            $stockOpname->status = 'draft';
            $stockOpname->save();
        });

        return back()->with('success', 'Opname dibatalkan kembali menjadi Draft. Stok sistem telah dikembalikan.');
    }

    /**
     * Update a draft transaction.
     */
    public function update(Request $request, StockOpname $stockOpname)
    {
        if ($stockOpname->status !== 'draft') {
            return back()->with('error', 'Hanya opname Draft yang dapat diubah.');
        }

        $request->validate([
            'opname_date'  => 'required|date',
            'warehouse_id' => 'required|exists:warehouses,id',
            'notes'        => 'nullable|string|max:500',
            'items'        => 'required|array|min:1',
            'items.*.product_id'   => 'required|exists:products,id',
            'items.*.unit_id'      => 'required|exists:units,id',
            'items.*.system_qty'   => 'required|numeric|min:0',
            'items.*.physical_qty' => 'required|numeric|min:0',
            'items.*.notes'        => 'nullable|string|max:200',
        ]);

        DB::transaction(function () use ($request, $stockOpname) {
            $stockOpname->update([
                'opname_date'  => $request->opname_date,
                'warehouse_id' => $request->warehouse_id,
                'notes'        => $request->notes,
            ]);

            // Re-create items
            $stockOpname->items()->delete();
            foreach ($request->items as $item) {
                $diff = $item['physical_qty'] - $item['system_qty'];
                $stockOpname->items()->create([
                    'product_id'   => $item['product_id'],
                    'unit_id'      => $item['unit_id'],
                    'system_qty'   => $item['system_qty'],
                    'physical_qty' => $item['physical_qty'],
                    'difference'   => $diff,
                    'notes'        => $item['notes'] ?? null,
                ]);
            }
        });

        return back()->with('success', 'Opname berhasil diperbarui.');
    }
}
