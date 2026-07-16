<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Define permissions grouped by module
        $permissions = [
            // Dashboard
            'dashboard.view',

            // Data Barang
            'barang.view',
            'barang.create',
            'barang.update',
            'barang.delete',

            // Kategori Barang
            'kategori.view',
            'kategori.create',
            'kategori.update',
            'kategori.delete',

            // Satuan Barang
            'satuan.view',
            'satuan.create',
            'satuan.update',
            'satuan.delete',

            // Supplier
            'supplier.view',
            'supplier.create',
            'supplier.update',
            'supplier.delete',

            // Gudang/Lokasi
            'gudang.view',
            'gudang.create',
            'gudang.update',
            'gudang.delete',

            // Barang Masuk
            'barang_masuk.view',
            'barang_masuk.create',
            'barang_masuk.update',
            'barang_masuk.delete',
            'barang_masuk.input',

            // Barang Keluar
            'barang_keluar.view',
            'barang_keluar.create',
            'barang_keluar.update',
            'barang_keluar.delete',
            'barang_keluar.input',

            // Transfer Stok
            'transfer_stok.view',
            'transfer_stok.create',
            'transfer_stok.update',
            'transfer_stok.delete',
            'transfer_stok.input',

            // Stock Opname
            'stock_opname.view',
            'stock_opname.create',
            'stock_opname.update',
            'stock_opname.delete',
            'stock_opname.input',

            // Retur Barang
            'retur_barang.view',
            'retur_barang.create',
            'retur_barang.update',
            'retur_barang.delete',
            'retur_barang.input',

            // Laporan
            'laporan.view',

            // Manajemen User
            'user.view',
            'user.create',
            'user.update',
            'user.delete',

            // Manajemen Role & Permission
            'role.view',
            'role.create',
            'role.update',
            'role.delete',

            // Pengaturan Notifikasi
            'notifikasi.view',
            'notifikasi.update',
        ];

        // Create permissions
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Create Admin Role
        $adminRole = Role::firstOrCreate([
            'name' => 'Admin',
            'guard_name' => 'web'
        ], [
            'description' => 'Administrator Utama dengan akses penuh ke seluruh modul'
        ]);
        // Give all permissions to Admin
        $adminRole->syncPermissions(Permission::all());

        // Create Staff Gudang Role
        $staffRole = Role::firstOrCreate([
            'name' => 'Staff Gudang',
            'guard_name' => 'web'
        ], [
            'description' => 'Staff Operasional Gudang yang mengelola transaksi barang masuk, keluar, transfer, retur, dan opname'
        ]);
        
        // Give specific permissions to Staff Gudang
        $staffRole->syncPermissions([
            'dashboard.view',
            'barang.view',
            
            'barang_masuk.view',
            'barang_masuk.input',
            
            'barang_keluar.view',
            'barang_keluar.input',
            
            'transfer_stok.view',
            'transfer_stok.input',
            
            'stock_opname.view',
            'stock_opname.input',
            
            'retur_barang.view',
            'retur_barang.input',
            
            'laporan.view',
        ]);
    }
}
