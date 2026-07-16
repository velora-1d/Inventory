<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Stock;
use App\Models\StockLedger;
use App\Models\Warehouse;
use App\Models\StockIn;
use App\Models\StockOut;
use App\Models\StockTransfer;
use App\Models\StockOpname;
use App\Models\StockReturn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display the dashboard page.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $selectedWarehouseId = $request->input('warehouse_id');

        // Enforcement of warehouse restrictions
        if ($user->warehouse_id) {
            $selectedWarehouseId = $user->warehouse_id;
        }

        // Get Warehouses list for filter dropdown
        $warehouses = Warehouse::where('status', 'active')->get(['id', 'name', 'code']);

        // 1. Total active products SKU
        $totalSku = Product::where('status', 'active')->count();

        // 2. Total Value of Inventory (weighted average price * stock qty)
        $totalValueQuery = Stock::join('products', 'stocks.product_id', '=', 'products.id')
            ->where('products.status', 'active');
            
        if ($selectedWarehouseId) {
            $totalValueQuery->where('stocks.warehouse_id', $selectedWarehouseId);
        }
        
        $totalValue = $totalValueQuery->sum(DB::raw('stocks.qty * products.avg_price'));

        // 2b. Total Low Stock count
        $lowStockTotalQuery = Product::where('status', 'active');
        if ($selectedWarehouseId) {
            $lowStockTotalQuery->whereHas('stocks', function ($q) use ($selectedWarehouseId) {
                $q->where('warehouse_id', $selectedWarehouseId)
                  ->whereRaw('qty <= products.min_stock');
            });
        } else {
            $lowStockTotalQuery->whereHas('stocks', function ($q) {
                $q->select('product_id')
                  ->groupBy('product_id')
                  ->havingRaw('SUM(qty) <= products.min_stock');
            });
        }
        $totalLowStock = $lowStockTotalQuery->count();

        // 2c. Total Warehouses
        $totalWarehouses = Warehouse::where('status', 'active')->count();

        // 2d. Total Suppliers
        $totalSuppliers = DB::table('suppliers')->where('status', 'active')->count();

        // 2e. Total Transactions (all logs)
        $totalLedgersQuery = StockLedger::query();
        if ($selectedWarehouseId) {
            $totalLedgersQuery->where('warehouse_id', $selectedWarehouseId);
        }
        $totalTransactions = $totalLedgersQuery->count();

        // 2f. Total Physical Qty of Stock
        $totalQtyStockQuery = Stock::query();
        if ($selectedWarehouseId) {
            $totalQtyStockQuery->where('warehouse_id', $selectedWarehouseId);
        }
        $totalQty = (float) $totalQtyStockQuery->sum('qty');

        // 2g. Total Draft Transactions
        $totalDrafts = 0;
        if ($selectedWarehouseId) {
            $totalDrafts += StockIn::where('status', 'draft')->where('warehouse_id', $selectedWarehouseId)->count();
            $totalDrafts += StockOut::where('status', 'draft')->where('warehouse_id', $selectedWarehouseId)->count();
            $totalDrafts += StockTransfer::where('status', 'draft')->where(function($q) use ($selectedWarehouseId) {
                $q->where('from_warehouse_id', $selectedWarehouseId)->orWhere('to_warehouse_id', $selectedWarehouseId);
            })->count();
            $totalDrafts += StockOpname::where('status', 'draft')->where('warehouse_id', $selectedWarehouseId)->count();
            $totalDrafts += StockReturn::where('status', 'draft')->where('warehouse_id', $selectedWarehouseId)->count();
        } else {
            $totalDrafts += StockIn::where('status', 'draft')->count();
            $totalDrafts += StockOut::where('status', 'draft')->count();
            $totalDrafts += StockTransfer::where('status', 'draft')->count();
            $totalDrafts += StockOpname::where('status', 'draft')->count();
            $totalDrafts += StockReturn::where('status', 'draft')->count();
        }

        // 3. Low stock items (stock qty <= min_stock)
        // Group stock by product if no warehouse is selected, otherwise filter by warehouse
        $lowStockQuery = Product::where('status', 'active')
            ->with(['baseUnit', 'defaultWarehouse']);
            
        if ($selectedWarehouseId) {
            $lowStockQuery->whereHas('stocks', function ($q) use ($selectedWarehouseId) {
                $q->where('warehouse_id', $selectedWarehouseId)
                  ->whereRaw('qty <= products.min_stock');
            });
        } else {
            // Aggregate all stocks for the product and compare with min_stock
            $lowStockQuery->whereHas('stocks', function ($q) {
                $q->select('product_id')
                  ->groupBy('product_id')
                  ->havingRaw('SUM(qty) <= products.min_stock');
            });
        }
        
        $lowStockItems = $lowStockQuery->limit(8)->get()->map(function ($product) use ($selectedWarehouseId) {
            $currentStock = $selectedWarehouseId
                ? $product->stocks()->where('warehouse_id', $selectedWarehouseId)->sum('qty')
                : $product->stocks()->sum('qty');
                
            return [
                'id' => $product->id,
                'sku' => $product->sku,
                'name' => $product->name,
                'min_stock' => $product->min_stock,
                'current_stock' => $currentStock,
                'unit' => $product->baseUnit->symbol ?? 'pcs',
                'warehouse' => $product->defaultWarehouse?->name ?? 'Tidak Ada',
            ];
        });

        // 4. Recent Transactions (last 5)
        $recentTransactionsQuery = StockLedger::with(['product', 'warehouse'])
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc');

        if ($selectedWarehouseId) {
            $recentTransactionsQuery->where('warehouse_id', $selectedWarehouseId);
        }

        $recentTransactions = $recentTransactionsQuery->limit(5)->get()->map(function ($ledger) {
            return [
                'id' => $ledger->id,
                'date' => $ledger->transaction_date->format('Y-m-d'),
                'product_name' => $ledger->product->name ?? 'Deleted Product',
                'sku' => $ledger->product->sku ?? '',
                'warehouse_name' => $ledger->warehouse->name ?? 'Deleted Warehouse',
                'type' => $ledger->transaction_type,
                'qty_in' => $ledger->qty_in,
                'qty_out' => $ledger->qty_out,
                'balance' => $ledger->balance,
            ];
        });

        // 5. In vs Out Chart Data (Last 30 Days)
        $chartData = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $chartData[$date] = [
                'date' => now()->subDays($i)->format('d M'),
                'in' => 0,
                'out' => 0,
            ];
        }

        $chartQuery = StockLedger::where('transaction_date', '>=', now()->subDays(29)->format('Y-m-d'));
        if ($selectedWarehouseId) {
            $chartQuery->where('warehouse_id', $selectedWarehouseId);
        }
        
        $chartLedgerData = $chartQuery->select('transaction_date', DB::raw('SUM(qty_in) as total_in'), DB::raw('SUM(qty_out) as total_out'))
            ->groupBy('transaction_date')
            ->get();

        foreach ($chartLedgerData as $data) {
            $formattedDate = $data->transaction_date->format('Y-m-d');
            if (isset($chartData[$formattedDate])) {
                $chartData[$formattedDate]['in'] = (float) $data->total_in;
                $chartData[$formattedDate]['out'] = (float) $data->total_out;
            }
        }

        return Inertia::render('Dashboard', [
            'stats' => [
                'total_sku' => $totalSku,
                'total_value' => (float) $totalValue,
                'total_low_stock' => $totalLowStock,
                'total_warehouses' => $totalWarehouses,
                'total_suppliers' => $totalSuppliers,
                'total_transactions' => $totalTransactions,
                'total_qty' => $totalQty,
                'total_drafts' => $totalDrafts,
            ],
            'warehouses' => $warehouses,
            'selected_warehouse_id' => $selectedWarehouseId,
            'low_stock_items' => $lowStockItems,
            'recent_transactions' => $recentTransactions,
            'chart_data' => array_values($chartData),
        ]);
    }
}
