<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Customer\CustomerAuthController;
use App\Http\Controllers\Customer\CustomerDashboardController;
use App\Http\Controllers\Customer\CustomerReceiptController;
use App\Http\Controllers\PM\PMAuthController;
use App\Http\Controllers\PM\PMDashboardController;
use App\Http\Controllers\PM\PMItemController;
use App\Http\Controllers\PM\PMSingleItemController;
use App\Http\Controllers\CompanyController;
// use App\Http\Controllers\DeliveryController; // Temporarily commented out
// use App\Http\Controllers\DispatchController; // Temporarily commented out
// use App\Http\Controllers\PaymentController; // Temporarily commented out
// use App\Http\Controllers\ReceiptController; // Temporarily commented out
// use App\Http\Controllers\TrackingController; // Temporarily commented out

Route::get('/', function () {
    return view('welcome');
});

// CSRF token refresh route
Route::get('/csrf-token', function () {
    return response()->json(['token' => csrf_token()]);
})->name('csrf.refresh');

// Temporary CSRF test routes
Route::get('/test-csrf', function () {
    return view('test-csrf');
})->name('test.csrf');

Route::post('/test-csrf', function (\Illuminate\Http\Request $request) {
    $request->validate([
        'test_input' => 'required|string'
    ]);

    return back()->with('success', 'CSRF test successful! Data: ' . $request->test_input);
})->name('test.csrf.submit');
Route::get('/test-csrf', function () {
    return response()->view('csrf_test');
});

Route::post('/test-csrf', function (Illuminate\Http\Request $request) {
    $request->validate(['test_field' => 'required']);
    return back()->with('success', 'CSRF test passed! Field value: ' . $request->test_field);
});

// Default login route for Laravel auth middleware
Route::get('/login', function () {
    return redirect('/admin/login');
})->name('login');

// PM specific login redirect
Route::get('/pm', function () {
    return redirect('/pm/login');
});

// Customer specific login redirect
Route::get('/customer', function () {
    return redirect('/customer/login');
});

// Admin Routes
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login'])->name('login.post');
    Route::get('/register', [AdminAuthController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [AdminAuthController::class, 'register']);
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

    Route::middleware(['role:admin'])->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/users', [AdminDashboardController::class, 'users'])->name('users.index');
        Route::get('/users/create', [AdminDashboardController::class, 'createUser'])->name('users.create');
        Route::post('/users', [AdminDashboardController::class, 'storeUser'])->name('users.store');
        Route::get('/users/{user}/edit', [AdminDashboardController::class, 'editUser'])->name('users.edit');
        Route::put('/users/{user}', [AdminDashboardController::class, 'updateUser'])->name('users.update');
        Route::patch('/users/{user}/toggle-status', [AdminDashboardController::class, 'toggleUserStatus'])->name('users.toggle-status');

        // Admin-only company management
        Route::get('/companies/financial-report', [CompanyController::class, 'financialReport'])->name('companies.financial-report');

        // System tracking and monitoring
        // Route::get('/tracking/system-overview', [TrackingController::class, 'systemOverview'])->name('tracking.system-overview'); // Temporarily commented out
        Route::get('/reports/financial', [AdminDashboardController::class, 'financialReports'])->name('reports.financial');
        Route::get('/reports/operational', [AdminDashboardController::class, 'operationalReports'])->name('reports.operational');
    });
});

