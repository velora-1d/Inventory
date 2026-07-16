<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\StockLedger;
use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class LedgerController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = StockLedger::with(['product', 'warehouse', 'unit', 'creator'])
            ->orderBy('transaction_date', 'desc')
            ->orderBy('id', 'desc');

        if ($user->warehouse_id) {
            $query->where('warehouse_id', $user->warehouse_id);
        }

        if ($request->product_id)   $query->where('product_id', $request->product_id);
        if ($request->warehouse_id) $query->where('warehouse_id', $request->warehouse_id);
        if ($request->type)         $query->where('transaction_type', $request->type);
        if ($request->date_from)    $query->whereDate('transaction_date', '>=', $request->date_from);
        if ($request->date_to)      $query->whereDate('transaction_date', '<=', $request->date_to);

        $ledgers    = $query->paginate(20)->withQueryString();
        $products   = Product::where('status', 'active')->get(['id', 'name', 'sku']);
        $warehouses = Warehouse::where('status', 'active')->get(['id', 'name', 'code']);

        return Inertia::render('Reports/Ledger', [
            'ledgers'    => $ledgers,
            'products'   => $products,
            'warehouses' => $warehouses,
            'filters'    => $request->only(['product_id', 'warehouse_id', 'type', 'date_from', 'date_to']),
        ]);
    }
}
