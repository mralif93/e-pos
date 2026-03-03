<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\ProcessSaleRequest;
use App\Models\Product;
use App\Models\Category;
use App\Models\Sale;
use App\Models\Customer;
use App\Models\User;
use App\Models\Shift;
use App\Models\Outlet;
use App\Domains\Sales\DTOs\SaleData;
use App\Domains\Sales\Actions\ProcessSaleAction;
use App\Domains\Security\Actions\LogAuditAction;
use App\Domains\Customers\Services\LoyaltyService;
use App\Services\OfflineSaleService;
use App\Services\ShiftService;
use Illuminate\Support\Facades\DB;
use App\Repositories\ProductRepository;
use App\Repositories\SaleRepository;

class PosApiController extends Controller
{
    public function __construct(
        protected ProductRepository $productRepository,
        protected SaleRepository $saleRepository
    ) {}

    /**
     * Search products for the POS terminal.
     */
    public function searchProducts(Request $request)
    {
        $user = auth()->user();
        $products = $this->productRepository->getActiveForOutlet(
            $user->outlet_id,
            $request->input('query'),
            $request->input('category_id')
        );

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    /**
     * Get all categories with active products.
     */
    public function getCategories()
    {
        $outletId = auth()->user()->outlet_id;
        $categories = Category::whereHas('products.prices', fn($q) => $q->where('outlet_id', $outletId))
            ->orderBy('sort_order')->get();

        return response()->json(['success' => true, 'data' => $categories]);
    }

    /**
     * Get sales history for the current outlet.
     */
    public function history(Request $request)
    {
        $user = auth()->user();
        $filters = $request->only(['search', 'date']);
        $sales = $this->saleRepository->getHistoryForOutlet($user->outlet_id, $filters);

        return response()->json([
            'success' => true,
            'data' => $sales->items(),
            'current_page' => $sales->currentPage(),
            'last_page' => $sales->lastPage(),
            'total' => $sales->total(),
        ]);
    }

    /**
     * Process a sale (Unified logic for online/offline terminal).
     */
    public function processSale(ProcessSaleRequest $request)
    {
        try {
            $saleData = SaleData::fromRequest($request);
            $action = new ProcessSaleAction();
            $sale = $action->execute($saleData);

            return response()->json([
                'success' => true,
                'message' => 'Sale processed',
                'data' => $sale->load(['saleItems.product', 'payments'])
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * Void a sale (Manager authorization required).
     */
    public function voidSale(Request $request, $id)
    {
        $request->validate(['pin' => 'required|string|size:4']);
        
        $manager = User::where('pin', $request->pin)
            ->whereIn('role', ['Manager', 'Admin', 'Super Admin'])
            ->where('outlet_id', auth()->user()->outlet_id) // Ensure manager belongs to this outlet
            ->first();

        if (!$manager) {
            return response()->json(['success' => false, 'message' => 'Invalid Manager PIN'], 403);
        }

        $sale = Sale::where('id', $id)->where('outlet_id', auth()->user()->outlet_id)->firstOrFail();
        
        DB::transaction(function() use ($sale, $manager) {
            $sale->update(['status' => 'void']);
            (new LogAuditAction())->execute('VOID_SALE', "Sale #{$sale->id} voided by {$manager->name}");
        });

        return response()->json(['success' => true, 'message' => 'Sale voided']);
    }

    /**
     * Manage Shifts (Open/Close).
     */
    public function currentShift()
    {
        $user = auth()->user();
        $shiftService = new ShiftService();
        $shift = $shiftService->getCurrentShift($user->outlet_id, $user->id);

        return response()->json([
            'success' => true,
            'data' => [
                'shift' => $shift,
                'summary' => $shift ? $shiftService->getShiftSalesSummary($shift) : null
            ]
        ]);
    }

    /**
     * Customer Loyalty Data.
     */
    public function customerPoints($id)
    {
        $customer = Customer::findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'phone' => $customer->phone,
                'loyalty_points' => $customer->loyalty_points,
                'value' => $customer->getPointsValue(),
                'tier' => $customer->loyalty_tier
            ]
        ]);
    }

    /**
     * Calculate point redemption discount.
     */
    public function calculatePointsRedemption(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'subtotal' => 'required|numeric|min:0',
            'points_to_redeem' => 'required|integer|min:0',
        ]);

        $customer = Customer::findOrFail($request->customer_id);
        $loyaltyService = new LoyaltyService();

        $maxRedeemable = $loyaltyService->calculateMaxRedeemablePoints($customer, $request->subtotal);
        $requestedPoints = min($request->points_to_redeem, $maxRedeemable);
        $discount = $loyaltyService->calculateDiscountFromPoints($requestedPoints, $customer->loyalty_tier);

        return response()->json([
            'success' => true,
            'data' => [
                'requested_points' => $request->points_to_redeem,
                'redeemable_points' => $requestedPoints,
                'max_redeemable_points' => $maxRedeemable,
                'discount_amount' => $discount,
                'remaining_points' => $customer->loyalty_points - $requestedPoints,
            ]
        ]);
    }