// PM Routes
Route::prefix('pm')->name('pm.')->group(function () {
    Route::get('/login', [PMAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [PMAuthController::class, 'login'])->name('login.post');
    Route::post('/logout', [PMAuthController::class, 'logout'])->name('logout');

    Route::middleware(['role:pm'])->group(function () {
        Route::get('/dashboard', [PMDashboardController::class, 'index'])->name('dashboard');

        // Debug route to test authentication
        Route::get('/debug', function() {
            return response()->json([
                'authenticated' => \Illuminate\Support\Facades\Auth::check(),
                'user' => \Illuminate\Support\Facades\Auth::user(),
                'role' => \Illuminate\Support\Facades\Auth::user() ? \Illuminate\Support\Facades\Auth::user()->role : null
            ]);
        })->name('debug');

        // Customer management
        Route::get('/customers', [PMDashboardController::class, 'customers'])->name('customers.index');
        Route::get('/customers/create', [PMDashboardController::class, 'createCustomer'])->name('customers.create');
        Route::post('/customers', [PMDashboardController::class, 'storeCustomer'])->name('customers.store');

        // User status toggle
        Route::patch('/users/{user}/toggle-status', [PMDashboardController::class, 'toggleUserStatus'])->name('users.toggle-status');

        // Items management
        Route::get('/items/pending', [PMItemController::class, 'pending'])->name('items.pending');
        Route::get('/items/pending/{serviceType}', [PMItemController::class, 'pendingByServiceType'])->name('items.pending.by-service-type');
        Route::get('/items/{id}/edit', [PMItemController::class, 'edit'])->name('items.edit');
        Route::post('/items/{id}/accept', [PMItemController::class, 'accept'])->name('items.accept');
        Route::post('/items/{id}/update-only', [PMItemController::class, 'updateOnly'])->name('items.update-only');
        Route::post('/items/{id}/accept-with-updates', [PMItemController::class, 'acceptWithUpdates'])->name('items.accept-with-updates');
        Route::post('/items/{id}/quick-accept', [PMItemController::class, 'quickAccept'])->name('items.quick-accept');
        Route::post('/items/{id}/reject', [PMItemController::class, 'reject'])->name('items.reject');
        Route::post('/items/{id}/update-barcode', [PMItemController::class, 'updateBarcode'])->name('items.update-barcode');
        Route::post('/bulk-upload/{id}/accept-all', [PMItemController::class, 'acceptBulkUploadCompletely'])->name('bulk-upload.accept-all');

        // Customer uploads management
        Route::get('/customer-uploads', [PMDashboardController::class, 'customerUploads'])->name('customer-uploads');
        Route::get('/view-customer-upload/{id}', [PMDashboardController::class, 'viewCustomerUpload'])->name('view-customer-upload');
        Route::get('/customer-upload/{id}/receipt', [PMDashboardController::class, 'viewCustomerUploadReceipt'])->name('view-customer-upload-receipt');
        Route::get('/customer-upload/{id}/print-receipt', [PMDashboardController::class, 'printCustomerUploadReceipt'])->name('print-customer-upload-receipt');
        Route::post('/accept-all-upload/{id}', [PMDashboardController::class, 'acceptAllUpload'])->name('accept-all-upload');
        Route::post('/accept-selected-upload/{id}', [PMDashboardController::class, 'acceptSelectedUpload'])->name('accept-selected-upload');

        // PM Bulk Upload (goes directly to items/item_bulk tables)
        Route::get('/bulk-upload', [PMDashboardController::class, 'bulkUpload'])->name('bulk-upload');
        Route::post('/bulk-upload', [PMDashboardController::class, 'storeBulkUpload'])->name('store-bulk-upload');
        Route::get('/bulk-upload/template', [PMDashboardController::class, 'showBulkUploadTemplate'])->name('bulk-upload.template');

        // Single Item Management
        Route::prefix('single-item')->name('single-item.')->group(function () {
            Route::get('/', [PMSingleItemController::class, 'index'])->name('index');
            Route::get('/slp-form', [PMSingleItemController::class, 'showSLPForm'])->name('slp-form');
            Route::get('/cod-form', [PMSingleItemController::class, 'showCODForm'])->name('cod-form');
            Route::get('/register-form', [PMSingleItemController::class, 'showRegisterForm'])->name('register-form');
            Route::post('/store-slp', [PMSingleItemController::class, 'storeSLP'])->name('store-slp');
            Route::post('/store-cod', [PMSingleItemController::class, 'storeCOD'])->name('store-cod');
            Route::post('/store-register', [PMSingleItemController::class, 'storeRegister'])->name('store-register');
            Route::post('/calculate-postage', [PMSingleItemController::class, 'calculatePostage'])->name('calculate-postage');
            Route::get('/receipt/{id}', [PMSingleItemController::class, 'showReceipt'])->name('receipt');
            Route::get('/print-receipt/{id}', [PMSingleItemController::class, 'printReceipt'])->name('print-receipt');
        });

        // Item Management with Barcode Scanning
        Route::prefix('item-management')->name('item-management.')->group(function () {
            Route::get('/', [PMItemController::class, 'management'])->name('index');
            Route::post('/search-barcode', [PMItemController::class, 'searchByBarcode'])->name('search-barcode');
            Route::get('/edit/{id}', [PMItemController::class, 'editItem'])->name('edit');
            Route::put('/update/{id}', [PMItemController::class, 'updateItem'])->name('update');
            Route::delete('/delete/{id}', [PMItemController::class, 'deleteItem'])->name('delete');
            Route::get('/items/list', [PMItemController::class, 'itemsList'])->name('items.list');
        });

        // Company management routes
        Route::resource('companies', CompanyController::class);

        // Delivery management routes (temporarily commented out)
        // Route::resource('deliveries', DeliveryController::class);
        // Route::post('/deliveries/{delivery}/assign-items', [DeliveryController::class, 'assignItems'])->name('deliveries.assign-items');

        // Dispatch management routes (temporarily commented out)
        // Route::resource('dispatches', DispatchController::class);
        // Route::post('/dispatches/{dispatch}/assign-items', [DispatchController::class, 'assignItems'])->name('dispatches.assign-items');

        // Payment management routes (temporarily commented out)
        // Route::resource('payments', PaymentController::class);

        // Receipt management routes (temporarily commented out)
        // Route::resource('receipts', ReceiptController::class);
        // Route::get('/receipts/{receipt}/print', [ReceiptController::class, 'print'])->name('receipts.print');
    });
});

// Customer Routes
Route::prefix('customer')->name('customer.')->group(function () {
    Route::get('/login', [CustomerAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [CustomerAuthController::class, 'login'])->name('login.post');
    Route::get('/register', [CustomerAuthController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [CustomerAuthController::class, 'register']);
    Route::post('/logout', [CustomerAuthController::class, 'logout'])->name('logout');

    Route::middleware(['role:customer'])->group(function () {
        Route::get('/dashboard', [CustomerDashboardController::class, 'index'])->name('dashboard');
        Route::get('/profile', [CustomerDashboardController::class, 'profile'])->name('profile');
        Route::patch('/profile', [CustomerDashboardController::class, 'updateProfile'])->name('profile.update');
        Route::patch('/password', [CustomerDashboardController::class, 'updatePassword'])->name('password.update');

        // Postal Services Routes
        Route::prefix('services')->name('services.')->group(function () {
            Route::get('/', [CustomerDashboardController::class, 'services'])->name('index');
            Route::get('/add-single-item', [CustomerDashboardController::class, 'addSingleItem'])->name('add-single-item');
            Route::post('/add-single-item', [CustomerDashboardController::class, 'storeSingleItem'])->name('store-single-item');
            Route::get('/bulk-upload', [CustomerDashboardController::class, 'bulkUpload'])->name('bulk-upload');
            Route::post('/bulk-upload', [CustomerDashboardController::class, 'storeBulkUpload'])->name('store-bulk-upload');
            Route::get('/bulk-upload/template', [CustomerDashboardController::class, 'showBulkUploadTemplate'])->name('bulk-upload.template');
            Route::get('/items', [CustomerDashboardController::class, 'items'])->name('items');
            Route::get('/view-upload/{id}', [CustomerDashboardController::class, 'viewUpload'])->name('view-upload');
            Route::get('/bulk-status/{id}', [CustomerDashboardController::class, 'bulkStatus'])->name('bulk-status');
            Route::delete('/bulk-upload/{id}', [CustomerDashboardController::class, 'deleteBulkUpload'])->name('delete-bulk-upload');
            Route::put('/bulk-item/{id}', [CustomerDashboardController::class, 'updateBulkItem'])->name('update-bulk-item');
            Route::delete('/bulk-item/{id}', [CustomerDashboardController::class, 'deleteBulkItem'])->name('delete-bulk-item');
            Route::post('/bulk-submit/{id}', [CustomerDashboardController::class, 'submitBulkToPM'])->name('submit-bulk-to-pm');
            Route::post('/get-slp-price', [CustomerDashboardController::class, 'getSlpPrice'])->name('get-slp-price');
            Route::post('/get-postal-price', [CustomerDashboardController::class, 'getPostalPrice'])->name('get-postal-price');
            Route::post('/calculate-postage', [CustomerDashboardController::class, 'calculatePostage'])->name('calculate-postage');

            // Debug route for testing postage calculation
            Route::get('/test-postage', function() {
                $slpPrice = \App\Models\SlpPricing::calculatePrice(250);
                $postPrice = \App\Models\PostPricing::calculatePrice(250, \App\Models\PostPricing::TYPE_REGISTER);

                return response()->json([
                    'slp_price_for_250g' => $slpPrice,
                    'post_price_for_250g' => $postPrice,
                    'pricing_tables_exist' => [
                        'slp_count' => \App\Models\SlpPricing::count(),
                        'post_count' => \App\Models\PostPricing::count()
                    ]
                ]);
            })->name('test-postage');
        });

        // Customer Receipt Management
        Route::prefix('receipts')->name('receipts.')->group(function () {
            Route::get('/', [CustomerReceiptController::class, 'index'])->name('index');
            Route::get('/{id}', [CustomerReceiptController::class, 'show'])->name('show');
            Route::get('/{id}/download', [CustomerReceiptController::class, 'download'])->name('download');
            Route::post('/search', [CustomerReceiptController::class, 'searchByBarcode'])->name('search');
        });

        // Item tracking routes
        Route::prefix('tracking')->name('tracking.')->group(function () {
            Route::get('/', [CustomerDashboardController::class, 'trackingIndex'])->name('index');
            Route::get('/item/{barcode}', [CustomerDashboardController::class, 'trackItem'])->name('item');
            Route::post('/search', [CustomerDashboardController::class, 'searchItems'])->name('search');
        });
    });
});

// Public tracking routes (no authentication required)
Route::prefix('track')->name('track.')->group(function () {
    Route::get('/', function () {
        return view('public.tracking');
    })->name('index');
    // Route::post('/item', [TrackingController::class, 'publicTrack'])->name('item'); // Temporarily commented out
});
