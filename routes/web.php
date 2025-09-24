<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Customer\CustomerAuthController;
use App\Http\Controllers\Customer\CustomerDashboardController;
use App\Http\Controllers\PM\PMAuthController;
use App\Http\Controllers\PM\PMDashboardController;

Route::get('/', function () {
    return view('welcome');
});

// Admin Routes
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login']);
    Route::get('/register', [AdminAuthController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [AdminAuthController::class, 'register']);
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

    Route::middleware(['auth', 'role:admin,pm'])->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/users', [AdminDashboardController::class, 'users'])->name('users.index');
        Route::get('/users/create', [AdminDashboardController::class, 'createUser'])->name('users.create');
        Route::post('/users', [AdminDashboardController::class, 'storeUser'])->name('users.store');
        Route::patch('/users/{user}/toggle-status', [AdminDashboardController::class, 'toggleUserStatus'])->name('users.toggle-status');
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
    });
});
