<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\ProcessSaleRequest;
use App\Models\Product;
use App\Models\Category;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Payment;
use App\Models\Customer;
use App\Models\User;
use App\Models\Shift;
use App\Models\InventoryTransfer;
use App\Models\InventoryTransferItem;
use App\Models\Outlet;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\Traits\PosOutletSettings;
use App\Services\OfflineSaleService;
use App\Services\ShiftService;
use App\Services\DuitNowQRService;
use App\Services\SSMCompanyLookupService;
use App\Domains\Sales\DTOs\SaleData;
use App\Domains\Sales\Actions\ProcessSaleAction;
use App\Domains\Security\Actions\LogAuditAction;
use App\Domains\Customers\Services\LoyaltyService;

class PosController extends Controller
{
    use PosOutletSettings;

    public function verifyPinEndpoint(Request $request)
    {
        $request->validate(['pin' => 'required|string|size:4']);
        $user = auth()->user();

        \Log::info('Verify Pin Endpoint Hit', [
            'expected_pin' => $user ? $user->pin : null,
            'provided_pin' => $request->pin,
            'user_id' => $user ? $user->id : null
        ]);

        // If user has a specific PIN, check it. 
        // If not, check if any manager PIN matches (optional, but for now strict user PIN or manager override PIN?)
        // Let's assume the user MUST have a PIN set to unlock their own session, OR use a manager PIN.
        // Simple implementation: Check against Auth user's PIN.

        $audit = new LogAuditAction();

        if ($user && trim((string) $user->pin) === trim((string) $request->pin)) {
            session(['pos_locked' => false]);
            session()->save();
            $audit->execute('PIN_VERIFY_SUCCESS', "User #{$user->id} ({$user->name}) unlocked POS via own PIN.");
            return response()->json(['success' => true]);
        }

        // Check if a Manager or Admin PIN is provided as an override
        $manager = User::where('pin', $request->pin)
            ->whereIn('role', ['Manager', 'Admin', 'Super Admin'])
            ->first();

        if ($manager) {
            session(['pos_locked' => false]);
            session()->save();
            $audit->execute('PIN_VERIFY_SUCCESS', "User #{$user->id} unlocked POS via Manager PIN override (#{$manager->id} - {$manager->name}).");
            return response()->json(['success' => true]);
        }

        $audit->execute('PIN_VERIFY_FAILED', "Failed POS unlock attempt for User #{$user->id}.", [
            'attempted_pin' => $request->pin // Note: Use with caution, but for 4-digit POS pins, it helps detect pattern guessing
        ]);

        return response()->json(['message' => 'Invalid PIN'], 403);
    }

    public function index()
    {
        $user = auth()->user();
        $apiToken = $this->createPosToken($user, 10);
        $outletSettings = $this->getOutletSettings($user);
        $apiBaseUrl = url('/api/v1');

        $shiftService = new ShiftService();
        $hasOpenShift = $shiftService->getCurrentShift($user->outlet_id) !== null;

        return view('pos.app', [
            'apiToken' => $apiToken, 
            'outletSettings' => $outletSettings,
            'apiBaseUrl' => $apiBaseUrl,
            'hasOpenShift' => $hasOpenShift
        ]);
    }

    public function checkout()
    {
        $user = auth()->user();
        $apiToken = $this->createPosToken($user, 30);
        $outletSettings = $this->getOutletSettings($user);
        $apiBaseUrl = url('/api/v1');

        return view('pos.checkout', [
            'apiToken' => $apiToken, 
            'outletSettings' => $outletSettings,
            'apiBaseUrl' => $apiBaseUrl
        ]);
    }

    public function lock()
    {
        $user = auth()->user();
        $apiToken = $this->createPosToken($user, 120);
        $outletSettings = $this->getOutletSettings($user);
        $apiBaseUrl = url('/api/v1');

        session(['pos_locked' => true]);

        return view('pos.lock', [
            'apiToken' => $apiToken, 
            'outletSettings' => $outletSettings,
            'apiBaseUrl' => $apiBaseUrl
        ]);
    }

