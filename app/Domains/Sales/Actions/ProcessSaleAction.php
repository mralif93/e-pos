<?php

namespace App\Domains\Sales\Actions;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductOutletPrice;
use App\Domains\Sales\DTOs\SaleData;
use App\Domains\Sales\Events\SaleCompleted;
use App\Domains\Inventory\Actions\RecordStockMovementAction;
use Illuminate\Support\Facades\DB;
use Exception;

class ProcessSaleAction
{
    /**
     * Execute the core sale transaction with pessimistic locking.
     *
     * @throws Exception
     */
    public function execute(SaleData $data): Sale
    {
        $stockMovementAction = new RecordStockMovementAction();

        return DB::transaction(function () use ($data, $stockMovementAction) {
            
            // 1. Validate Stock with Pessimistic Locking
            $stockErrors = [];
            foreach ($data->items as $item) {
                // Use lockForUpdate() on the ProductOutletPrice record to prevent concurrent modifications
                $outletPriceQuery = ProductOutletPrice::where('product_id', $item['product_id'])
                    ->where('outlet_id', $data->outletId);

                if (DB::getDriverName() !== 'sqlite') {
                    $outletPriceQuery->lockForUpdate();
                }

                $outletPrice = $outletPriceQuery->first();

                $availableStock = $outletPrice ? $outletPrice->stock_level : null;

                // If not found in ProductOutletPrice, fallback to global Product stock level
                if ($availableStock === null) {
                    $productQuery = Product::where('id', $item['product_id']);
                    if (DB::getDriverName() !== 'sqlite') {
                        $productQuery->lockForUpdate();
                    }
                    $product = $productQuery->first();
                    $availableStock = $product ? $product->stock_level : 0;
                }

                if ($availableStock !== null && $availableStock < $item['quantity']) {
                    $productName = Product::find($item['product_id'])->name;
                    $stockErrors[] = "Insufficient stock for {$productName}. Available: {$availableStock}";
                }
            }

            if (!empty($stockErrors)) {
                throw new Exception(implode(' | ', $stockErrors));
            }

            // 2. Create Sale Record
            $sale = Sale::create([
                'outlet_id' => $data->outletId,
                'user_id' => $data->userId,
                'customer_id' => $data->customerId,
                'total_amount' => $data->totalAmount,
                'tax_amount' => $data->taxAmount,
                'discount_amount' => $data->discountAmount,
                'discount_reason' => $data->discountReason,
                'status' => $data->status,
            ]);

            // 3. Attach Sale Items and Deduct Inventory via Ledger
            foreach ($data->items as $item) {
                $product = Product::findOrFail($item['product_id']);

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                ]);

                // Record the "OUT" movement in the central Stock Ledger
                $stockMovementAction->execute(
                    product: $product,
                    quantity: -$item['quantity'], // Negative for deductions
                    type: 'SALE',
                    outletId: $data->outletId,
                    reference: $sale,
                    unitPrice: (float) $item['price']
                );
            }

            // 4. Attach Payments
            foreach ($data->payments as $payment) {
                Payment::create([
                    'sale_id' => $sale->id,
                    'amount' => $payment['amount'],
                    'payment_method' => $payment['payment_method'],
                ]);
            }

            // 5. Fire Event for decoupled side-effects
            // This now includes loyalty point rewards via the SaleCompleted listener
            event(new SaleCompleted($sale, $data->pointsToRedeem));

            return $sale;
        });
    }
}
