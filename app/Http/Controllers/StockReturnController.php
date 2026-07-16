<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Stock;
use App\Models\StockReturn;
use App\Models\StockReturnItem;
use App\Models\StockLedger;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class StockReturnController extends Controller
{
    private function generateTransactionNo(string $type): string
    {
        $code   = $type === 'return_in' ? 'RI' : 'RO'; // return_in = retur dari customer, return_out = retur ke supplier
        $prefix = "{$code}-" . now()->format('Ymd') . '-';
        $last   = StockReturn::where('transaction_no', 'like', $prefix . '%')
            ->orderBy('transaction_no', 'desc')
            ->value('transaction_no');
        $nextSeq = $last ? (int) substr($last, -4) + 1 : 1;
        return $prefix . str_pad($nextSeq, 4, '0', STR_PAD_LEFT);
    }

    public function index(Request $request)
    {
        $user = Auth::user();

        $query = StockReturn::with(['warehouse', 'creator'])->withCount('items');

        if ($user->warehouse_id) {
            $query->where('warehouse_id', $user->warehouse_id);
        }

        if ($request->search) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('transaction_no', 'like', "%{$s}%")
                  ->orWhere('reason', 'like', "%{$s}%");
            });
        }
        if ($request->status)      $query->where('status', $request->status);
        if ($request->return_type) $query->where('return_type', $request->return_type);
        if ($request->warehouse_id) $query->where('warehouse_id', $request->warehouse_id);
        if ($request->date_from)   $query->whereDate('return_date', '>=', $request->date_from);
        if ($request->date_to)     $query->whereDate('return_date', '<=', $request->date_to);

        $returns = $query->orderBy('return_date', 'desc')->orderBy('id', 'desc')
            ->paginate(15)->withQueryString();

        $warehouses = Warehouse::where('status', 'active')->get(['id', 'name', 'code']);
        $suppliers  = Supplier::where('status', 'active')->get(['id', 'name', 'code']);
        $products   = Product::where('status', 'active')
            ->with(['baseUnit'])
            ->get(['id', 'name', 'sku', 'base_unit_id', 'avg_price', 'purchase_price']);
        $units  = Unit::all(['id', 'name', 'symbol']);
        $stocks = Stock::all(['product_id', 'warehouse_id', 'qty']);

        return Inertia::render('Transactions/StockReturn', [
            'returns'    => $returns,
            'warehouses' => $warehouses,
            'suppliers'  => $suppliers,
            'products'   => $products,
            'units'      => $units,
            'stocks'     => $stocks,
            'filters'    => $request->only(['search', 'status', 'return_type', 'warehouse_id', 'date_from', 'date_to']),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'return_date'  => 'required|date',
            'return_type'  => 'required|in:return_in,return_out',
            'warehouse_id' => 'required|exists:warehouses,id',
            'reason'       => 'nullable|string|max:500',
            'items'        => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.unit_id'    => 'required|exists:units,id',
            'items.*.qty'        => 'required|numeric|min:0.01',
            'items.*.price'      => 'required|numeric|min:0',
        ]);

        // For return_out (retur ke supplier), validate sufficient stock
        if ($request->return_type === 'return_out') {
            foreach ($request->items as $item) {
                $stock = Stock::where('product_id', $item['product_id'])
                    ->where('warehouse_id', $request->warehouse_id)->first();
                $available = $stock?->qty ?? 0;
                if ($item['qty'] > $available) {
                    $product = Product::find($item['product_id']);
                    return back()->withErrors([
                        'items' => "Stok tidak cukup untuk '{$product->name}'. Tersedia: {$available}."
                    ]);
                }
            }
        }

        DB::transaction(function () use ($request) {
            $return = StockReturn::create([
                'transaction_no' => $this->generateTransactionNo($request->return_type),
                'return_date'    => $request->return_date,
                'return_type'    => $request->return_type,
                'warehouse_id'   => $request->warehouse_id,
                'reason'         => $request->reason,
                'status'         => 'draft',
                'created_by'     => Auth::id(),
            ]);

            foreach ($request->items as $item) {
                $return->items()->create([
                    'product_id'    => $item['product_id'],
                    'unit_id'       => $item['unit_id'],
                    'qty'           => $item['qty'],
                    'qty_base_unit' => $item['qty'],
                    'price'         => $item['price'],
                    'subtotal'      => $item['qty'] * $item['price'],
                    'condition'     => $item['condition'] ?? 'good',
                    'notes'         => $item['notes'] ?? null,
                ]);
            }
        });

        return back()->with('success', 'Retur barang berhasil dibuat (Draft).');
    }

    public function confirm(Request $request, StockReturn $stockReturn)
    {
        if ($stockReturn->status !== 'draft') {
            return back()->with('error', 'Retur ini sudah dikonfirmasi sebelumnya.');
        }

        DB::transaction(function () use ($stockReturn) {
            foreach ($stockReturn->items as $item) {
                $stock = Stock::firstOrCreate(
                    ['product_id' => $item->product_id, 'warehouse_id' => $stockReturn->warehouse_id],
                    ['qty' => 0]
                );

                // return_in = barang kembali masuk ke gudang (stok +)
                // return_out = barang keluar ke supplier (stok -)
                if ($stockReturn->return_type === 'return_in') {
                    $stock->qty += $item->qty_base_unit;
                    $ledgerType  = 'return_in';
                    $qtyIn  = $item->qty_base_unit;
                    $qtyOut = 0;
                } else {
                    $stock->qty -= $item->qty_base_unit;
                    $ledgerType  = 'return_out';
                    $qtyIn  = 0;
                    $qtyOut = $item->qty_base_unit;
                }
                $stock->save();

                $typeLabel = $stockReturn->return_type === 'return_in' ? 'Retur Masuk' : 'Retur Keluar';
                StockLedger::create([
                    'product_id'       => $item->product_id,
                    'warehouse_id'     => $stockReturn->warehouse_id,
                    'transaction_type' => $ledgerType,
                    'reference_type'   => 'stock_return',
                    'reference_id'     => $stockReturn->id,
                    'unit_id'          => $item->unit_id,
                    'qty_in'           => $qtyIn,
                    'qty_out'          => $qtyOut,
                    'balance'          => $stock->qty,
                    'price'            => $item->price,
                    'notes'            => "{$typeLabel} #{$stockReturn->transaction_no}",
                    'created_by'       => Auth::id(),
                    'transaction_date' => $stockReturn->return_date,
                ]);
            }

            $stockReturn->status = 'completed';
            $stockReturn->save();
        });

        return back()->with('success', 'Retur barang dikonfirmasi. Stok telah diperbarui.');
    }

    public function destroy(StockReturn $stockReturn)
    {
        if ($stockReturn->status !== 'draft') {
            return back()->with('error', 'Hanya retur Draft yang dapat dihapus.');
        }
        $stockReturn->items()->delete();
        $stockReturn->delete();
        return back()->with('success', 'Retur berhasil dihapus.');
    }

    public function show(StockReturn $stockReturn)
    {
        $stockReturn->load(['warehouse', 'creator', 'items.product', 'items.unit']);
        return response()->json($stockReturn);
    }

    /**
     * Revert completed transaction back to draft and rollback stock adjustments.
     */
    public function unconfirm(StockReturn $stockReturn)
    {
        if ($stockReturn->status !== 'completed') {
            return back()->with('error', 'Hanya retur berstatus Selesai yang dapat dibatalkan.');
        }

        // Check if rollback causes negative stock (only relevant for return_in which added stock)
        if ($stockReturn->return_type === 'return_in') {
            foreach ($stockReturn->items as $item) {
                $stock = Stock::where('product_id', $item->product_id)
                    ->where('warehouse_id', $stockReturn->warehouse_id)
                    ->first();
                
                if (!$stock || ($stock->qty - $item->qty_base_unit) < 0) {
                    return back()->with('error', "Gagal membatalkan: Stok untuk barang {$item->product->name} akan menjadi negatif.");
                }
            }
        }

        DB::transaction(function () use ($stockReturn) {
            foreach ($stockReturn->items as $item) {
                $stock = Stock::firstOrCreate(
                    ['product_id' => $item->product_id, 'warehouse_id' => $stockReturn->warehouse_id],
                    ['qty' => 0]
                );

                if ($stockReturn->return_type === 'return_in') {
                    // Reversing return_in: subtract stock
                    $stock->qty -= $item->qty_base_unit;
                } else {
                    // Reversing return_out: add stock
                    $stock->qty += $item->qty_base_unit;
                }
                $stock->save();
            }

            // Delete ledgers
            StockLedger::where('reference_type', 'stock_return')
                ->where('reference_id', $stockReturn->id)
                ->delete();

            $stockReturn->status = 'draft';
            $stockReturn->save();
        });

        return back()->with('success', 'Retur dibatalkan kembali menjadi Draft. Stok telah disesuaikan.');
    }

    /**
     * Update a draft transaction.
     */
    public function update(Request $request, StockReturn $stockReturn)
    {
        if ($stockReturn->status !== 'draft') {
            return back()->with('error', 'Hanya retur Draft yang dapat diubah.');
        }

        $request->validate([
            'return_date'  => 'required|date',
            'return_type'  => 'required|in:return_in,return_out',
            'warehouse_id' => 'required|exists:warehouses,id',
            'reason'       => 'nullable|string|max:500',
            'items'        => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.unit_id'    => 'required|exists:units,id',
            'items.*.qty'        => 'required|numeric|min:0.01',
            'items.*.price'      => 'required|numeric|min:0',
        ]);

        // For return_out (retur ke supplier), validate sufficient stock
        if ($request->return_type === 'return_out') {
            foreach ($request->items as $item) {
                $stock = Stock::where('product_id', $item['product_id'])
                    ->where('warehouse_id', $request->warehouse_id)->first();
                $available = $stock?->qty ?? 0;
                if ($item['qty'] > $available) {
                    $product = Product::find($item['product_id']);
                    return back()->withErrors([
                        'items' => "Stok tidak cukup untuk '{$product->name}'. Tersedia: {$available}."
                    ]);
                }
            }
        }

        DB::transaction(function () use ($request, $stockReturn) {
            $stockReturn->update([
                'return_date'  => $request->return_date,
                'return_type'  => $request->return_type,
                'warehouse_id' => $request->warehouse_id,
                'reason'       => $request->reason,
            ]);

            // Re-create items
            $stockReturn->items()->delete();
            foreach ($request->items as $item) {
                $product = \App\Models\Product::findOrFail($item['product_id']);
                $qtyBase = $item['qty'] * ($product->base_unit_id == $item['unit_id'] ? 1 : ($product->purchase_unit_id == $item['unit_id'] ? ($product->purchase_unit_multiplier ?? 1) : 1));

                $stockReturn->items()->create([
                    'product_id'    => $item['product_id'],
                    'unit_id'       => $item['unit_id'],
                    'qty'           => $item['qty'],
                    'qty_base_unit' => $qtyBase,
                    'price'         => $item['price'],
                    'subtotal'      => $item['qty'] * $item['price'],
                ]);
            }
        });

        return back()->with('success', 'Retur berhasil diperbarui.');
    }
}