    public function searchProducts(Request $request)
    {
        $query = $request->input('query');
        $user = auth()->user();
        $userOutletId = $user ? $user->outlet_id : null;

        $products = Product::where('is_active', true)
            ->when($userOutletId, function ($queryBuilder) use ($userOutletId) {
                $queryBuilder->whereHas('prices', function ($priceQuery) use ($userOutletId) {
                    $priceQuery->where('outlet_id', $userOutletId);
                });
            })
            ->with([
                'prices' => function ($query) use ($userOutletId) { // Eager load prices for the specific outlet
                    $query->where('outlet_id', $userOutletId);
                },
                'modifiers.items' // Eager load modifiers and their items
            ])
            ->where(function ($queryBuilder) use ($query) {
                if (!empty($query)) {
                    $queryBuilder->where('name', 'like', '%' . $query . '%')
                        ->orWhere('slug', 'like', '%' . $query . '%')
                        ->orWhere('sku', 'like', '%' . $query . '%')
                        ->orWhere('barcode', 'like', '%' . $query . '%');
                }
            })
            ->when($request->category_id, function ($q) use ($request) {
                $q->where('category_id', $request->category_id);
            })
            ->select('id', 'name', 'description', 'price', 'cost', 'stock_level', 'sku', 'barcode') // Select only necessary columns from products table
            ->get();

        // Map products to include the specific price for the current outlet
        $formattedProducts = $products->map(function ($product) use ($userOutletId) {
            $outletPrice = $product->prices->firstWhere('outlet_id', $userOutletId);
            $price = $outletPrice ? $outletPrice->price : $product->price; // Use outlet price if available, else default product price
            $stockLevel = $outletPrice ? $outletPrice->stock_level : $product->stock_level; // Use outlet stock if available, else default product stock

            return [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'price' => $price,
                'cost' => $product->cost, // Keep default cost, as it's not per-outlet
                'stock_level' => $stockLevel,
                'modifiers' => $product->modifiers,
            ];
        });

        return response()->json($formattedProducts);
    }

    public function verifyPin(Request $request)
    {
        $request->validate(['pin' => 'required|string|size:4']);

        $manager = User::where('pin', $request->pin)
            ->whereIn('role', ['Super Admin', 'Admin', 'Manager'])
            ->first();

        if ($manager) {
            return response()->json(['valid' => true, 'manager_name' => $manager->name]);
        }

        return response()->json(['valid' => false], 401);
    }

