<?php

namespace App\Domains\Inventory\Actions;

use App\Models\StockLedger;
use App\Models\Product;
use App\Models\ProductOutletPrice;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Exception;

class RecordStockMovementAction
{
    /**
     * Records a stock movement in the ledger and updates current stock levels.
     *
     * @param Product $product
     * @param float $quantity        Positive for additions, negative for deductions.
     * @param string $type           Movement type (e.g., 'SALE', 'PURCHASE')
     * @param int|null $outletId     Affected outlet
     * @param Model|null $reference  Reference model for the movement
     * @param string|null $notes
     * @param float|null $unitCost   Override the product's current cost
     * @param float|null $unitPrice  Sale price at the time of movement
     * @return StockLedger
     * @throws Exception
     */
    public function execute(
        Product $product,
        float $quantity,
        string $type,
        ?int $outletId = null,
        ?Model $reference = null,
        ?string $notes = null,
        ?float $unitCost = null,
        ?float $unitPrice = null
    ): StockLedger {
        return DB::transaction(function () use ($product, $quantity, $type, $outletId, $reference, $notes, $unitCost, $unitPrice) {
            
            // 1. Determine current cost (fallback to product's defined cost if not provided)
            $cost = $unitCost ?? (float) $product->cost;
            $price = $unitPrice ?? (float) $product->price;

            // 2. Create Ledger Entry
            $ledgerEntry = StockLedger::create([
                'product_id' => $product->id,
                'outlet_id' => $outletId,
                'user_id' => auth()->id(),
                'quantity' => $quantity,
                'unit_cost' => $cost,
                'unit_price' => $price,
                'type' => $type,
                'reference_type' => $reference ? get_class($reference) : null,
                'reference_id' => $reference ? $reference->getKey() : null,
                'notes' => $notes,
            ]);

            // 3. Update Current Stock Level in Outlet or Global
            // We use pessimistic locking here again if we're not using it in the caller
            if ($outletId) {
                $outletPrice = ProductOutletPrice::where('product_id', $product->id)
                    ->where('outlet_id', $outletId)
                    ->lockForUpdate()
                    ->first();
                
                if ($outletPrice) {
                    $outletPrice->increment('stock_level', $quantity);
                } else {
                    // Create an outlet price record if it doesn't exist
                    ProductOutletPrice::create([
                        'product_id' => $product->id,
                        'outlet_id' => $outletId,
                        'price' => $product->price,
                        'stock_level' => $quantity,
                    ]);
                }
            } else {
                // Global update
                $product->lockForUpdate()->increment('stock_level', $quantity);
            }

            return $ledgerEntry;
        });
    }
}
