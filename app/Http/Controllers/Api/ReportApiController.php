<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\Product;
use App\Models\StockLedger;
use App\Domains\Inventory\Services\StockValuationService;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportApiController extends Controller
{
    /**
     * Get a high-level sales dashboard for the terminal.
     */
    public function salesSummary(Request $request)
    {
        $outletId = auth()->user()->outlet_id;
        $range = $request->input('range', 'today'); // 'today', 'week', 'month'
        
        $query = Sale::where('outlet_id', $outletId)->where('status', 'completed');

        if ($range === 'today') {
            $query->whereDate('created_at', Carbon::today());
        } elseif ($range === 'week') {
            $query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
        } elseif ($range === 'month') {
            $query->whereMonth('created_at', Carbon::now()->month)
                  ->whereYear('created_at', Carbon::now()->year);
        }

        $summary = [
            'total_sales_amount' => (float) $query->sum('total_amount'),
            'total_sales_count' => $query->count(),
            'total_tax' => (float) $query->sum('tax_amount'),
            'total_discount' => (float) $query->sum('discount_amount'),
            'average_order_value' => $query->count() > 0 ? $query->sum('total_amount') / $query->count() : 0,
        ];

        return response()->json(['success' => true, 'data' => $summary]);
    }

    /**
     * Get inventory valuation for the terminal.
     */
    public function inventoryValue(Request $request)
    {
        $outletId = auth()->user()->outlet_id;
        $valuationService = new StockValuationService();
        
        $products = Product::whereHas('prices', function($q) use ($outletId) {
            $q->where('outlet_id', $outletId);
        })->get();

        $totalFifoValue = 0;
        $totalAvcoValue = 0;

        foreach ($products as $product) {
            $totalFifoValue += $valuationService->getFifoValue($product->id, $outletId);
            $totalAvcoValue += $valuationService->getAverageCostValue($product->id, $outletId);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'fifo_total_value' => round($totalFifoValue, 2),
                'avco_total_value' => round($totalAvcoValue, 2),
                'product_count' => $products->count(),
                'valuation_method' => config('services.inventory.valuation_method', 'FIFO'),
            ]
        ]);
    }

    /**
     * Get top-selling products.
     */
    public function topSellingProducts(Request $request)
    {
        $outletId = auth()->user()->outlet_id;
        $limit = $request->input('limit', 5);

        $topProducts = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->where('sales.outlet_id', $outletId)
            ->where('sales.status', 'completed')
            ->select('products.name', DB::raw('SUM(sale_items.quantity) as total_sold'), DB::raw('SUM(sale_items.quantity * sale_items.price) as total_revenue'))
            ->groupBy('products.id', 'products.name')
            ->orderBy('total_sold', 'desc')
            ->limit($limit)
            ->get();

        return response()->json(['success' => true, 'data' => $topProducts]);
    }
}