    public function verifyCoupon(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'amount' => 'required|numeric|min:0'
        ]);

        $coupon = \App\Models\Coupon::where('code', $request->code)->valid()->first();

        if (!$coupon) {
            return response()->json(['valid' => false, 'message' => 'Invalid or expired coupon.'], 404);
        }

        if (!$coupon->isValidForAmount($request->amount)) {
            return response()->json(['valid' => false, 'message' => 'Minimum spend of ' . number_format($coupon->min_spend, 2) . ' required.'], 422);
        }

        // Calculate Discount
        $discountAmount = 0;
        if ($coupon->type === 'fixed') {
            $discountAmount = $coupon->value;
        } else {
            $discountAmount = $request->amount * ($coupon->value / 100);
        }

        // Cap discount at total amount
        $discountAmount = min($discountAmount, $request->amount);

        return response()->json([
            'valid' => true,
            'coupon' => $coupon,
            'discount_amount' => $discountAmount,
            'type' => $coupon->type,
            'value' => $coupon->value
        ]);
    }

    public function processSale(ProcessSaleRequest $request)
    {
        $userOutletId = auth()->user()->outlet_id;

        if ($request->outlet_id != $userOutletId) {
            return response()->json(['message' => 'Unauthorized access to outlet.'], 403);
        }

        // 1. Initial validations (that don't require locking)
        $totalPayment = collect($request->payments)->sum('amount');
        if ($totalPayment < $request->total_amount - 0.01) {
            return response()->json(['message' => 'Insufficient payment amount.'], 422);
        }

        // 2. Prepare Data DTO
        $saleData = SaleData::fromRequest($request);

        // 3. Execute core action
        try {
            $action = new ProcessSaleAction();
            $sale = $action->execute($saleData);

            $sale->load(['saleItems.product', 'payments', 'customer', 'outlet', 'user']);

            return response()->json(['message' => 'Sale processed successfully', 'sale' => $sale], 201);
        } catch (\Exception $e) {
            \Log::error('Sale Processing Failed: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'message' => 'Error processing sale',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function history(Request $request)
    {
        $user = auth()->user();
        $outletId = $user->outlet_id;

        $query = Sale::where('outlet_id', $outletId)
            ->with(['saleItems.product', 'user']) // Eager load
            ->latest();

        // Filter by Order ID if provided
        if ($request->filled('search')) {
            $query->where('id', 'like', '%' . $request->search . '%');
        }

        // Filter by Date if provided
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        $sales = $query->paginate(20);

        return response()->json($sales);
    }

    public function voidSale(Request $request, $id)
    {
        $request->validate([
            'pin' => 'required|string|size:4',
        ]);

        $manager = User::where('pin', $request->pin)
            ->whereIn('role', ['Super Admin', 'Admin', 'Manager'])
            ->first();

        $audit = new LogAuditAction();

        if (!$manager) {
            $audit->execute('VOID_SALE_FAILED', "Failed manager PIN override during void sale attempt for Sale #{$id}.");
            return response()->json(['message' => 'Invalid Manager PIN'], 403);
        }

        $sale = Sale::where('id', $id)->where('outlet_id', auth()->user()->outlet_id)->firstOrFail();

        if ($sale->status === 'void') {
            return response()->json(['message' => 'Sale is already voided'], 400);
        }

        DB::beginTransaction();
        try {
            $oldStatus = $sale->status;
            $sale->update(['status' => 'void']);
            
            $audit->execute('VOID_SALE_SUCCESS', "Sale #{$id} voided by Manager #{$manager->id} ({$manager->name}).", [
                'sale_id' => $id,
                'manager_id' => $manager->id,
                'old_status' => $oldStatus
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error voiding sale', 'error' => $e->getMessage()], 500);
        }

        return response()->json(['message' => 'Sale voided successfully', 'sale' => $sale]);
    }
    public function searchCustomer(Request $request)
    {
        $query = $request->input('query');
        if (empty($query)) {
            return response()->json([]);
        }

        $customers = Customer::where('name', 'like', '%' . $query . '%')
            ->orWhere('phone', 'like', '%' . $query . '%')
            ->limit(10)
            ->get(['id', 'name', 'phone', 'email']);

        return response()->json($customers);
    }

    public function createCustomer(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20|unique:customers,phone',
            'email' => 'nullable|email|max:255|unique:customers,email',
        ]);

        $customer = Customer::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'created_by' => auth()->id(),
        ]);

        return response()->json(['message' => 'Customer created successfully', 'customer' => $customer], 201);
    }
    public function getCategories()
    {
        $user = auth()->user();
        $outletId = $user ? $user->outlet_id : null;

        $categories = Category::query()
            ->when($outletId, function ($query) use ($outletId) {
                // Return categories that have at least one active product with a price for this outlet
                $query->whereHas('products', function ($productQuery) use ($outletId) {
                    $productQuery->where('is_active', true)
                        ->whereHas('prices', function ($priceQuery) use ($outletId) {
                            $priceQuery->where('outlet_id', $outletId);
                        });
                });
            })
            ->orderBy('sort_order', 'asc')
            ->get();

        return response()->json($categories);
    }

    public function getOutlets()
    {
        $outlets = Outlet::where('is_active', true)->get(['id', 'name', 'outlet_code', 'address', 'phone']);

        return response()->json(['outlets' => $outlets]);
    }

    public function getCustomerPoints(Request $request)
    {
        $customerId = $request->customer_id;

        if (!$customerId) {
            return response()->json(['message' => 'Customer ID required'], 400);
        }

        $customer = Customer::findOrFail($customerId);

        return response()->json([
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'phone' => $customer->phone,
                'loyalty_points' => $customer->loyalty_points,
                'total_points_earned' => $customer->total_points_earned,
                'loyalty_tier' => $customer->loyalty_tier,
                'points_value' => $customer->getPointsValue(),
            ]
        ]);
    }

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
            'requested_points' => $request->points_to_redeem,
            'redeemable_points' => $requestedPoints,
            'max_redeemable_points' => $maxRedeemable,
            'discount_amount' => $discount,
            'remaining_points' => $customer->loyalty_points - $requestedPoints,
        ]);
    }

    public function generateReceiptPdf($id)
    {
        $sale = Sale::with(['saleItems.product', 'payments', 'customer', 'user', 'outlet'])->findOrFail($id);
        $outlet = $sale->outlet ?? auth()->user()->outlet;

        $outletSettings = $outlet ? $outlet->settings : [];
        if ($outlet) {
            $outletSettings['name'] = $outlet->name;
            $outletSettings['address'] = $outlet->address;
            $outletSettings['phone'] = $outlet->phone;
        }

        $pdf = Pdf::loadView('pos.receipt-pdf', [
            'sale' => $sale,
            'outletSettings' => $outletSettings
        ]);

        // Calculate dynamic height based on content
        // Base height (Header + Footer + Meta + Totals) approx 150mm
        // Item row approx 10mm
        $itemCount = $sale->saleItems->count();
        $paymentCount = $sale->payments->count();
        $baseHeight = 120; // mm
        $itemHeight = 8; // mm per item
        $paymentHeight = 6; // mm per payment

        $totalHeight = $baseHeight + ($itemCount * $itemHeight) + ($paymentCount * $paymentHeight);

        // Convert to points (1mm = 2.83465pt)
        $msg = $outletSettings['receipt_footer'] ?? '';
        if (strlen($msg) > 50)
            $totalHeight += 10;

        $customPaper = array(0, 0, 226.77, $totalHeight * 2.83465); // 80mm width
        $pdf->setPaper($customPaper);

        return $pdf->stream('receipt-' . $sale->id . '.pdf');
    }

    public function saveOfflineDraft(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'outlet_id' => 'required|exists:outlets,id',
            'customer_id' => 'nullable|exists:customers,id',
            'cart_data' => 'required|array',
            'total_amount' => 'required|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'discount_reason' => 'nullable|string',
            'payments' => 'required|array',
            'payments.*.amount' => 'required|numeric|min:0',
            'payments.*.payment_method' => 'required|string',
        ]);

        $offlineService = new OfflineSaleService();
        $draft = $offlineService->saveDraft($request->all());

        return response()->json([
            'message' => 'Draft saved offline',
            'draft_id' => $draft->id,
            'local_created_at' => $draft->local_created_at,
        ], 201);
    }

    public function syncOfflineDrafts()
    {
        $offlineService = new OfflineSaleService();
        $results = $offlineService->syncAllPendingDrafts();

        return response()->json([
            'message' => 'Sync completed',
            'results' => $results,
            'pending_count' => $offlineService->getPendingCount(),
        ]);
    }

    public function getOfflineDrafts()
    {
        $offlineService = new OfflineSaleService();
        $drafts = $offlineService->getPendingDrafts();

        return response()->json([
            'pending_count' => $drafts->count(),
            'drafts' => $drafts,
        ]);
    }

    public function checkPendingOfflineSales()
    {
        $offlineService = new OfflineSaleService();

        return response()->json([
            'has_pending' => $offlineService->getPendingCount() > 0,
            'pending_count' => $offlineService->getPendingCount(),
        ]);
    }

    public function getLowStockAlerts(Request $request)
    {
        $user = auth()->user();
        if (!$user->outlet_id) {
            return response()->json(['alerts' => []]);
        }

        $lowStockProducts = \App\Models\ProductOutletPrice::with('product')
            ->where('outlet_id', $user->outlet_id)
            ->whereRaw('stock_level <= (SELECT low_stock_threshold FROM products WHERE products.id = product_outlet_prices.product_id AND products.low_stock_threshold > 0)')
            ->get();

        return response()->json([
            'alerts' => $lowStockProducts->map(function ($item) {
                return [
                    'id' => $item->product_id,
                    'name' => $item->product->name ?? 'Unknown',
                    'stock_level' => $item->stock_level,
                    'threshold' => $item->product->low_stock_threshold ?? 0,
                ];
            })
        ]);
    }

    public function openShift(Request $request)
    {
        $request->validate(['opening_cash' => 'required|numeric|min:0']);
        $user = auth()->user();
        $shiftService = new ShiftService();

        try {
            $shift = $shiftService->openShift($user->outlet_id, $user->id, $request->opening_cash);
            return response()->json(['message' => 'Shift opened successfully', 'shift' => $shift], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function closeShift(Request $request, $id)
    {
        $request->validate([
            'closing_cash' => 'required|numeric',
            'notes' => 'nullable|string',
            'pin' => 'required|string|size:4'
        ]);

        // Manager PIN Verification for security
        $manager = User::where('pin', $request->pin)
            ->whereIn('role', ['Manager', 'Admin', 'Super Admin'])
            ->where('outlet_id', auth()->user()->outlet_id)
            ->first();

        if (!$manager) {
            return response()->json(['message' => 'Invalid Manager PIN for authorization'], 403);
        }

        $shift = Shift::findOrFail($id);
        $shiftService = new ShiftService();

        try {
            $closedShift = $shiftService->closeShift($shift, array_merge(
                $request->only('closing_cash', 'notes'),
                ['closed_by' => $manager->id]
            ));
            return response()->json(['message' => 'Shift closed successfully', 'shift' => $closedShift]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function getCurrentShift()
    {
        $user = auth()->user();
        if (!$user->outlet_id) return response()->json(['message' => 'User has no outlet assigned', 'shift' => null], 400);

        $shiftService = new ShiftService();
        $shift = $shiftService->getCurrentShift($user->outlet_id);

        if (!$shift) return response()->json(['message' => 'No open shift found', 'shift' => null]);

        $salesSummary = $shiftService->getShiftSalesSummary($shift);
        return response()->json([
            'shift' => $shift,
            'sales_summary' => $salesSummary,
        ]);
    }

    public function getShiftHistory(Request $request)
    {
        $user = auth()->user();
        $shiftService = new ShiftService();

        $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date) : null;
        $endDate = $request->filled('end_date') ? Carbon::parse($request->end_date) : null;

        $shifts = $shiftService->getShiftHistory($user->outlet_id, $startDate, $endDate);

        return response()->json(['shifts' => $shifts]);
    }

    public function generateDuitNowQR(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'order_id' => 'nullable|string',
        ]);

        $duitNowService = new DuitNowQRService();

        if (!$duitNowService->isConfigured()) {
            return response()->json([
                'message' => 'DuitNow QR is not configured. Please contact administrator.',
                'configured' => false,
            ], 503);
        }

        $orderId = $request->order_id ?? DuitNowQRService::generateMerchantOrderId();
        $customerName = $request->filled('customer_name') ? $request->customer_name : null;

        $qrData = $duitNowService->generateDynamicQR(
            (float) $request->amount,
            $orderId,
            $customerName
        );

        return response()->json([
            'qr_data' => $qrData,
            'configured' => true,
        ]);
    }

    public function generateStaticDuitNowQR(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
        ]);

        $duitNowService = new DuitNowQRService();

        if (!$duitNowService->isConfigured()) {
            return response()->json([
                'message' => 'DuitNow QR is not configured. Please contact administrator.',
                'configured' => false,
            ], 503);
        }

        $qrData = $duitNowService->generateStaticQR((float) $request->amount);

        return response()->json([
            'qr_data' => $qrData,
            'configured' => true,
        ]);
    }

    public function verifyDuitNowPayment(Request $request)
    {
        $request->validate([
            'order_id' => 'required|string',
            'amount' => 'required|numeric',
        ]);

        $duitNowService = new DuitNowQRService();
        $result = $duitNowService->verifyPayment(
            $request->order_id,
            (float) $request->amount
        );

        return response()->json($result);
    }

    public function searchCompany(Request $request)
    {
        $request->validate([
            'query' => 'required|string|min:3',
        ]);

        $ssmService = new SSMCompanyLookupService();

        if (!$ssmService->isConfigured()) {
            return response()->json([
                'message' => 'SSM lookup is not configured. Using mock data.',
                'configured' => false,
                'results' => [],
            ], 503);
        }

        $results = $ssmService->searchByCompanyName($request->input('query'));

        return response()->json($results);
    }

    public function getCompanyDetails(Request $request)
    {
        $request->validate([
            'registration_number' => 'required|string|min:12',
        ]);

        $ssmService = new SSMCompanyLookupService();

        if (!$ssmService->isConfigured()) {
            $mockData = $ssmService->getMockCompanyData($request->registration_number);
            return response()->json(array_merge(['configured' => false], $mockData));
        }

        $result = $ssmService->searchByRegistrationNumber($request->registration_number);

        return response()->json(array_merge(['configured' => true], $result));
    }

    public function getCompanyOfficers(Request $request)
    {
        $request->validate([
            'registration_number' => 'required|string|min:12',
        ]);

        $ssmService = new SSMCompanyLookupService();

        if (!$ssmService->isConfigured()) {
            return response()->json([
                'message' => 'SSM lookup is not configured.',
                'configured' => false,
            ], 503);
        }

        $result = $ssmService->getCompanyOfficers($request->registration_number);

        return response()->json(array_merge(['configured' => true], $result));
    }

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

        if (!$user->outlet_id) {
            return response()->json(['message' => 'User has no outlet assigned'], 400);
        }

        if ($request->to_outlet_id == $user->outlet_id) {
            return response()->json(['message' => 'Cannot transfer to the same outlet'], 422);
        }

        $transfer = InventoryTransfer::create([
            'from_outlet_id' => $user->outlet_id,
            'to_outlet_id' => $request->to_outlet_id,
            'requested_by' => $user->id,
            'notes' => $request->notes,
            'status' => 'pending',
        ]);

        foreach ($request->items as $item) {
            InventoryTransferItem::create([
                'inventory_transfer_id' => $transfer->id,
                'product_id' => $item['product_id'],
                'quantity_requested' => $item['quantity'],
            ]);
        }

        $transfer->load('items.product');

        return response()->json(['message' => 'Transfer request created', 'transfer' => $transfer], 201);
    }

    public function getPendingTransfers(Request $request)
    {
        $user = auth()->user();

        if (!$user->outlet_id) {
            return response()->json(['message' => 'User has no outlet assigned', 'transfers' => []], 400);
        }

        $type = $request->get('type', 'incoming');

        $query = InventoryTransfer::with(['fromOutlet', 'toOutlet', 'items.product', 'requestedBy']);

        if ($type === 'incoming') {
            $query->where('to_outlet_id', $user->outlet_id);
        } else {
            $query->where('from_outlet_id', $user->outlet_id);
        }

        $transfers = $query->whereIn('status', ['pending', 'approved', 'in_transit'])
            ->orderBy('requested_at', 'desc')
            ->get();

        return response()->json(['transfers' => $transfers]);
    }

    public function approveTransfer($id)
    {
        $transfer = InventoryTransfer::findOrFail($id);
        $user = auth()->user();

        if ($transfer->to_outlet_id != $user->outlet_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($transfer->status != 'pending') {
            return response()->json(['message' => 'Transfer is not pending'], 400);
        }

        $transfer->approve($user->id);

        return response()->json(['message' => 'Transfer approved', 'transfer' => $transfer]);
    }

    public function rejectTransfer(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string',
        ]);

        $transfer = InventoryTransfer::findOrFail($id);
        $user = auth()->user();

        if ($transfer->to_outlet_id != $user->outlet_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $transfer->reject($request->reason, $user->id);

        return response()->json(['message' => 'Transfer rejected', 'transfer' => $transfer]);
    }

    public function markInTransit($id)
    {
        $transfer = InventoryTransfer::findOrFail($id);
        $user = auth()->user();

        if ($transfer->from_outlet_id != $user->outlet_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($transfer->status != 'approved') {
            return response()->json(['message' => 'Transfer must be approved first'], 400);
        }

        $transfer->markInTransit($user->id);

        return response()->json(['message' => 'Transfer marked as in transit', 'transfer' => $transfer]);
    }

    public function receiveTransfer(Request $request, $id)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity_received' => 'required|integer|min:0',
        ]);

        $transfer = InventoryTransfer::findOrFail($id);
        $user = auth()->user();

        if ($transfer->to_outlet_id != $user->outlet_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($transfer->status != 'in_transit') {
            return response()->json(['message' => 'Transfer must be in transit'], 400);
        }

        foreach ($request->items as $item) {
            $transferItem = InventoryTransferItem::where('inventory_transfer_id', $transfer->id)
                ->where('product_id', $item['product_id'])
                ->first();

            if ($transferItem) {
                $transferItem->update(['quantity_received' => $item['quantity_received']]);
            }
        }

        $transfer->receive($user->id);

        return response()->json(['message' => 'Transfer received', 'transfer' => $transfer->fresh('items')]);
    }

    public function getTransferHistory(Request $request)
    {
        $user = auth()->user();

        $transfers = InventoryTransfer::where('from_outlet_id', $user->outlet_id)
            ->orWhere('to_outlet_id', $user->outlet_id)
            ->with(['fromOutlet', 'toOutlet', 'items.product', 'requestedBy', 'approvedBy', 'receivedBy'])
            ->orderBy('requested_at', 'desc')
            ->paginate(20);

        return response()->json($transfers);
    }
}