<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PosApiController;
use App\Http\Controllers\Api\ReportApiController;
use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\ManagerApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Authentication Routes
Route::prefix('v1/auth')->group(function () {
    Route::post('/login', [AuthApiController::class, 'login']);
    Route::post('/forgot-password', [AuthApiController::class, 'forgotPassword']);
    
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AuthApiController::class, 'me']);
        Route::post('/logout', [AuthApiController::class, 'logout']);
        Route::post('/verify-pin', [AuthApiController::class, 'verifyPin']);
    });
});

Route::prefix('v1')->middleware('auth:sanctum')->name('api.v1.')->group(function () {
    
    // POS Terminal Routes
    Route::prefix('pos')->name('pos.')->group(function () {
        Route::get('/products', [PosApiController::class, 'searchProducts'])->name('products');
        Route::get('/categories', [PosApiController::class, 'getCategories'])->name('categories');
        Route::get('/history', [PosApiController::class, 'history'])->name('history');
        Route::post('/sales', [PosApiController::class, 'processSale'])->name('sales');
        Route::post('/sales/{id}/void', [PosApiController::class, 'voidSale'])->name('void');
        
        // Coupons
        Route::post('/coupons/verify', [PosApiController::class, 'verifyCoupon'])->name('coupons.verify');

        // Shifts Management
        Route::get('/shift/current', [PosApiController::class, 'currentShift'])->name('shift.current');
        Route::post('/shift/open', [PosApiController::class, 'openShift'])->name('shift.open');
        Route::post('/shift/{id}/close', [PosApiController::class, 'closeShift'])->name('shift.close');
        Route::get('/shift/history', [PosApiController::class, 'getShiftHistory'])->name('shift.history');
        
        // Customers Management
        Route::get('/customers', [PosApiController::class, 'searchCustomers'])->name('customers.search');
        Route::post('/customers', [PosApiController::class, 'createCustomer'])->name('customers.create');
        Route::get('/customers/{id}/points', [PosApiController::class, 'customerPoints'])->name('customers.points');
        Route::post('/customers/points/calculate', [PosApiController::class, 'calculatePointsRedemption'])->name('points.calculate');
        
        // Inventory Transfers
        Route::get('/transfers/pending', [PosApiController::class, 'getPendingTransfers'])->name('transfers.pending');
        Route::post('/transfers', [PosApiController::class, 'createTransfer'])->name('transfers.create');
        Route::post('/transfers/{id}/receive', [PosApiController::class, 'receiveTransfer'])->name('transfers.receive');

        // Outlets & Settings
        Route::get('/outlets', [PosApiController::class, 'getOutlets'])->name('outlets');
        Route::get('/inventory/low-stock', [PosApiController::class, 'getLowStockAlerts'])->name('low-stock');

        // Payments (DuitNow QR)
        Route::post('/payments/duitnow/qr', [PosApiController::class, 'generateDuitNowQR']);
        Route::post('/payments/duitnow/verify', [PosApiController::class, 'verifyDuitNowPayment']);

        // Offline Sync (preserves existing drafts if any)
        Route::post('/sync', [\App\Http\Controllers\PosController::class, 'syncOfflineDrafts']);
    });

    // Reporting & Dashboard Routes
    Route::prefix('reports')->group(function () {
        Route::get('/sales-summary', [ReportApiController::class, 'salesSummary']);
        Route::get('/inventory-valuation', [ReportApiController::class, 'inventoryValue']);
        Route::get('/top-products', [ReportApiController::class, 'topSellingProducts']);
    });

    // Manager Insights Routes
    Route::prefix('manager')->group(function () {
        Route::get('/performance', [ManagerApiController::class, 'crossOutletPerformance']);
        Route::get('/dead-stock', [ManagerApiController::class, 'deadStockReport']);
        Route::get('/margins', [ManagerApiController::class, 'marginAnalysis']);
    });

});
