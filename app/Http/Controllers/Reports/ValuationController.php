<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Stock;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ValuationController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = Stock::with(['product.category', 'product.baseUnit', 'warehouse'])
            ->join('products', 'stocks.product_id', '=', 'products.id')
            ->select('stocks.*', DB::raw('stocks.qty * products.avg_price as total_value'))
            ->where('stocks.qty', '>', 0);

        if ($user->warehouse_id) {
            $query->where('stocks.warehouse_id', $user->warehouse_id);
        }
        if ($request->warehouse_id) $query->where('stocks.warehouse_id', $request->warehouse_id);
        if ($request->category_id)  $query->where('products.category_id', $request->category_id);
        if ($request->search) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('products.name', 'like', "%{$s}%")
                  ->orWhere('products.sku', 'like', "%{$s}%");
            });
        }

        $stocks   = $query->orderBy('total_value', 'desc')->paginate(25)->withQueryString();
        $totalValue = Stock::join('products', 'stocks.product_id', '=', 'products.id')
            ->select(DB::raw('SUM(stocks.qty * products.avg_price) as total'))
            ->when($user->warehouse_id, fn($q) => $q->where('stocks.warehouse_id', $user->warehouse_id))
            ->when($request->warehouse_id, fn($q) => $q->where('stocks.warehouse_id', $request->warehouse_id))
            ->value('total') ?? 0;

        $warehouses = Warehouse::where('status', 'active')->get(['id', 'name', 'code']);
        $categories = Category::all(['id', 'name']);

        return Inertia::render('Reports/Valuation', [
            'stocks'     => $stocks,
            'totalValue' => $totalValue,
            'warehouses' => $warehouses,
            'categories' => $categories,
            'filters'    => $request->only(['warehouse_id', 'category_id', 'search']),
        ]);
    }
}
