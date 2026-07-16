<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\StockInController;
use App\Http\Controllers\StockOutController;
use App\Http\Controllers\StockTransferController;
use App\Http\Controllers\StockOpnameController;
use App\Http\Controllers\StockReturnController;
use App\Http\Controllers\Reports\LedgerController;
use App\Http\Controllers\Reports\MutationController;
use App\Http\Controllers\Reports\ValuationController;
use App\Http\Controllers\Reports\LowStockController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\NotificationSettingController;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    Route::resource('products', ProductController::class);
    Route::resource('categories', CategoryController::class);
    Route::resource('units', UnitController::class);
    
    // Master Partner & Lokasi
    Route::resource('suppliers', SupplierController::class);
    Route::resource('warehouses', WarehouseController::class);
    
    // Transaksi — Barang Masuk
    Route::resource('stock-ins', StockInController::class)->only(['index', 'store', 'show', 'destroy', 'update']);
    Route::post('/stock-ins/{stockIn}/confirm', [StockInController::class, 'confirm'])->name('stock-ins.confirm');
    Route::post('/stock-ins/{stockIn}/unconfirm', [StockInController::class, 'unconfirm'])->name('stock-ins.unconfirm');

    // Transaksi — Barang Keluar
    Route::resource('stock-outs', StockOutController::class)->only(['index', 'store', 'show', 'destroy', 'update']);
    Route::post('/stock-outs/{stockOut}/confirm', [StockOutController::class, 'confirm'])->name('stock-outs.confirm');
    Route::post('/stock-outs/{stockOut}/unconfirm', [StockOutController::class, 'unconfirm'])->name('stock-outs.unconfirm');

    // Transaksi — Transfer Stok
    Route::resource('stock-transfers', StockTransferController::class)->only(['index', 'store', 'show', 'destroy', 'update']);
    Route::post('/stock-transfers/{stockTransfer}/confirm', [StockTransferController::class, 'confirm'])->name('stock-transfers.confirm');
    Route::post('/stock-transfers/{stockTransfer}/unconfirm', [StockTransferController::class, 'unconfirm'])->name('stock-transfers.unconfirm');
    
    // Transaksi — Stock Opname
    Route::resource('stock-opnames', StockOpnameController::class)->only(['index', 'store', 'show', 'destroy', 'update']);
    Route::post('/stock-opnames/{stockOpname}/confirm', [StockOpnameController::class, 'confirm'])->name('stock-opnames.confirm');
    Route::post('/stock-opnames/{stockOpname}/unconfirm', [StockOpnameController::class, 'unconfirm'])->name('stock-opnames.unconfirm');

    // Transaksi — Retur Barang
    Route::resource('stock-returns', StockReturnController::class)->only(['index', 'store', 'show', 'destroy', 'update']);
    Route::post('/stock-returns/{stockReturn}/confirm', [StockReturnController::class, 'confirm'])->name('stock-returns.confirm');
    Route::post('/stock-returns/{stockReturn}/unconfirm', [StockReturnController::class, 'unconfirm'])->name('stock-returns.unconfirm');

    // Laporan
    Route::get('/reports/ledger',    [LedgerController::class, 'index'])->name('reports.ledger');
    Route::get('/reports/mutations', [MutationController::class, 'index'])->name('reports.mutations');
    Route::get('/reports/valuation', [ValuationController::class, 'index'])->name('reports.valuation');
    Route::get('/reports/low-stock', [LowStockController::class, 'index'])->name('reports.low-stock');

    // Pengaturan
    Route::resource('users', UserController::class)->except(['create', 'edit']);
    Route::resource('roles', RoleController::class)->except(['create', 'edit']);
    Route::get('/notification-settings', [NotificationSettingController::class, 'index'])->name('notification-settings.index');
    Route::put('/notification-settings', [NotificationSettingController::class, 'update'])->name('notification-settings.update');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Pengaturan User (profil, password, PIN)
    Route::get('/settings/user', [App\Http\Controllers\UserSettingsController::class, 'index'])->name('settings.user');
    Route::patch('/settings/user/profile', [App\Http\Controllers\UserSettingsController::class, 'updateProfile'])->name('settings.user.profile');
    Route::patch('/settings/user/password', [App\Http\Controllers\UserSettingsController::class, 'updatePassword'])->name('settings.user.password');
    Route::patch('/settings/user/pin', [App\Http\Controllers\UserSettingsController::class, 'updatePin'])->name('settings.user.pin');

    // Role Switch (Admin only)
    Route::post('/role-switch', [App\Http\Controllers\RoleSwitchController::class, 'switch'])->name('role-switch.switch');
    Route::post('/role-switch/restore', [App\Http\Controllers\RoleSwitchController::class, 'restore'])->name('role-switch.restore');
});

require __DIR__.'/auth.php';
