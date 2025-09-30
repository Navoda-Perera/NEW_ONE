<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Customer\CustomerAuthController;
use App\Http\Controllers\Customer\CustomerDashboardController;
use App\Http\Controllers\PM\PMAuthController;
use App\Http\Controllers\PM\PMDashboardController;
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

Route::get('/debug-service-types', function () {
    $serviceTypes = [
        'register_post' => [
            'label' => 'Register Post',
            'has_weight' => true,
            'base_price' => 50
        ],
        'slp_courier' => [
            'label' => 'SLP Courier',
            'has_weight' => true,
            'base_price' => 100
        ],
        'cod' => [
            'label' => 'COD',
            'has_weight' => true,
            'base_price' => 75
        ],
        'remittance' => [
            'label' => 'Remittance',
            'has_weight' => false,
            'base_price' => 25
        ]
    ];
    return view('debug-service-types', compact('serviceTypes'));
});

Route::get('/debug-items', function () {
    $user = \App\Models\User::where('role', 'customer')->first();

    $query = \App\Models\Item::where('created_by', $user->id);
    $items = $query->orderBy('created_at', 'desc')->paginate(15);

    $serviceTypeLabels = [
        'register_post' => 'Register Post',
        'slp_courier' => 'SLP Courier',
        'cod' => 'COD',
        'remittance' => 'Remittance'
    ];

    $itemBulkData = \App\Models\ItemBulk::where('created_by', $user->id)
        ->where('category', 'single_item')
        ->orderBy('created_at', 'desc')
        ->get()
        ->keyBy('id');

    return [
        'user' => $user->name,
        'items_count' => $items->count(),
        'bulk_data_count' => $itemBulkData->count(),
        'service_labels' => $serviceTypeLabels,
        'first_item' => $items->first() ? [
            'receiver_name' => $items->first()->receiver_name,
            'created_at' => $items->first()->created_at
        ] : null
    ];
});

// Admin Routes
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login']);
    Route::get('/register', [AdminAuthController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [AdminAuthController::class, 'register']);
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

    Route::middleware(['auth', 'role:admin'])->group(function () {
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
    Route::post('/login', [PMAuthController::class, 'login']);
    Route::post('/logout', [PMAuthController::class, 'logout'])->name('logout');

    Route::middleware(['auth', 'role:pm'])->group(function () {
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

        // Company management routes
        Route::resource('companies', CompanyController::class);

        // Postman management routes
        Route::resource('postmen', PostmanController::class);

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
    Route::post('/login', [CustomerAuthController::class, 'login']);
    Route::get('/register', [CustomerAuthController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [CustomerAuthController::class, 'register']);
    Route::post('/logout', [CustomerAuthController::class, 'logout'])->name('logout');

    Route::middleware(['auth', 'role:customer'])->group(function () {
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
