<?php

namespace App\Services;

use App\Models\OfflineSaleDraft;
use App\Models\OfflineSaleItem;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Payment;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use App\Domains\Sales\DTOs\SaleData;
use App\Domains\Sales\Actions\ProcessSaleAction;

class OfflineSaleService
{
    public function saveDraft(array $data): OfflineSaleDraft
    {
        $cartData = $data['cart_data'] ?? [];
        $items = $cartData['items'] ?? [];
        
        unset($cartData['items']);

        $draft = OfflineSaleDraft::create([
            'id' => $data['id'] ?? (string) \Illuminate\Support\Str::uuid(),
            'uuid' => $data['uuid'] ?? ($data['id'] ?? (string) \Illuminate\Support\Str::uuid()),
            'user_id' => $data['user_id'],
            'outlet_id' => $data['outlet_id'],
            'customer_id' => $data['customer_id'] ?? null,
            'cart_data' => $cartData,
            'total_amount' => $data['total_amount'],
            'tax_amount' => $data['tax_amount'] ?? 0,
            'discount_amount' => $data['discount_amount'] ?? 0,
            'discount_reason' => $data['discount_reason'] ?? null,
            'payments' => $data['payments'] ?? [],
            'points_earned' => $data['points_earned'] ?? 0,
            'points_redeemed' => $data['points_redeemed'] ?? 0,
            'discount_from_points' => $data['discount_from_points'] ?? 0,
            'local_created_at' => $data['local_created_at'] ?? now(),
            'synced' => false,
        ]);

        foreach ($items as $item) {
            OfflineSaleItem::create([
                'draft_id' => $draft->id,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
                'notes' => $item['notes'] ?? null,
            ]);
        }

        return $draft;
    }

    public function syncDraft(OfflineSaleDraft $draft): Sale
    {
        // Check if already synced (prevent double sync)
        if ($draft->synced) {
            return $draft->syncedSale;
        }

        // Convert Draft to SaleData DTO
        $items = $draft->saleItems->map(function($item) {
            return [
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'price' => $item->price,
            ];
        })->toArray();

        $saleData = new SaleData(
            outletId: $draft->outlet_id,
            userId: $draft->user_id,
            items: $items,
            payments: $draft->payments,
            totalAmount: (float) $draft->total_amount,
            taxAmount: (float) $draft->tax_amount,
            discountAmount: (float) $draft->discount_amount,
            discountReason: $draft->discount_reason,
            customerId: $draft->customer_id,
            status: 'completed',
            pointsToRedeem: $draft->points_redeemed
        );

        // Execute core action
        $action = new ProcessSaleAction();
        $sale = $action->execute($saleData);

        // Map the UUID from draft to the newly created sale
        $sale->update(['uuid' => $draft->uuid]);

        $draft->markAsSynced($sale->id);

        return $sale;
    }

    public function syncAllPendingDrafts(): array
    {
        $results = [
            'success' => [],
            'failed' => [],
        ];

        $pendingDrafts = OfflineSaleDraft::getUnsyncedDrafts()->get();

        foreach ($pendingDrafts as $draft) {
            try {
                $sale = $this->syncDraft($draft);
                $results['success'][] = [
                    'draft_id' => $draft->id,
                    'sale_id' => $sale->id,
                ];
            } catch (\Exception $e) {
                $results['failed'][] = [
                    'draft_id' => $draft->id,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    public function getPendingCount(): int
    {
        return OfflineSaleDraft::where('synced', false)->count();
    }

    public function getPendingDrafts()
    {
        return OfflineSaleDraft::where('synced', false)
            ->with(['user', 'customer', 'saleItems.product'])
            ->orderBy('local_created_at', 'asc')
            ->get();
    }
}
