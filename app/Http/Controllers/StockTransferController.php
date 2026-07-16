<?php

namespace App\Http\Controllers;

use App\Models\Stock;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Models\StockLedger;
use App\Models\Warehouse;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class StockTransferController extends Controller
{
    private function generateTransactionNo(): string
    {
        $prefix = 'ST-' . now()->format('Ymd') . '-';
        $last = StockTransfer::where('transaction_no', 'like', $prefix . '%')
            ->orderBy('transaction_no', 'desc')
            ->value('transaction_no');
        $nextSeq = $last ? (int) substr($last, -4) + 1 : 1;
        return $prefix . str_pad($nextSeq, 4, '0', STR_PAD_LEFT);
    }

    public function index(Request $request)
    {
        $user = Auth::user();

        $query = StockTransfer::with(['fromWarehouse', 'toWarehouse', 'creator'])
            ->withCount('items');

        if ($user->warehouse_id) {
            $query->where(function ($q) use ($user) {
                $q->where('from_warehouse_id', $user->warehouse_id)
                  ->orWhere('to_warehouse_id', $user->warehouse_id);
            });
        }

        if ($request->search) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('transaction_no', 'like', "%{$s}%")
                  ->orWhere('reference_no', 'like', "%{$s}%");
            });
        }
        if ($request->status)              $query->where('status', $request->status);
        if ($request->from_warehouse_id)   $query->where('from_warehouse_id', $request->from_warehouse_id);
        if ($request->to_warehouse_id)     $query->where('to_warehouse_id', $request->to_warehouse_id);
        if ($request->date_from)           $query->whereDate('transaction_date', '>=', $request->date_from);
        if ($request->date_to)             $query->whereDate('transaction_date', '<=', $request->date_to);

        $transfers = $query->orderBy('transaction_date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(15)
            ->withQueryString();

        $warehouses = Warehouse::where('status', 'active')->get(['id', 'name', 'code']);
        $products   = Product::where('status', 'active')
            ->with(['baseUnit'])
            ->get(['id', 'name', 'sku', 'base_unit_id', 'avg_price']);
        $units      = Unit::all(['id', 'name', 'symbol']);
        $stocks     = Stock::all(['product_id', 'warehouse_id', 'qty']);

        return Inertia::render('Transactions/StockTransfer', [
            'transfers'  => $transfers,
            'warehouses' => $warehouses,
            'products'   => $products,
            'units'      => $units,
            'stocks'     => $stocks,
            'filters'    => $request->only(['search', 'status', 'from_warehouse_id', 'to_warehouse_id', 'date_from', 'date_to']),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'transaction_date'   => 'required|date',
            'from_warehouse_id'  => 'required|exists:warehouses,id',
            'to_warehouse_id'    => 'required|exists:warehouses,id|different:from_warehouse_id',
            'reference_no'       => 'nullable|string|max:100',
            'notes'              => 'nullable|string|max:500',
            'items'              => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.unit_id'    => 'required|exists:units,id',
            'items.*.qty'        => 'required|numeric|min:0.01',
        ]);

        // Pre-check stock at source warehouse
        foreach ($request->items as $item) {
            $stock = Stock::where('product_id', $item['product_id'])
                ->where('warehouse_id', $request->from_warehouse_id)
                ->first();
            $available = $stock?->qty ?? 0;
            if ($item['qty'] > $available) {
                $product = Product::find($item['product_id']);
                return back()->withErrors([
                    'items' => "Stok tidak cukup untuk '{$product->name}' di gudang asal. Tersedia: {$available}."
                ]);
            }
        }

        DB::transaction(function () use ($request) {
            $transfer = StockTransfer::create([
                'transaction_no'   => $this->generateTransactionNo(),
                'transaction_date' => $request->transaction_date,
                'from_warehouse_id'=> $request->from_warehouse_id,
                'to_warehouse_id'  => $request->to_warehouse_id,
                'reference_no'     => $request->reference_no,
                'notes'            => $request->notes,
                'status'           => 'draft',
                'created_by'       => Auth::id(),
            ]);

            foreach ($request->items as $item) {
                $product = Product::find($item['product_id']);
                $transfer->items()->create([
                    'product_id'    => $item['product_id'],
                    'unit_id'       => $item['unit_id'],
                    'qty'           => $item['qty'],
                    'qty_base_unit' => $item['qty'],
                    'price'         => $product->avg_price ?? 0,
                ]);
            }
        });

        return back()->with('success', 'Transfer stok berhasil dibuat (Draft).');
    }

    public function confirm(Request $request, StockTransfer $stockTransfer)
    {
        if ($stockTransfer->status !== 'draft') {
            return back()->with('error', 'Transaksi ini sudah dikonfirmasi.');
        }

        // Re-validate stock at confirmation time
        foreach ($stockTransfer->items as $item) {
            $stock = Stock::where('product_id', $item->product_id)
                ->where('warehouse_id', $stockTransfer->from_warehouse_id)
                ->first();
            $available = $stock?->qty ?? 0;
            if ($item->qty_base_unit > $available) {
                return back()->with('error', "Stok tidak mencukupi untuk '{$item->product->name}'. Tersedia: {$available}.");
            }
        }

        DB::transaction(function () use ($stockTransfer) {
            foreach ($stockTransfer->items as $item) {
                // Deduct from source
                $fromStock = Stock::firstOrCreate(
                    ['product_id' => $item->product_id, 'warehouse_id' => $stockTransfer->from_warehouse_id],
                    ['qty' => 0]
                );
                $fromStock->qty -= $item->qty_base_unit;
                $fromStock->save();

                 StockLedger::create([
                    'product_id'       => $item->product_id,
                    'warehouse_id'     => $stockTransfer->from_warehouse_id,
                    'transaction_type' => 'transfer_out',
                    'reference_type'   => 'stock_transfer',
                    'reference_id'     => $stockTransfer->id,
                    'unit_id'          => $item->unit_id,
                    'qty_in'           => 0,
                    'qty_out'          => $item->qty_base_unit,
                    'balance'          => $fromStock->qty,
                    'price'            => $item->price ?? $item->product->avg_price ?? 0,
                    'notes'            => "Transfer Keluar #{$stockTransfer->transaction_no}",
                    'created_by'       => Auth::id(),
                    'transaction_date' => $stockTransfer->transaction_date,
                ]);

                // Add to destination
                $toStock = Stock::firstOrCreate(
                    ['product_id' => $item->product_id, 'warehouse_id' => $stockTransfer->to_warehouse_id],
                    ['qty' => 0]
                );
                $toStock->qty += $item->qty_base_unit;
                $toStock->save();

                StockLedger::create([
                    'product_id'       => $item->product_id,
                    'warehouse_id'     => $stockTransfer->to_warehouse_id,
                    'transaction_type' => 'transfer_in',
                    'reference_type'   => 'stock_transfer',
                    'reference_id'     => $stockTransfer->id,
                    'unit_id'          => $item->unit_id,
                    'qty_in'           => $item->qty_base_unit,
                    'qty_out'          => 0,
                    'balance'          => $toStock->qty,
                    'price'            => $item->price ?? $item->product->avg_price ?? 0,
                    'notes'            => "Transfer Masuk #{$stockTransfer->transaction_no}",
                    'created_by'       => Auth::id(),
                    'transaction_date' => $stockTransfer->transaction_date,
                ]);
            }

            $stockTransfer->status = 'completed';
            $stockTransfer->save();
        });

        return back()->with('success', 'Transfer stok berhasil dikonfirmasi.');
    }

    public function destroy(StockTransfer $stockTransfer)
    {
        if ($stockTransfer->status !== 'draft') {
            return back()->with('error', 'Hanya transaksi Draft yang dapat dihapus.');
        }
        $stockTransfer->items()->delete();
        $stockTransfer->delete();
        return back()->with('success', 'Transaksi transfer berhasil dihapus.');
    }

    public function show(StockTransfer $stockTransfer)
    {
        $stockTransfer->load(['fromWarehouse', 'toWarehouse', 'creator', 'items.product', 'items.unit']);
        return response()->json($stockTransfer);
    }

    /**
     * Revert completed transaction back to draft and rollback stock adjustments.
     */
    public function unconfirm(StockTransfer $stockTransfer)
    {
        if ($stockTransfer->status !== 'completed') {
            return back()->with('error', 'Hanya transaksi berstatus Selesai yang dapat dibatalkan.');
        }

        // Check if rollback causes negative stock in destination warehouse
        foreach ($stockTransfer->items as $item) {
            $toStock = Stock::where('product_id', $item->product_id)
                ->where('warehouse_id', $stockTransfer->to_warehouse_id)
                ->first();
            
            if (!$toStock || ($toStock->qty - $item->qty_base_unit) < 0) {
                return back()->with('error', "Gagal membatalkan: Stok untuk barang {$item->product->name} di gudang tujuan ({$stockTransfer->toWarehouse->name}) akan menjadi negatif.");
            }
        }

        DB::transaction(function () use ($stockTransfer) {
            foreach ($stockTransfer->items as $item) {
                // Return stock to from_warehouse
                $fromStock = Stock::firstOrCreate(
                    ['product_id' => $item->product_id, 'warehouse_id' => $stockTransfer->from_warehouse_id],
                    ['qty' => 0]
                );
                $fromStock->qty += $item->qty_base_unit;
                $fromStock->save();

                // Deduct stock from to_warehouse
                $toStock = Stock::where('product_id', $item->product_id)
                    ->where('warehouse_id', $stockTransfer->to_warehouse_id)
                    ->first();
                $toStock->qty -= $item->qty_base_unit;
                $toStock->save();
            }

            // Delete ledgers
            StockLedger::where('reference_type', 'stock_transfer')
                ->where('reference_id', $stockTransfer->id)
                ->delete();

            $stockTransfer->status = 'draft';
            $stockTransfer->save();
        });

        return back()->with('success', 'Transaksi dibatalkan kembali menjadi Draft. Stok telah dikembalikan ke gudang asal.');
    }

    /**
     * Update a draft transaction.
     */
    public function update(Request $request, StockTransfer $stockTransfer)
    {
        if ($stockTransfer->status !== 'draft') {
            return back()->with('error', 'Hanya transaksi Draft yang dapat diubah.');
        }

        $request->validate([
            'transaction_date'  => 'required|date',
            'from_warehouse_id' => 'required|exists:warehouses,id',
            'to_warehouse_id'   => 'required|exists:warehouses,id|different:from_warehouse_id',
            'reference_no'      => 'nullable|string|max:50',
            'notes'             => 'nullable|string',
            'items'             => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.unit_id'    => 'required|exists:units,id',
            'items.*.qty'        => 'required|numeric|min:0.01',
            'items.*.price'      => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request, $stockTransfer) {
            $stockTransfer->update([
                'transaction_date'  => $request->transaction_date,
                'from_warehouse_id' => $request->from_warehouse_id,
                'to_warehouse_id'   => $request->to_warehouse_id,
                'reference_no'      => $request->reference_no,
                'notes'             => $request->notes,
            ]);

            // Re-create items
            $stockTransfer->items()->delete();
            foreach ($request->items as $item) {
                $product = \App\Models\Product::findOrFail($item['product_id']);
                $qtyBase = $item['qty'] * ($product->base_unit_id == $item['unit_id'] ? 1 : ($product->purchase_unit_id == $item['unit_id'] ? ($product->purchase_unit_multiplier ?? 1) : 1));

                $stockTransfer->items()->create([
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
