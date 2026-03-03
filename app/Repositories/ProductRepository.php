<?php

namespace App\Repositories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class ProductRepository
{
    /**
     * Get all active products for a specific outlet with pricing and stock.
     */
    public function getActiveForOutlet(int $outletId, ?string $query = null, ?int $categoryId = null): Collection
    {
        $cacheKey = "outlet_{$outletId}_products_" . md5($query . $categoryId);

        // We use short-lived cache for POS data to keep it responsive but fresh
        return Cache::remember($cacheKey, 60, function () use ($outletId, $query, $categoryId) {
            return Product::where('is_active', true)
                ->when($outletId, function ($q) use ($outletId) {
                    $q->whereHas('prices', fn($p) => $p->where('outlet_id', $outletId));
                })
                ->with(['prices' => fn($q) => $q->where('outlet_id', $outletId), 'modifiers.items'])
                ->when($query, function ($q) use ($query) {
                    $q->where(fn($sq) => 
                        $sq->where('name', 'like', "%$query%")
                           ->orWhere('sku', 'like', "%$query%")
                           ->orWhere('barcode', 'like', "%$query%")
                    );
                })
                ->when($categoryId, fn($q) => $q->where('category_id', $categoryId))
                ->get()
                ->map(function ($p) use ($outletId) {
                    // Flatten the outlet-specific price/stock into the object for convenience
                    $outletData = $p->prices->firstWhere('outlet_id', $outletId);
                    $p->price = $outletData->price ?? $p->price;
                    $p->stock_level = $outletData->stock_level ?? $p->stock_level;
                    return $p;
                });
        });
    }

    /**
     * Find a single product for an outlet.
     */
    public function findForOutlet(int $productId, int $outletId): ?Product
    {
        $product = Product::with(['prices' => fn($q) => $q->where('outlet_id', $outletId)])
            ->find($productId);

        if ($product) {
            $outletData = $product->prices->firstWhere('outlet_id', $outletId);
            $product->price = $outletData->price ?? $product->price;
            $product->stock_level = $outletData->stock_level ?? $product->stock_level;
        }

        return $product;
    }
}
