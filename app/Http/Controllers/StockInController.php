<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Stock;
use App\Models\StockIn;
use App\Models\StockInItem;
use App\Models\StockLedger;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class StockInController extends Controller
{
    /**
     * Generate auto transaction number: SI-YYYYMMDD-XXXX
     */
    private function generateTransactionNo(): string
    {
        $prefix = 'SI-' . now()->format('Ymd') . '-';
        $last = StockIn::where('transaction_no', 'like', $prefix . '%')
            ->orderBy('transaction_no', 'desc')
            ->value('transaction_no');

        $nextSeq = $last
            ? (int) substr($last, -4) + 1
            : 1;

        return $prefix . str_pad($nextSeq, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Display listing of stock-in transactions.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = StockIn::with(['supplier', 'warehouse', 'creator'])
            ->withCount('items');

        // Restrict to user's warehouse if not admin
        if ($user->warehouse_id) {
            $query->where('warehouse_id', $user->warehouse_id);
        }

        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('transaction_no', 'like', "%{$search}%")
                  ->orWhere('reference_no', 'like', "%{$search}%")
                  ->orWhereHas('supplier', fn($s) => $s->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->warehouse_id) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        if ($request->date_from) {
            $query->whereDate('transaction_date', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $query->whereDate('transaction_date', '<=', $request->date_to);
        }

        $stockIns = $query->orderBy('transaction_date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(15)
            ->withQueryString();

        $warehouses = Warehouse::where('status', 'active')->get(['id', 'name', 'code']);
        $suppliers  = Supplier::where('status', 'active')->get(['id', 'name', 'code']);
        $products   = Product::where('status', 'active')
            ->with(['baseUnit', 'purchaseUnit'])
            ->get(['id', 'name', 'sku', 'base_unit_id', 'purchase_unit_id', 'purchase_price', 'avg_price']);
        $units = Unit::all(['id', 'name', 'symbol']);

        return Inertia::render('Transactions/StockIn', [
            'stockIns'   => $stockIns,
            'warehouses' => $warehouses,
            'suppliers'  => $suppliers,
            'products'   => $products,
            'units'      => $units,
            'filters'    => $request->only(['search', 'status', 'warehouse_id', 'date_from', 'date_to']),
        ]);
    }

    /**
     * Store a new stock-in transaction (always Draft initially).
     */
    public function store(Request $request)
    {
        $request->validate([
            'transaction_date' => 'required|date',
            'supplier_id'      => 'required|exists:suppliers,id',
            'warehouse_id'     => 'required|exists:warehouses,id',
            'reference_no'     => 'nullable|string|max:100',
            'notes'            => 'nullable|string|max:500',
            'items'            => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.unit_id'    => 'required|exists:units,id',
            'items.*.qty'        => 'required|numeric|min:0.01',
            'items.*.price'      => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request) {
            $stockIn = StockIn::create([
                'transaction_no'   => $this->generateTransactionNo(),
                'transaction_date' => $request->transaction_date,
                'supplier_id'      => $request->supplier_id,
                'warehouse_id'     => $request->warehouse_id,
                'reference_no'     => $request->reference_no,
                'notes'            => $request->notes,
                'status'           => 'draft',
                'created_by'       => Auth::id(),
            ]);

            foreach ($request->items as $item) {
                $product = Product::find($item['product_id']);
                $unit    = Unit::find($item['unit_id']);

                // Calculate base unit quantity (if purchase unit has conversion)
                $qtyBase = $item['qty'];
                if ($product->purchaseUnit && $product->base_unit_id !== $product->purchase_unit_id) {
                    // Simplified: use qty as is if no conversion table; use base qty = qty for now
                    $qtyBase = $item['qty'];
                }

                $stockIn->items()->create([
                    'product_id'    => $item['product_id'],
                    'unit_id'       => $item['unit_id'],
                    'qty'           => $item['qty'],
                    'qty_base_unit' => $qtyBase,
                    'price'         => $item['price'],
                    'subtotal'      => $item['qty'] * $item['price'],
                ]);
            }
        });

        return back()->with('success', 'Transaksi barang masuk berhasil dibuat (Draft).');
    }

    /**
     * Confirm / complete a draft transaction: update stock, avg_price, and stock_ledger.
     */
    public function confirm(Request $request, StockIn $stockIn)
    {
        if ($stockIn->status !== 'draft') {
            return back()->with('error', 'Transaksi ini sudah dikonfirmasi sebelumnya.');
        }

        DB::transaction(function () use ($stockIn) {
            foreach ($stockIn->items as $item) {
                $product = $item->product;

                // 1. Update or create stock record with row locking
                $stock = Stock::where('product_id', $item->product_id)
                    ->where('warehouse_id', $stockIn->warehouse_id)
                    ->lockForUpdate()
                    ->first();

                if (!$stock) {
                    $stock = Stock::create([
                        'product_id' => $item->product_id,
                        'warehouse_id' => $stockIn->warehouse_id,
                        'qty' => 0
                    ]);
                }

                $oldQty = $stock->qty;
                $addQty = $item->qty_base_unit;

                // 2. Weighted average price calculation
                $oldAvg    = $product->avg_price ?? $item->price;
                $totalOld  = $oldQty * $oldAvg;
                $totalNew  = $addQty * $item->price;
                $newAvg    = ($oldQty + $addQty) > 0
                    ? ($totalOld + $totalNew) / ($oldQty + $addQty)
                    : $item->price;

                // 3. Save new stock qty
                $stock->qty += $addQty;
                $stock->save();

                // 4. Update product average price
                $product->avg_price = round($newAvg, 2);
                $product->save();

                // 5. Record in stock ledger
                StockLedger::create([
                    'product_id'       => $item->product_id,
                    'warehouse_id'     => $stockIn->warehouse_id,
                    'transaction_type' => 'in',
                    'reference_type'   => 'stock_in',
                    'reference_id'     => $stockIn->id,
                    'qty_in'           => $addQty,
                    'qty_out'          => 0,
                    'balance'          => $stock->qty,
                    'unit_id'          => $item->unit_id,
                    'price'            => $item->price,
                    'notes'            => "Barang Masuk #{$stockIn->transaction_no}",
                    'created_by'       => Auth::id(),
                    'transaction_date' => $stockIn->transaction_date,
                ]);
            }

            // 6. Mark transaction as completed
            $stockIn->status = 'completed';
            $stockIn->save();
        });

        return back()->with('success', 'Transaksi berhasil dikonfirmasi. Stok telah diperbarui.');
    }

    /**
     * Delete a draft transaction (only draft can be deleted).
     */
    public function destroy(StockIn $stockIn)
    {
        if ($stockIn->status !== 'draft') {
            return back()->with('error', 'Hanya transaksi berstatus Draft yang dapat dihapus.');
        }

        $stockIn->items()->delete();
        $stockIn->delete();

        return back()->with('success', 'Transaksi berhasil dihapus.');
    }

    /**
     * Get transaction details for viewing.
     */
    public function show(StockIn $stockIn)
    {
        $stockIn->load(['supplier', 'warehouse', 'creator', 'items.product', 'items.unit']);
        return response()->json($stockIn);
    }

    /**
     * Revert completed transaction back to draft and rollback stock adjustments.
     */
    public function unconfirm(StockIn $stockIn)
    {
        if ($stockIn->status !== 'completed') {
            return back()->with('error', 'Hanya transaksi berstatus Selesai yang dapat dibatalkan.');
        }

        // Check if rollback causes negative stock
        foreach ($stockIn->items as $item) {
            $stock = Stock::where('product_id', $item->product_id)
                ->where('warehouse_id', $stockIn->warehouse_id)
                ->first();
            
            if (!$stock || ($stock->qty - $item->qty_base_unit) < 0) {
                return back()->with('error', "Gagal membatalkan: Stok untuk barang {$item->product->name} akan menjadi negatif.");
            }
        }

        DB::transaction(function () use ($stockIn) {
            foreach ($stockIn->items as $item) {
                $stock = Stock::where('product_id', $item->product_id)
                    ->where('warehouse_id', $stockIn->warehouse_id)
                    ->first();
                
                $stock->qty -= $item->qty_base_unit;
                $stock->save();
            }

            // Delete ledgers
            StockLedger::where('reference_type', 'stock_in')
                ->where('reference_id', $stockIn->id)
                ->delete();

            $stockIn->status = 'draft';
            $stockIn->save();
        });

        return back()->with('success', 'Transaksi dibatalkan kembali menjadi Draft. Stok telah dikurangi.');
    }

    /**
     * Update a draft transaction.
     */
    public function update(Request $request, StockIn $stockIn)
    {
        if ($stockIn->status !== 'draft') {
            return back()->with('error', 'Hanya transaksi Draft yang dapat diubah.');
        }

        $request->validate([
            'transaction_date' => 'required|date',
            'supplier_id'      => 'required|exists:suppliers,id',
            'warehouse_id'     => 'required|exists:warehouses,id',
            'reference_no'     => 'nullable|string|max:50',
            'notes'            => 'nullable|string',
            'items'            => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.unit_id'    => 'required|exists:units,id',
            'items.*.qty'        => 'required|numeric|min:0.01',
            'items.*.price'      => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request, $stockIn) {
            $stockIn->update([
                'transaction_date' => $request->transaction_date,
                'supplier_id'      => $request->supplier_id,
                'warehouse_id'     => $request->warehouse_id,
                'reference_no'     => $request->reference_no,
                'notes'            => $request->notes,
            ]);

            // Re-create items
            $stockIn->items()->delete();
            foreach ($request->items as $item) {
                $product = \App\Models\Product::findOrFail($item['product_id']);
                $qtyBase = $item['qty'] * ($product->base_unit_id == $item['unit_id'] ? 1 : ($product->purchase_unit_id == $item['unit_id'] ? ($product->purchase_unit_multiplier ?? 1) : 1));

                $stockIn->items()->create([
                    'product_id'    => $item['product_id'],
                    'unit_id'       => $item['unit_id'],
                    'qty'           => $item['qty'],
                    'qty_base_unit' => $qtyBase,
                    'price'         => $item['price'],
                    'subtotal'      => $item['qty'] * $item['price'],
                ]);
            }
        });

        return back()->with('success', 'Transaksi berhasil diperbarui.');
    }
}
