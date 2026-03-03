<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\PosLoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\TransferController;
use App\Http\Controllers\Admin\ShiftController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\OutletController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Middleware\EnforceOutletRestriction;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('guest')->group(function () {
    // Auth routes
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');

    // Password Reset routes
    Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPasswordForm'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');

    // POS Login routes (alias)
    Route::get('/pos/login', [AuthController::class, 'showLoginForm'])->name('pos.login');
    Route::post('/pos/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::post('/pos/logout', [AuthController::class, 'logout'])->name('pos.logout');

// Home redirect based on role
Route::get('/home', function () {
    $user = auth()->user();
    if ($user && in_array($user->role, ['Cashier', 'Manager'])) {
        return redirect('/pos');
    }
    return redirect('/admin/dashboard');
})->middleware('auth');


Route::middleware(['auth:web,sanctum'])->group(function () {
    // Admin Routes
    Route::prefix('admin')->name('admin.')->middleware(['can:access-admin', EnforceOutletRestriction::class, 'outlet.session'])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Users
        Route::resource('users', UserController::class)->except(['show']);
        Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');

        // Outlets
        Route::resource('outlets', OutletController::class)->except(['show']);
        Route::get('/outlets/{outlet}', [OutletController::class, 'show'])->name('outlets.show');

        Route::get('/products', [ProductController::class, 'index'])->name('products.index');
        Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
        Route::post('/products', [ProductController::class, 'store'])->name('products.store');
        Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');
        Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
        Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
        Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');

        Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create');
        Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::get('/categories/{category}', [CategoryController::class, 'show'])->name('categories.show');
        Route::get('/categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
        Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

        Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
        Route::get('/customers/create', [CustomerController::class, 'create'])->name('customers.create');
        Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
        Route::get('/customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');
        Route::get('/customers/{customer}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
        Route::put('/customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
        Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');
        Route::get('/customers/{customer}/points', [CustomerController::class, 'points'])->name('customers.points');

        Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
        Route::get('/inventory/by-outlet/{outlet}', [InventoryController::class, 'getProductsByOutlet'])->name('inventory.by-outlet');
        Route::post('/inventory/adjust', [InventoryController::class, 'adjust'])->name('inventory.adjust');
        Route::get('/inventory/adjustments', [InventoryController::class, 'adjustments'])->name('inventory.adjustments');

        Route::get('/transfers', [TransferController::class, 'index'])->name('transfers.index');
        Route::get('/transfers/create', [TransferController::class, 'create'])->name('transfers.create');
        Route::post('/transfers', [TransferController::class, 'store'])->name('transfers.store');
        Route::get('/transfers/{transfer}', [TransferController::class, 'show'])->name('transfers.show');
        Route::get('/transfers/{transfer}/edit', [TransferController::class, 'edit'])->name('transfers.edit');
        Route::put('/transfers/{transfer}', [TransferController::class, 'update'])->name('transfers.update');
        Route::delete('/transfers/{transfer}', [TransferController::class, 'destroy'])->name('transfers.destroy');
        Route::post('/transfers/{id}/approve', [TransferController::class, 'approve'])->name('transfers.approve');
        Route::post('/transfers/{id}/reject', [TransferController::class, 'reject'])->name('transfers.reject');
        Route::post('/transfers/{id}/transit', [TransferController::class, 'transit'])->name('transfers.transit');
        Route::post('/transfers/{id}/receive', [TransferController::class, 'receive'])->name('transfers.receive');

        Route::get('/shifts', [ShiftController::class, 'index'])->name('shifts.index');
        Route::get('/shifts/create', [ShiftController::class, 'create'])->name('shifts.create');
        Route::post('/shifts', [ShiftController::class, 'store'])->name('shifts.store');
        Route::get('/shifts/{shift}/edit', [ShiftController::class, 'edit'])->name('shifts.edit');
        Route::put('/shifts/{shift}', [ShiftController::class, 'update'])->name('shifts.update');
        Route::delete('/shifts/{shift}', [ShiftController::class, 'destroy'])->name('shifts.destroy');
        Route::post('/shifts/{shift}/close', [ShiftController::class, 'close'])->name('shifts.close');
        Route::get('/shifts/{shift}', [ShiftController::class, 'show'])->name('shifts.show');

        Route::get('/reports/sales', [ReportController::class, 'sales'])->name('reports.sales');
        Route::get('/reports/inventory', [ReportController::class, 'inventory'])->name('reports.inventory');
        Route::get('/reports/outlets', [ReportController::class, 'outlets'])->name('reports.outlets');

        Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');

        // Profile
        Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
        Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    });

    // POS Routes
    Route::middleware(['pos.lock.check'])->group(function () {
        Route::get('/pos', [PosController::class, 'index'])->name('pos.home')->middleware('can:access-pos');
        Route::get('/pos/checkout', [PosController::class, 'checkout'])->name('pos.checkout')->middleware('can:access-pos');
        Route::get('/pos/lock', [PosController::class, 'lock'])->name('pos.lock')->middleware('can:access-pos');
        Route::post('/pos/verify-pin', [PosController::class, 'verifyPinEndpoint'])->name('pos.verify-pin')->middleware('can:access-pos');
        Route::get('/pos/sales/{id}/receipt', [PosController::class, 'generateReceiptPdf'])->name('pos.sales.receipt')->middleware('can:access-pos');

        Route::get('/pos/customer/points', [PosController::class, 'getCustomerPoints'])->name('pos.customer.points')->middleware('can:access-pos');
        Route::post('/pos/points/calculate', [PosController::class, 'calculatePointsRedemption'])->name('pos.points.calculate')->middleware('can:access-pos');

        Route::post('/pos/offline/save', [PosController::class, 'saveOfflineDraft'])->name('pos.offline.save')->middleware('can:access-pos');
        Route::post('/pos/offline/sync', [PosController::class, 'syncOfflineDrafts'])->name('pos.offline.sync')->middleware('can:access-pos');
        Route::get('/pos/offline/drafts', [PosController::class, 'getOfflineDrafts'])->name('pos.offline.drafts')->middleware('can:access-pos');
        Route::get('/pos/offline/check', [PosController::class, 'checkPendingOfflineSales'])->name('pos.offline.check')->middleware('can:access-pos');

        Route::get('/pos/inventory/low-stock', [PosController::class, 'getLowStockAlerts'])->name('pos.inventory.low-stock')->middleware('can:access-pos');

        Route::post('/pos/shift/open', [PosController::class, 'openShift'])->name('pos.shift.open')->middleware('can:access-pos');
        Route::post('/pos/shift/{id}/close', [PosController::class, 'closeShift'])->name('pos.shift.close')->middleware('can:access-pos');
        Route::get('/pos/shift/current', [PosController::class, 'getCurrentShift'])->name('pos.shift.current')->middleware('can:access-pos');
        Route::get('/pos/shift/history', [PosController::class, 'getShiftHistory'])->name('pos.shift.history')->middleware('can:access-pos');

        Route::post('/pos/payment/duitnow-qr', [PosController::class, 'generateDuitNowQR'])->name('pos.payment.duitnow-qr')->middleware('can:access-pos');
        Route::post('/pos/payment/duitnow-static', [PosController::class, 'generateStaticDuitNowQR'])->name('pos.payment.duitnow-static')->middleware('can:access-pos');
        Route::post('/pos/payment/duitnow-verify', [PosController::class, 'verifyDuitNowPayment'])->name('pos.payment.duitnow-verify')->middleware('can:access-pos');

        Route::get('/pos/company/search', [PosController::class, 'searchCompany'])->name('pos.company.search')->middleware('can:access-pos');
        Route::get('/pos/company/details', [PosController::class, 'getCompanyDetails'])->name('pos.company.details')->middleware('can:access-pos');
        Route::get('/pos/company/officers', [PosController::class, 'getCompanyOfficers'])->name('pos.company.officers')->middleware('can:access-pos');

        Route::post('/pos/transfer', [PosController::class, 'createTransfer'])->name('pos.transfer.create')->middleware('can:access-pos');
        Route::get('/pos/transfer/pending', [PosController::class, 'getPendingTransfers'])->name('pos.transfer.pending')->middleware('can:access-pos');
        Route::post('/pos/transfer/{id}/approve', [PosController::class, 'approveTransfer'])->name('pos.transfer.approve')->middleware('can:access-pos');
        Route::post('/pos/transfer/{id}/reject', [PosController::class, 'rejectTransfer'])->name('pos.transfer.reject')->middleware('can:access-pos');
        Route::post('/pos/transfer/{id}/transit', [PosController::class, 'markInTransit'])->name('pos.transfer.transit')->middleware('can:access-pos');
        Route::post('/pos/transfer/{id}/receive', [PosController::class, 'receiveTransfer'])->name('pos.transfer.receive')->middleware('can:access-pos');
        Route::get('/pos/transfer/history', [PosController::class, 'getTransferHistory'])->name('pos.transfer.history')->middleware('can:access-pos');
    });
});