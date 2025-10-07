<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Customer\CustomerAuthController;
use App\Http\Controllers\Customer\CustomerDashboardController;
use App\Http\Controllers\PM\PMAuthController;
use App\Http\Controllers\PM\PMDashboardController;
use App\Http\Controllers\PM\PMItemController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\PostmanController;
// use App\Http\Controllers\DeliveryController; // Temporarily commented out
// use App\Http\Controllers\DispatchController; // Temporarily commented out
// use App\Http\Controllers\PaymentController; // Temporarily commented out
// use App\Http\Controllers\ReceiptController; // Temporarily commented out
// use App\Http\Controllers\TrackingController; // Temporarily commented out

Route::get('/', function () {
    return view('welcome');
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

        // Customer management
        Route::get('/customers', [PMDashboardController::class, 'customers'])->name('customers.index');
        Route::get('/customers/create', [PMDashboardController::class, 'createCustomer'])->name('customers.create');
        Route::post('/customers', [PMDashboardController::class, 'storeCustomer'])->name('customers.store');

        // Postman management
        Route::get('/postmen', [PMDashboardController::class, 'postmen'])->name('postmen.index');
        Route::get('/postmen/create', [PMDashboardController::class, 'createPostman'])->name('postmen.create');
        Route::post('/postmen', [PMDashboardController::class, 'storePostman'])->name('postmen.store');

        // User status toggle
        Route::patch('/users/{user}/toggle-status', [PMDashboardController::class, 'toggleUserStatus'])->name('users.toggle-status');

        // Items management
        Route::get('/items/pending', [PMItemController::class, 'pending'])->name('items.pending');
        Route::get('/items/{id}/edit', [PMItemController::class, 'edit'])->name('items.edit');
        Route::post('/items/{id}/accept', [PMItemController::class, 'accept'])->name('items.accept');
        Route::post('/items/{id}/reject', [PMItemController::class, 'reject'])->name('items.reject');

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
            Route::get('/items', [CustomerDashboardController::class, 'items'])->name('items');
            Route::get('/bulk-status/{id}', [CustomerDashboardController::class, 'bulkStatus'])->name('bulk-status');
            Route::put('/bulk-item/{id}', [CustomerDashboardController::class, 'updateBulkItem'])->name('update-bulk-item');
            Route::delete('/bulk-item/{id}', [CustomerDashboardController::class, 'deleteBulkItem'])->name('delete-bulk-item');
            Route::post('/bulk-submit/{id}', [CustomerDashboardController::class, 'submitBulkToPM'])->name('submit-bulk-to-pm');
            Route::post('/get-slp-price', [CustomerDashboardController::class, 'getSlpPrice'])->name('get-slp-price');
            Route::post('/get-postal-price', [CustomerDashboardController::class, 'getPostalPrice'])->name('get-postal-price');
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
