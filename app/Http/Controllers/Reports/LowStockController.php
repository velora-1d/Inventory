<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Stock;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class LowStockController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // Get products where current stock <= min_stock threshold
        $query = Stock::with(['product.category', 'product.baseUnit', 'warehouse'])
            ->join('products', 'stocks.product_id', '=', 'products.id')
            ->whereColumn('stocks.qty', '<=', 'products.min_stock')
            ->where('products.status', 'active')
            ->select('stocks.*');

        if ($user->warehouse_id) {
            $query->where('stocks.warehouse_id', $user->warehouse_id);
        }
        if ($request->warehouse_id) $query->where('stocks.warehouse_id', $request->warehouse_id);
        if ($request->category_id)  $query->where('products.category_id', $request->category_id);
        if ($request->severity === 'critical') {
            // Critical: stock = 0
            $query->where('stocks.qty', '=', 0);
        } elseif ($request->severity === 'low') {
            // Low: 0 < stock <= min_stock
            $query->where('stocks.qty', '>', 0);
        }
        if ($request->search) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('products.name', 'like', "%{$s}%")
                  ->orWhere('products.sku', 'like', "%{$s}%");
            });
        }

        $lowStocks  = $query->orderBy('stocks.qty', 'asc')->paginate(25)->withQueryString();
        $critical   = Stock::join('products', 'stocks.product_id', '=', 'products.id')
            ->where('stocks.qty', 0)
            ->when($user->warehouse_id, fn($q) => $q->where('stocks.warehouse_id', $user->warehouse_id))
            ->count();
        $belowMin   = Stock::join('products', 'stocks.product_id', '=', 'products.id')
            ->whereColumn('stocks.qty', '<=', 'products.min_stock')
            ->where('stocks.qty', '>', 0)
            ->when($user->warehouse_id, fn($q) => $q->where('stocks.warehouse_id', $user->warehouse_id))
            ->count();

        $warehouses = Warehouse::where('status', 'active')->get(['id', 'name', 'code']);
        $categories = Category::all(['id', 'name']);

        return Inertia::render('Reports/LowStock', [
            'lowStocks'  => $lowStocks,
            'summary'    => ['critical' => $critical, 'below_min' => $belowMin, 'total' => $critical + $belowMin],
            'warehouses' => $warehouses,
            'categories' => $categories,
            'filters'    => $request->only(['warehouse_id', 'category_id', 'severity', 'search']),
        ]);
    }
}
