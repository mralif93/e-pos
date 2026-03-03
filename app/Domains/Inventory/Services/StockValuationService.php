<?php

namespace App\Domains\Inventory\Services;

use App\Models\StockLedger;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class StockValuationService
{
    /**
     * Calculate inventory value using Weighted Average Cost (AVCO)
     */
    public function getAverageCostValue(int $productId, ?int $outletId = null): float
    {
        $query = StockLedger::where('product_id', $productId);
        
        if ($outletId) {
            $query->where('outlet_id', $outletId);
        }

        // Only consider "IN" movements (Purchases, Returns, etc.) for cost basis
        $totalInQty = (float) $query->clone()->where('quantity', '>', 0)->sum('quantity');
        $totalInCost = (float) $query->clone()->where('quantity', '>', 0)
            ->select(DB::raw('SUM(quantity * unit_cost) as total_cost'))
            ->value('total_cost');

        if ($totalInQty === 0.0) {
            $product = Product::find($productId);
            return $product ? (float) $product->cost : 0.0;
        }

        return $totalInCost / $totalInQty;
    }

    /**
     * Calculate current inventory value using FIFO (First-In, First-Out)
     */
    public function getFifoValue(int $productId, ?int $outletId = null): float
    {
        // 1. Get current physical stock level
        $currentStock = $this->getCurrentStockLevel($productId, $outletId);
        
        if ($currentStock <= 0) {
            return 0.0;
        }

        // 2. Get recent "IN" movements in reverse chronological order
        // We work backward from the most recent purchases until we account for all current stock
        $inMovements = StockLedger::where('product_id', $productId)
            ->where('quantity', '>', 0)
            ->when($outletId, fn($q) => $q->where('outlet_id', $outletId))
            ->latest()
            ->get();

        $remainingToValue = $currentStock;
        $totalValue = 0.0;

        foreach ($inMovements as $movement) {
            $qtyToTake = min($remainingToValue, (float) $movement->quantity);
            $totalValue += $qtyToTake * (float) $movement->unit_cost;
            $remainingToValue -= $qtyToTake;

            if ($remainingToValue <= 0) {
                break;
            }
        }

        // If we still have stock but no matching "IN" movements (e.g., initial migration data),
        // fallback to the current product cost for the remainder.
        if ($remainingToValue > 0) {
            $product = Product::find($productId);
            $totalValue += $remainingToValue * ($product ? (float) $product->cost : 0.0);
        }

        return $totalValue;
    }

    private function getCurrentStockLevel(int $productId, ?int $outletId = null): float
    {
        $query = StockLedger::where('product_id', $productId);
        if ($outletId) {
            $query->where('outlet_id', $outletId);
        }
        return (float) $query->sum('quantity');
    }
}
