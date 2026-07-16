<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\StockLedger;
use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class MutationController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // Aggregate mutations per product per warehouse for the period
        $query = StockLedger::with(['product.baseUnit', 'warehouse'])
            ->select(
                'product_id',
                'warehouse_id',
                DB::raw('SUM(qty_in) as total_in'),
                DB::raw('SUM(qty_out) as total_out'),
                DB::raw('SUM(qty_in) - SUM(qty_out) as net_change'),
                DB::raw('MAX(balance) as latest_balance'),
                DB::raw('COUNT(*) as transaction_count')
            )
            ->groupBy('product_id', 'warehouse_id');

        if ($user->warehouse_id) {
            $query->where('warehouse_id', $user->warehouse_id);
        }
        if ($request->product_id)   $query->where('product_id', $request->product_id);
        if ($request->warehouse_id) $query->where('warehouse_id', $request->warehouse_id);
        if ($request->date_from)    $query->whereDate('transaction_date', '>=', $request->date_from);
        if ($request->date_to)      $query->whereDate('transaction_date', '<=', $request->date_to);

        $mutations  = $query->orderBy('total_in', 'desc')->paginate(20)->withQueryString();
        $products   = Product::where('status', 'active')->get(['id', 'name', 'sku']);
        $warehouses = Warehouse::where('status', 'active')->get(['id', 'name', 'code']);

        // Summary totals
        $summaryQuery = StockLedger::query()
            ->when($user->warehouse_id, fn($q) => $q->where('warehouse_id', $user->warehouse_id))
            ->when($request->warehouse_id, fn($q) => $q->where('warehouse_id', $request->warehouse_id))
            ->when($request->date_from, fn($q) => $q->whereDate('transaction_date', '>=', $request->date_from))
            ->when($request->date_to,   fn($q) => $q->whereDate('transaction_date', '<=', $request->date_to));

        $summary = [
            'total_in'  => (clone $summaryQuery)->sum('qty_in'),
            'total_out' => (clone $summaryQuery)->sum('qty_out'),
            'transactions' => (clone $summaryQuery)->count(),
        ];

        return Inertia::render('Reports/Mutations', [
            'mutations'  => $mutations,
            'summary'    => $summary,
            'products'   => $products,
            'warehouses' => $warehouses,
            'filters'    => $request->only(['product_id', 'warehouse_id', 'date_from', 'date_to']),
        ]);
    }
}
