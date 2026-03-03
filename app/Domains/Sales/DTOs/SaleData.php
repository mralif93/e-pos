<?php

namespace App\Domains\Sales\DTOs;

use Illuminate\Http\Request;

readonly class SaleData
{
    /**
     * @param array<int, array{product_id: int, quantity: int, price: float}> $items
     * @param array<int, array{amount: float, payment_method: string}> $payments
     */
    public function __construct(
        public int $outletId,
        public int $userId,
        public array $items,
        public array $payments,
        public float $totalAmount,
        public float $taxAmount = 0,
        public float $discountAmount = 0,
        public ?string $discountReason = null,
        public ?int $customerId = null,
        public string $status = 'completed',
        public int $pointsToRedeem = 0,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            outletId: (int) $request->input('outlet_id'),
            userId: (int) $request->input('user_id'),
            items: (array) $request->input('items'),
            payments: (array) $request->input('payments'),
            totalAmount: (float) $request->input('total_amount'),
            taxAmount: (float) ($request->input('tax_amount') ?? 0),
            discountAmount: (float) ($request->input('discount_amount') ?? 0),
            discountReason: $request->input('discount_reason'),
            customerId: $request->input('customer_id') ? (int) $request->input('customer_id') : null,
            status: $request->input('status', 'completed'),
            pointsToRedeem: (int) ($request->input('points_to_redeem') ?? 0),
        );
    }
}