    /**
     * Search customers by name or phone.
     */
    public function searchCustomers(Request $request)
    {
        $query = $request->input('query');
        $customers = Customer::where('name', 'like', "%$query%")
            ->orWhere('phone', 'like', "%$query%")
            ->limit(10)->get();

        return response()->json(['success' => true, 'data' => $customers]);
    }

    /**
     * Create a new customer from the terminal.
     */
    public function createCustomer(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20|unique:customers,phone',
            'email' => 'nullable|email|unique:customers,email',
        ]);

        $customer = Customer::create(array_merge($validated, ['created_by' => auth()->id()]));
        return response()->json(['success' => true, 'data' => $customer], 201);
    }

    /**
     * Verify a coupon code.
     */
    public function verifyCoupon(Request $request)
    {
        $request->validate(['code' => 'required|string', 'amount' => 'required|numeric']);
        $coupon = \App\Models\Coupon::where('code', $request->code)->valid()->first();

        if (!$coupon || !$coupon->isValidForAmount($request->amount)) {
            return response()->json(['success' => false, 'message' => 'Invalid or ineligible coupon'], 422);
        }

        return response()->json(['success' => true, 'data' => [
            'code' => $coupon->code,
            'discount' => $coupon->calculateDiscount($request->amount)
        ]]);
    }

    /**
     * Detailed Shift Management.
     */
    public function openShift(Request $request)
    {
        $request->validate(['opening_cash' => 'required|numeric']);
        try {
            $shift = (new ShiftService())->openShift(auth()->user()->outlet_id, auth()->id(), $request->opening_cash);
            return response()->json(['success' => true, 'data' => $shift]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function closeShift(Request $request, $id)
    {
        $request->validate([
            'closing_cash' => 'required|numeric',
            'pin' => 'required|string|size:4'
        ]);

        $manager = User::where('pin', $request->pin)
            ->whereIn('role', ['Manager', 'Admin', 'Super Admin'])
            ->where('outlet_id', auth()->user()->outlet_id)
            ->first();

        if (!$manager) {
            return response()->json(['success' => false, 'message' => 'Invalid Manager PIN for authorization'], 403);
        }

        $shift = Shift::findOrFail($id);
        $closedShift = (new ShiftService())->closeShift($shift, array_merge($request->only('closing_cash', 'notes'), ['closed_by' => $manager->id]));
        
        return response()->json(['success' => true, 'data' => $closedShift]);
    }

    /**
     * Get shift history for the current outlet.
     */
    public function getShiftHistory(Request $request)
    {
        $user = auth()->user();
        $shiftService = new ShiftService();

        $startDate = $request->filled('start_date') ? \Carbon\Carbon::parse($request->start_date) : null;
        $endDate = $request->filled('end_date') ? \Carbon\Carbon::parse($request->end_date) : null;

        $shifts = $shiftService->getShiftHistory($user->outlet_id, $startDate, $endDate);

        return response()->json(['success' => true, 'data' => $shifts]);
    }

    /**
     * Inventory Transfers between outlets.
     */
    public function getPendingTransfers()
    {
        $outletId = auth()->user()->outlet_id;
        $transfers = \App\Models\InventoryTransfer::with(['fromOutlet', 'items.product'])
            ->where('to_outlet_id', $outletId)
            ->whereIn('status', ['pending', 'in_transit'])->get();

        return response()->json(['success' => true, 'data' => $transfers]);
    }

    public function receiveTransfer(Request $request, $id)
    {
        $transfer = \App\Models\InventoryTransfer::findOrFail($id);
        $transfer->receive(auth()->id());
        return response()->json(['success' => true, 'message' => 'Inventory received and updated']);
    }

    /**
     * Create a new inventory transfer request.
     */
    public function createTransfer(Request $request)
    {
        $request->validate([
            'to_outlet_id' => 'required|exists:outlets,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        $user = auth()->user();

        if ($request->to_outlet_id == $user->outlet_id) {
            return response()->json(['success' => false, 'message' => 'Cannot transfer to the same outlet'], 422);
        }

        DB::beginTransaction();
        try {
            $transfer = \App\Models\InventoryTransfer::create([
                'from_outlet_id' => $user->outlet_id,
                'to_outlet_id' => $request->to_outlet_id,
                'requested_by' => $user->id,
                'notes' => $request->notes,
                'status' => 'pending',
            ]);

            foreach ($request->items as $item) {
                \App\Models\InventoryTransferItem::create([
                    'inventory_transfer_id' => $transfer->id,
                    'product_id' => $item['product_id'],
                    'quantity_requested' => $item['quantity'],
                ]);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Transfer request created', 'data' => $transfer->load('items.product')], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Get list of active outlets.
     */
    public function getOutlets()
    {
        $outlets = Outlet::where('is_active', true)->get(['id', 'name', 'outlet_code', 'address', 'phone']);
        return response()->json(['success' => true, 'data' => $outlets]);
    }

    /**
     * Get low stock alerts for the current outlet.
     */
    public function getLowStockAlerts()
    {
        $user = auth()->user();
        $lowStockProducts = \App\Models\ProductOutletPrice::with('product')
            ->where('outlet_id', $user->outlet_id)
            ->whereRaw('stock_level <= (SELECT low_stock_threshold FROM products WHERE products.id = product_outlet_prices.product_id AND products.low_stock_threshold > 0)')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $lowStockProducts->map(fn($item) => [
                'id' => $item->product_id,
                'name' => $item->product->name ?? 'Unknown',
                'stock_level' => $item->stock_level,
                'threshold' => $item->product->low_stock_threshold ?? 0,
            ])
        ]);
    }

    /**
     * DuitNow QR Payment Integration.
     */
    public function generateDuitNowQR(Request $request)
    {
        $request->validate(['amount' => 'required|numeric|min:1']);
        $duitNowService = new \App\Services\DuitNowQRService();

        if (!$duitNowService->isConfigured()) {
            return response()->json(['success' => false, 'message' => 'DuitNow QR not configured'], 503);
        }

        $qrData = $duitNowService->generateDynamicQR(
            (float) $request->amount,
            $request->order_id ?? \App\Services\DuitNowQRService::generateMerchantOrderId()
        );

        return response()->json(['success' => true, 'data' => $qrData]);
    }

    public function verifyDuitNowPayment(Request $request)
    {
        $request->validate(['order_id' => 'required|string', 'amount' => 'required|numeric']);
        $duitNowService = new \App\Services\DuitNowQRService();
        $result = $duitNowService->verifyPayment($request->order_id, (float) $request->amount);

        return response()->json(['success' => true, 'data' => $result]);
    }
}
