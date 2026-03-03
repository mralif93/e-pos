<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\StockLedger;
use App\Models\Outlet;
use App\Domains\Security\Actions\LogAuditAction;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ManagerApiController extends Controller
{
    /**
     * Get real-time sales performance across all outlets.
     */
    public function crossOutletPerformance()
    {
        $this->authorizeManager();

        $performance = Outlet::withCount(['sales' => function($query) {
            $query->where('status', 'completed')->whereDate('created_at', Carbon::today());
        }])
        ->withSum(['sales' => function($query) {
            $query->where('status', 'completed')->whereDate('created_at', Carbon::today());
        }], 'total_amount')
        ->get()
        ->map(fn($outlet) => [
            'id' => $outlet->id,
            'name' => $outlet->name,
            'today_sales_count' => $outlet->sales_count,
            'today_revenue' => (float) ($outlet->sales_sum_total_amount ?? 0),
        ]);

        return response()->json(['success' => true, 'data' => $performance]);
    }

    /**
     * Identify inventory that hasn't moved in X days (Dead Stock).
     */
    public function deadStockReport(Request $request)
    {
        $this->authorizeManager();
        $days = $request->input('days', 30);
        $outletId = $request->input('outlet_id');

        $deadStock = DB::table('products')
            ->leftJoin('sale_items', 'products.id', '=', 'sale_items.product_id')
            ->leftJoin('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->select('products.id', 'products.name', 'products.sku', DB::raw('MAX(sales.created_at) as last_sold_at'))
            ->when($outletId, fn($q) => $q->where('sales.outlet_id', $outletId))
            ->groupBy('products.id', 'products.name', 'products.sku')
            ->havingRaw('last_sold_at < ? OR last_sold_at IS NULL', [Carbon::now()->subDays($days)])
            ->limit(20)
            ->get();

        return response()->json(['success' => true, 'data' => $deadStock]);
    }

    /**
     * Profit Margin Analysis using the Stock Ledger costs.
     */
    public function marginAnalysis(Request $request)
    {
        $this->authorizeManager();
        $outletId = $request->input('outlet_id');
        
        $margins = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('stock_ledger', function($join) {
                $join->on('sale_items.product_id', '=', 'stock_ledger.product_id')
                     ->on('sales.id', '=', 'stock_ledger.reference_id')
                     ->where('stock_ledger.reference_type', '=', 'App\Models\Sale');
            })
            ->select(
                'sale_items.product_id',
                DB::raw('SUM(sale_items.quantity * sale_items.price) as revenue'),
                DB::raw('SUM(ABS(stock_ledger.quantity) * stock_ledger.unit_cost) as total_cost'),
                DB::raw('SUM(sale_items.quantity * sale_items.price) - SUM(ABS(stock_ledger.quantity) * stock_ledger.unit_cost) as profit')
            )
            ->when($outletId, fn($q) => $q->where('sales.outlet_id', $outletId))
            ->where('sales.status', 'completed')
            ->groupBy('sale_items.product_id')
            ->get()
            ->map(fn($item) => [
                'product_id' => $item->product_id,
                'revenue' => (float) $item->revenue,
                'cost' => (float) $item->total_cost,
                'profit' => (float) $item->profit,
                'margin_percentage' => $item->revenue > 0 ? ($item->profit / $item->revenue) * 100 : 0
            ]);

        return response()->json(['success' => true, 'data' => $margins]);
    }

    protected function authorizeManager()
    {
        if (!in_array(auth()->user()->role, ['Manager', 'Admin', 'Super Admin'])) {
            abort(403, 'Unauthorized. Manager access required.');
        }
    }
}
