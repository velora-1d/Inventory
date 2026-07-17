<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Stock;
use App\Models\StockOut;
use App\Models\StockOutItem;
use App\Models\StockLedger;
use App\Models\Warehouse;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class StockOutController extends Controller
{
    private function generateTransactionNo(): string
    {
        $prefix = 'SO-' . now()->format('Ymd') . '-';
        $last = StockOut::where('transaction_no', 'like', $prefix . '%')
            ->orderBy('transaction_no', 'desc')
            ->value('transaction_no');

        $nextSeq = $last ? (int) substr($last, -4) + 1 : 1;
        return $prefix . str_pad($nextSeq, 4, '0', STR_PAD_LEFT);
    }

    public function index(Request $request)
    {
        $user = Auth::user();

        $query = StockOut::with(['warehouse', 'creator'])
            ->withCount('items');

        if ($user->warehouse_id) {
            $query->where('warehouse_id', $user->warehouse_id);
        }

        if ($request->search) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('transaction_no', 'like', "%{$s}%")
                  ->orWhere('reference_no', 'like', "%{$s}%")
                  ->orWhere('recipient', 'like', "%{$s}%");
            });
        }
        if ($request->status)       $query->where('status', $request->status);
        if ($request->warehouse_id) $query->where('warehouse_id', $request->warehouse_id);
        if ($request->date_from)    $query->whereDate('transaction_date', '>=', $request->date_from);
        if ($request->date_to)      $query->whereDate('transaction_date', '<=', $request->date_to);

        $stockOuts = $query->orderBy('transaction_date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(15)
            ->withQueryString();

        $warehouses = Warehouse::where('status', 'active')->get(['id', 'name', 'code']);
        $products   = Product::where('status', 'active')
            ->with(['baseUnit'])
            ->get(['id', 'name', 'sku', 'base_unit_id', 'sale_price', 'avg_price']);
        $units = Unit::all(['id', 'name', 'symbol']);

        // Attach current stock per product per warehouse for validation
        $stocks = Stock::all(['product_id', 'warehouse_id', 'qty']);

        return Inertia::render('Transactions/StockOut', [
            'stockOuts'  => $stockOuts,
            'warehouses' => $warehouses,
            'products'   => $products,
            'units'      => $units,
            'stocks'     => $stocks,
            'filters'    => $request->only(['search', 'status', 'warehouse_id', 'date_from', 'date_to']),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'transaction_date' => 'required|date',
            'warehouse_id'     => 'required|exists:warehouses,id',
            'reference_no'     => 'nullable|string|max:100',
            'recipient'        => 'nullable|string|max:150',
            'notes'            => 'nullable|string|max:500',
            'items'            => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.unit_id'    => 'required|exists:units,id',
            'items.*.qty'        => 'required|numeric|min:0.01',
            'items.*.price'      => 'required|numeric|min:0',
        ]);

        // Pre-check stock availability for all items
        foreach ($request->items as $item) {
            $stock = Stock::where('product_id', $item['product_id'])
                ->where('warehouse_id', $request->warehouse_id)
                ->first();
            $available = $stock?->qty ?? 0;
            if ($item['qty'] > $available) {
                $product = Product::find($item['product_id']);
                return back()->withErrors([
                    'items' => "Stok tidak cukup untuk barang '{$product->name}'. Tersedia: {$available}, diminta: {$item['qty']}."
                ]);
            }
        }

        DB::transaction(function () use ($request) {
            $stockOut = StockOut::create([
                'transaction_no'   => $this->generateTransactionNo(),
                'transaction_date' => $request->transaction_date,
                'warehouse_id'     => $request->warehouse_id,
                'reference_no'     => $request->reference_no,
                'recipient'        => $request->recipient,
                'notes'            => $request->notes,
                'status'           => 'draft',
                'created_by'       => Auth::id(),
            ]);

            foreach ($request->items as $item) {
                $stockOut->items()->create([
                    'product_id'    => $item['product_id'],
                    'unit_id'       => $item['unit_id'],
                    'qty'           => $item['qty'],
                    'qty_base_unit' => $item['qty'],
                    'price'         => $item['price'],
                    'subtotal'      => $item['qty'] * $item['price'],
                ]);
            }
        });

        return back()->with('success', 'Transaksi barang keluar berhasil dibuat (Draft).');
    }

    public function confirm(Request $request, StockOut $stockOut)
    {
        if ($stockOut->status !== 'draft') {
            return back()->with('error', 'Transaksi ini sudah dikonfirmasi sebelumnya.');
        }

        // Re-validate stock at time of confirmation
        foreach ($stockOut->items as $item) {
            $stock = Stock::where('product_id', $item->product_id)
                ->where('warehouse_id', $stockOut->warehouse_id)
                ->first();
            $available = $stock?->qty ?? 0;
            if ($item->qty_base_unit > $available) {
                return back()->with('error', "Stok tidak mencukupi untuk '{$item->product->name}'. Tersedia: {$available}.");
            }
        }

        DB::transaction(function () use ($stockOut) {
            foreach ($stockOut->items as $item) {
                $stock = Stock::where('product_id', $item->product_id)
                    ->where('warehouse_id', $stockOut->warehouse_id)
                    ->lockForUpdate()
                    ->first();

                $stock->qty -= $item->qty_base_unit;
                $stock->save();

                StockLedger::create([
                    'product_id'       => $item->product_id,
                    'warehouse_id'     => $stockOut->warehouse_id,
                    'transaction_type' => 'out',
                    'reference_type'   => 'stock_out',
                    'reference_id'     => $stockOut->id,
                    'qty_in'           => 0,
                    'qty_out'          => $item->qty_base_unit,
                    'balance'          => $stock->qty,
                    'unit_id'          => $item->unit_id,
                    'price'            => $item->price,
                    'notes'            => "Barang Keluar #{$stockOut->transaction_no}",
                    'created_by'       => Auth::id(),
                    'transaction_date' => $stockOut->transaction_date,
                ]);
            }

            $stockOut->status = 'completed';
            $stockOut->save();
        });

        return back()->with('success', 'Transaksi dikonfirmasi. Stok telah dikurangi.');
    }

    public function destroy(StockOut $stockOut)
    {
        if ($stockOut->status !== 'draft') {
            return back()->with('error', 'Hanya transaksi Draft yang dapat dihapus.');
        }
        $stockOut->items()->delete();
        $stockOut->delete();
        return back()->with('success', 'Transaksi berhasil dihapus.');
    }

    public function show(StockOut $stockOut)
    {
        $stockOut->load(['warehouse', 'creator', 'items.product', 'items.unit']);
        return response()->json($stockOut);
    }

    /**
     * Revert completed transaction back to draft and rollback stock adjustments.
     */
    public function unconfirm(StockOut $stockOut)
    {
        if ($stockOut->status !== 'completed') {
            return back()->with('error', 'Hanya transaksi berstatus Selesai yang dapat dibatalkan.');
        }

        DB::transaction(function () use ($stockOut) {
            foreach ($stockOut->items as $item) {
                $stock = Stock::firstOrCreate(
                    ['product_id' => $item->product_id, 'warehouse_id' => $stockOut->warehouse_id],
                    ['qty' => 0]
                );
                
                $stock->qty += $item->qty_base_unit;
                $stock->save();
            }

            // Delete ledgers
            StockLedger::where('reference_type', 'stock_out')
                ->where('reference_id', $stockOut->id)
                ->delete();

            $stockOut->status = 'draft';
            $stockOut->save();
        });

        return back()->with('success', 'Transaksi dibatalkan kembali menjadi Draft. Stok telah ditambahkan kembali.');
    }

    /**
     * Update a draft transaction.
     */
    public function update(Request $request, StockOut $stockOut)
    {
        if ($stockOut->status !== 'draft') {
            return back()->with('error', 'Hanya transaksi Draft yang dapat diubah.');
        }

        $request->validate([
            'transaction_date' => 'required|date',
            'warehouse_id'     => 'required|exists:warehouses,id',
            'reference_no'     => 'nullable|string|max:50',
            'notes'            => 'nullable|string',
            'items'            => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.unit_id'    => 'required|exists:units,id',
            'items.*.qty'        => 'required|numeric|min:0.01',
            'items.*.price'      => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request, $stockOut) {
            $stockOut->update([
                'transaction_date' => $request->transaction_date,
                'warehouse_id'     => $request->warehouse_id,
                'reference_no'     => $request->reference_no,
                'notes'            => $request->notes,
            ]);

            // Re-create items
            $stockOut->items()->delete();
            foreach ($request->items as $item) {
                $product = \App\Models\Product::findOrFail($item['product_id']);
                $qtyBase = $item['qty'] * ($product->base_unit_id == $item['unit_id'] ? 1 : ($product->purchase_unit_id == $item['unit_id'] ? ($product->purchase_unit_multiplier ?? 1) : 1));

                $stockOut->items()->create([
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
