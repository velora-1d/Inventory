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
        'canLogin'      => Route::has('login'),
        'canRegister'   => Route::has('register'),
        'laravelVersion'=> Application::VERSION,
        'phpVersion'    => PHP_VERSION,
    ]);
});

Route::middleware(['auth', 'verified'])->group(function () {

    // ── Dashboard ──────────────────────────────────────────────────────────
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware('permission:dashboard.view')
        ->name('dashboard');

    // ── Master Barang ──────────────────────────────────────────────────────
    Route::middleware('permission:barang.view')->group(function () {
        Route::get('products',               [ProductController::class, 'index'])->name('products.index');
        Route::get('products/{product}',     [ProductController::class, 'show'])->name('products.show');
        Route::get('products/{product}/edit',[ProductController::class, 'edit'])->name('products.edit');
        Route::get('products/create',        [ProductController::class, 'create'])->name('products.create');
    });
    Route::post('products',              [ProductController::class, 'store'])->middleware('permission:barang.create')->name('products.store');
    Route::put('products/{product}',     [ProductController::class, 'update'])->middleware('permission:barang.update')->name('products.update');
    Route::patch('products/{product}',   [ProductController::class, 'update']);
    Route::delete('products/{product}',  [ProductController::class, 'destroy'])->middleware('permission:barang.delete')->name('products.destroy');

    Route::middleware('permission:kategori.view')->group(function () {
        Route::get('categories',                 [CategoryController::class, 'index'])->name('categories.index');
        Route::get('categories/{category}',      [CategoryController::class, 'show'])->name('categories.show');
        Route::get('categories/create',          [CategoryController::class, 'create'])->name('categories.create');
        Route::get('categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
    });
    Route::post('categories',              [CategoryController::class, 'store'])->middleware('permission:kategori.create')->name('categories.store');
    Route::put('categories/{category}',    [CategoryController::class, 'update'])->middleware('permission:kategori.update')->name('categories.update');
    Route::patch('categories/{category}',  [CategoryController::class, 'update']);
    Route::delete('categories/{category}', [CategoryController::class, 'destroy'])->middleware('permission:kategori.delete')->name('categories.destroy');

    Route::middleware('permission:satuan.view')->group(function () {
        Route::get('units',              [UnitController::class, 'index'])->name('units.index');
        Route::get('units/{unit}',       [UnitController::class, 'show'])->name('units.show');
        Route::get('units/create',       [UnitController::class, 'create'])->name('units.create');
        Route::get('units/{unit}/edit',  [UnitController::class, 'edit'])->name('units.edit');
    });
    Route::post('units',         [UnitController::class, 'store'])->middleware('permission:satuan.create')->name('units.store');
    Route::put('units/{unit}',   [UnitController::class, 'update'])->middleware('permission:satuan.update')->name('units.update');
    Route::patch('units/{unit}', [UnitController::class, 'update']);
    Route::delete('units/{unit}',[UnitController::class, 'destroy'])->middleware('permission:satuan.delete')->name('units.destroy');

    // ── Partner & Lokasi ───────────────────────────────────────────────────
    Route::middleware('permission:supplier.view')->group(function () {
        Route::get('suppliers',                  [SupplierController::class, 'index'])->name('suppliers.index');
        Route::get('suppliers/{supplier}',       [SupplierController::class, 'show'])->name('suppliers.show');
        Route::get('suppliers/create',           [SupplierController::class, 'create'])->name('suppliers.create');
        Route::get('suppliers/{supplier}/edit',  [SupplierController::class, 'edit'])->name('suppliers.edit');
    });
    Route::post('suppliers',              [SupplierController::class, 'store'])->middleware('permission:supplier.create')->name('suppliers.store');
    Route::put('suppliers/{supplier}',    [SupplierController::class, 'update'])->middleware('permission:supplier.update')->name('suppliers.update');
    Route::patch('suppliers/{supplier}',  [SupplierController::class, 'update']);
    Route::delete('suppliers/{supplier}', [SupplierController::class, 'destroy'])->middleware('permission:supplier.delete')->name('suppliers.destroy');

    Route::middleware('permission:gudang.view')->group(function () {
        Route::get('warehouses',                  [WarehouseController::class, 'index'])->name('warehouses.index');
        Route::get('warehouses/{warehouse}',      [WarehouseController::class, 'show'])->name('warehouses.show');
        Route::get('warehouses/create',           [WarehouseController::class, 'create'])->name('warehouses.create');
        Route::get('warehouses/{warehouse}/edit', [WarehouseController::class, 'edit'])->name('warehouses.edit');
    });
    Route::post('warehouses',              [WarehouseController::class, 'store'])->middleware('permission:gudang.create')->name('warehouses.store');
    Route::put('warehouses/{warehouse}',   [WarehouseController::class, 'update'])->middleware('permission:gudang.update')->name('warehouses.update');
    Route::patch('warehouses/{warehouse}', [WarehouseController::class, 'update']);
    Route::delete('warehouses/{warehouse}',[WarehouseController::class, 'destroy'])->middleware('permission:gudang.delete')->name('warehouses.destroy');

    // ── Transaksi — Barang Masuk ───────────────────────────────────────────
    Route::middleware('permission:barang_masuk.view')->group(function () {
        Route::get('stock-ins',           [StockInController::class, 'index'])->name('stock-ins.index');
        Route::get('stock-ins/{stockIn}', [StockInController::class, 'show'])->name('stock-ins.show');
    });
    Route::post('stock-ins',                          [StockInController::class, 'store'])->middleware('permission:barang_masuk.input')->name('stock-ins.store');
    Route::put('stock-ins/{stockIn}',                 [StockInController::class, 'update'])->middleware('permission:barang_masuk.update')->name('stock-ins.update');
    Route::patch('stock-ins/{stockIn}',               [StockInController::class, 'update']);
    Route::delete('stock-ins/{stockIn}',              [StockInController::class, 'destroy'])->middleware('permission:barang_masuk.delete')->name('stock-ins.destroy');
    Route::post('stock-ins/{stockIn}/confirm',        [StockInController::class, 'confirm'])->middleware('permission:barang_masuk.create')->name('stock-ins.confirm');
    Route::post('stock-ins/{stockIn}/unconfirm',      [StockInController::class, 'unconfirm'])->middleware('permission:barang_masuk.update')->name('stock-ins.unconfirm');

    // ── Transaksi — Barang Keluar ──────────────────────────────────────────
    Route::middleware('permission:barang_keluar.view')->group(function () {
        Route::get('stock-outs',            [StockOutController::class, 'index'])->name('stock-outs.index');
        Route::get('stock-outs/{stockOut}', [StockOutController::class, 'show'])->name('stock-outs.show');
    });
    Route::post('stock-outs',                          [StockOutController::class, 'store'])->middleware('permission:barang_keluar.input')->name('stock-outs.store');
    Route::put('stock-outs/{stockOut}',                [StockOutController::class, 'update'])->middleware('permission:barang_keluar.update')->name('stock-outs.update');
    Route::patch('stock-outs/{stockOut}',              [StockOutController::class, 'update']);
    Route::delete('stock-outs/{stockOut}',             [StockOutController::class, 'destroy'])->middleware('permission:barang_keluar.delete')->name('stock-outs.destroy');
    Route::post('stock-outs/{stockOut}/confirm',       [StockOutController::class, 'confirm'])->middleware('permission:barang_keluar.create')->name('stock-outs.confirm');
    Route::post('stock-outs/{stockOut}/unconfirm',     [StockOutController::class, 'unconfirm'])->middleware('permission:barang_keluar.update')->name('stock-outs.unconfirm');

    // ── Transaksi — Transfer Stok ──────────────────────────────────────────
    Route::middleware('permission:transfer_stok.view')->group(function () {
        Route::get('stock-transfers',                [StockTransferController::class, 'index'])->name('stock-transfers.index');
        Route::get('stock-transfers/{stockTransfer}',[StockTransferController::class, 'show'])->name('stock-transfers.show');
    });
    Route::post('stock-transfers',                            [StockTransferController::class, 'store'])->middleware('permission:transfer_stok.input')->name('stock-transfers.store');
    Route::put('stock-transfers/{stockTransfer}',             [StockTransferController::class, 'update'])->middleware('permission:transfer_stok.update')->name('stock-transfers.update');
    Route::patch('stock-transfers/{stockTransfer}',           [StockTransferController::class, 'update']);
    Route::delete('stock-transfers/{stockTransfer}',          [StockTransferController::class, 'destroy'])->middleware('permission:transfer_stok.delete')->name('stock-transfers.destroy');
    Route::post('stock-transfers/{stockTransfer}/confirm',    [StockTransferController::class, 'confirm'])->middleware('permission:transfer_stok.create')->name('stock-transfers.confirm');
    Route::post('stock-transfers/{stockTransfer}/unconfirm',  [StockTransferController::class, 'unconfirm'])->middleware('permission:transfer_stok.update')->name('stock-transfers.unconfirm');

    // ── Transaksi — Stock Opname ───────────────────────────────────────────
    Route::middleware('permission:stock_opname.view')->group(function () {
        Route::get('stock-opnames',                 [StockOpnameController::class, 'index'])->name('stock-opnames.index');
        Route::get('stock-opnames/{stockOpname}',   [StockOpnameController::class, 'show'])->name('stock-opnames.show');
    });
    Route::post('stock-opnames',                            [StockOpnameController::class, 'store'])->middleware('permission:stock_opname.input')->name('stock-opnames.store');
    Route::put('stock-opnames/{stockOpname}',               [StockOpnameController::class, 'update'])->middleware('permission:stock_opname.update')->name('stock-opnames.update');
    Route::patch('stock-opnames/{stockOpname}',             [StockOpnameController::class, 'update']);
    Route::delete('stock-opnames/{stockOpname}',            [StockOpnameController::class, 'destroy'])->middleware('permission:stock_opname.delete')->name('stock-opnames.destroy');
    Route::post('stock-opnames/{stockOpname}/confirm',      [StockOpnameController::class, 'confirm'])->middleware('permission:stock_opname.create')->name('stock-opnames.confirm');
    Route::post('stock-opnames/{stockOpname}/unconfirm',    [StockOpnameController::class, 'unconfirm'])->middleware('permission:stock_opname.update')->name('stock-opnames.unconfirm');

    // ── Transaksi — Retur Barang ───────────────────────────────────────────
    Route::middleware('permission:retur_barang.view')->group(function () {
        Route::get('stock-returns',                 [StockReturnController::class, 'index'])->name('stock-returns.index');
        Route::get('stock-returns/{stockReturn}',   [StockReturnController::class, 'show'])->name('stock-returns.show');
    });
    Route::post('stock-returns',                            [StockReturnController::class, 'store'])->middleware('permission:retur_barang.input')->name('stock-returns.store');
    Route::put('stock-returns/{stockReturn}',               [StockReturnController::class, 'update'])->middleware('permission:retur_barang.update')->name('stock-returns.update');
    Route::patch('stock-returns/{stockReturn}',             [StockReturnController::class, 'update']);
    Route::delete('stock-returns/{stockReturn}',            [StockReturnController::class, 'destroy'])->middleware('permission:retur_barang.delete')->name('stock-returns.destroy');
    Route::post('stock-returns/{stockReturn}/confirm',      [StockReturnController::class, 'confirm'])->middleware('permission:retur_barang.create')->name('stock-returns.confirm');
    Route::post('stock-returns/{stockReturn}/unconfirm',    [StockReturnController::class, 'unconfirm'])->middleware('permission:retur_barang.update')->name('stock-returns.unconfirm');

    // ── Laporan ────────────────────────────────────────────────────────────
    Route::middleware('permission:laporan.view')->group(function () {
        Route::get('/reports/ledger',    [LedgerController::class,    'index'])->name('reports.ledger');
        Route::get('/reports/mutations', [MutationController::class,  'index'])->name('reports.mutations');
        Route::get('/reports/valuation', [ValuationController::class, 'index'])->name('reports.valuation');
        Route::get('/reports/low-stock', [LowStockController::class,  'index'])->name('reports.low-stock');
    });

    // ── Pengaturan — Users ─────────────────────────────────────────────────
    Route::middleware('permission:user.view')->group(function () {
        Route::get('users',          [UserController::class, 'index'])->name('users.index');
        Route::get('users/{user}',   [UserController::class, 'show'])->name('users.show');
    });
    Route::post('users',         [UserController::class, 'store'])->middleware('permission:user.create')->name('users.store');
    Route::put('users/{user}',   [UserController::class, 'update'])->middleware('permission:user.update')->name('users.update');
    Route::patch('users/{user}', [UserController::class, 'update']);
    Route::delete('users/{user}',[UserController::class, 'destroy'])->middleware('permission:user.delete')->name('users.destroy');

    // ── Pengaturan — Roles ─────────────────────────────────────────────────
    Route::middleware('permission:role.view')->group(function () {
        Route::get('roles',          [RoleController::class, 'index'])->name('roles.index');
        Route::get('roles/{role}',   [RoleController::class, 'show'])->name('roles.show');
    });
    Route::post('roles',         [RoleController::class, 'store'])->middleware('permission:role.create')->name('roles.store');
    Route::put('roles/{role}',   [RoleController::class, 'update'])->middleware('permission:role.update')->name('roles.update');
    Route::patch('roles/{role}', [RoleController::class, 'update']);
    Route::delete('roles/{role}',[RoleController::class, 'destroy'])->middleware('permission:role.delete')->name('roles.destroy');

    // ── Notifikasi ─────────────────────────────────────────────────────────
    Route::get('/notification-settings',  [NotificationSettingController::class, 'index'])
        ->middleware('permission:notifikasi.view')->name('notification-settings.index');
    Route::put('/notification-settings',  [NotificationSettingController::class, 'update'])
        ->middleware('permission:notifikasi.update')->name('notification-settings.update');

    // ── Profile & Pengaturan Akun (semua role boleh akses) ─────────────────
    Route::get('/profile',    [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile',  [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/settings/user',           [App\Http\Controllers\UserSettingsController::class, 'index'])->name('settings.user');
    Route::patch('/settings/user/profile', [App\Http\Controllers\UserSettingsController::class, 'updateProfile'])->name('settings.user.profile');
    Route::patch('/settings/user/password',[App\Http\Controllers\UserSettingsController::class, 'updatePassword'])->name('settings.user.password');
    Route::patch('/settings/user/pin',     [App\Http\Controllers\UserSettingsController::class, 'updatePin'])->name('settings.user.pin');

    // ── Role Switch (super-admin only) ─────────────────────────────────────
    Route::post('/role-switch',         [App\Http\Controllers\RoleSwitchController::class, 'switch'])->middleware('role:super-admin|Admin')->name('role-switch.switch');
    Route::post('/role-switch/restore', [App\Http\Controllers\RoleSwitchController::class, 'restore'])->middleware('role:super-admin|Admin')->name('role-switch.restore');
});

require __DIR__.'/auth.php';
