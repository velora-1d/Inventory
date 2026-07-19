<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class InventoryMasterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Disable foreign key checks for clean fresh seed
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('stock_return_items')->truncate();
        DB::table('stock_returns')->truncate();
        DB::table('stock_opname_items')->truncate();
        DB::table('stock_opnames')->truncate();
        DB::table('stock_transfer_items')->truncate();
        DB::table('stock_transfers')->truncate();
        DB::table('stock_out_items')->truncate();
        DB::table('stock_outs')->truncate();
        DB::table('stock_in_items')->truncate();
        DB::table('stock_ins')->truncate();
        DB::table('stock_ledgers')->truncate();
        DB::table('stocks')->truncate();
        DB::table('products')->truncate();
        DB::table('suppliers')->truncate();
        DB::table('units')->truncate();
        DB::table('categories')->truncate();
        DB::table('warehouses')->truncate();
        DB::table('users')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 1. Seed Warehouses
        $warehouses = [
            [
                'code' => 'GD-UTAMA',
                'name' => 'Gudang Utama',
                'address' => 'Jl. Industri No. 1, Jakarta Pusat',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'GD-CABANG',
                'name' => 'Gudang Cabang',
                'address' => 'Jl. Raya Merdeka No. 45, Bandung',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($warehouses as $wh) {
            DB::table('warehouses')->insert($wh);
        }

        // Get warehouses for reference
        $gudangUtamaId = DB::table('warehouses')->where('code', 'GD-UTAMA')->value('id');
        $gudangCabangId = DB::table('warehouses')->where('code', 'GD-CABANG')->value('id');

        // 2. Seed Users
        // Admin
        $adminUser = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@inventory.com',
            'password' => Hash::make('password'),
            'warehouse_id' => null,
            'status' => 'active',
        ]);
        $adminUser->assignRole('Admin');

        // Staff Gudang Utama
        $staffUtama = User::create([
            'name' => 'Budi Staff Utama',
            'email' => 'staff.utama@inventory.com',
            'password' => Hash::make('password'),
            'warehouse_id' => $gudangUtamaId,
            'status' => 'active',
        ]);
        $staffUtama->assignRole('Staff Gudang');

        // Staff Gudang Cabang
        $staffCabang = User::create([
            'name' => 'Andi Staff Cabang',
            'email' => 'staff.cabang@inventory.com',
            'password' => Hash::make('password'),
            'warehouse_id' => $gudangCabangId,
            'status' => 'active',
        ]);
        $staffCabang->assignRole('Staff Gudang');

        // Update warehouse PICs
        DB::table('warehouses')->where('id', $gudangUtamaId)->update(['pic_user_id' => $staffUtama->id]);
        DB::table('warehouses')->where('id', $gudangCabangId)->update(['pic_user_id' => $staffCabang->id]);

        // 3. Seed Categories
        $categories = [
            ['name' => 'Elektronik', 'description' => 'Peralatan elektronik dan gadget', 'status' => 'active', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Makanan & Minuman', 'description' => 'Bahan pangan dan minuman kemasan', 'status' => 'active', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Alat Tulis Kantor', 'description' => 'Perlengkapan tulis dan administrasi kantor', 'status' => 'active', 'created_at' => now(), 'updated_at' => now()],
        ];
        DB::table('categories')->insert($categories);

        $catElektronikId = DB::table('categories')->where('name', 'Elektronik')->value('id');
        $catFnbId = DB::table('categories')->where('name', 'Makanan & Minuman')->value('id');
        $catAtkId = DB::table('categories')->where('name', 'Alat Tulis Kantor')->value('id');

        // 4. Seed Units
        $units = [
            ['name' => 'Pcs', 'symbol' => 'pcs', 'status' => 'active', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Dus', 'symbol' => 'dus', 'status' => 'active', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Karton', 'symbol' => 'karton', 'status' => 'active', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Box', 'symbol' => 'box', 'status' => 'active', 'created_at' => now(), 'updated_at' => now()],
        ];
        DB::table('units')->insert($units);

        $unitPcsId = DB::table('units')->where('symbol', 'pcs')->value('id');
        $unitDusId = DB::table('units')->where('symbol', 'dus')->value('id');
        $unitKartonId = DB::table('units')->where('symbol', 'karton')->value('id');
        $unitBoxId = DB::table('units')->where('symbol', 'box')->value('id');

        // 5. Seed Suppliers
        $suppliers = [
            [
                'code' => 'SPL-001',
                'name' => 'PT. Mega Elektronik Indonesia',
                'address' => 'Mangga Dua Square Blok C No. 10, Jakarta Utara',
                'phone' => '021-5551234',
                'email' => 'sales@megaelektronik.co.id',
                'contact_person' => 'Hendra',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'SPL-002',
                'name' => 'CV. Pangan Makmur Lestari',
                'address' => 'Kawasan Industri Candi Blok B No. 3, Semarang',
                'phone' => '024-7654321',
                'email' => 'info@panganmakmur.com',
                'contact_person' => 'Dewi',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];
        DB::table('suppliers')->insert($suppliers);

        $supplierElektronikId = DB::table('suppliers')->where('code', 'SPL-001')->value('id');
        $supplierFnbId = DB::table('suppliers')->where('code', 'SPL-002')->value('id');

        // 6. Seed Products
        $products = [
            [
                'sku' => 'PRD-EL-001',
                'name' => 'Asus ROG Phone 8 Pro',
                'category_id' => $catElektronikId,
                'base_unit_id' => $unitPcsId,
                'purchase_unit_id' => $unitPcsId,
                'sale_unit_id' => $unitPcsId,
                'purchase_price' => 12500000,
                'sale_price' => 15000000,
                'avg_price' => 12500000,
                'min_stock' => 5,
                'default_warehouse_id' => $gudangUtamaId,
                'description' => 'Smartphone gaming premium Asus ROG Phone 8 Pro 16GB/512GB',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'sku' => 'PRD-EL-002',
                'name' => 'Samsung LED Monitor 24"',
                'category_id' => $catElektronikId,
                'base_unit_id' => $unitPcsId,
                'purchase_unit_id' => $unitPcsId,
                'sale_unit_id' => $unitPcsId,
                'purchase_price' => 1400000,
                'sale_price' => 1750000,
                'avg_price' => 1400000,
                'min_stock' => 10,
                'default_warehouse_id' => $gudangUtamaId,
                'description' => 'Samsung IPS Monitor 24 Inch Full HD Bezelless',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'sku' => 'PRD-EL-003',
                'name' => 'Logitech MX Keys Keyboard',
                'category_id' => $catElektronikId,
                'base_unit_id' => $unitPcsId,
                'purchase_unit_id' => $unitPcsId,
                'sale_unit_id' => $unitPcsId,
                'purchase_price' => 1600000,
                'sale_price' => 2100000,
                'avg_price' => 1600000,
                'min_stock' => 8,
                'default_warehouse_id' => $gudangUtamaId,
                'description' => 'Logitech MX Keys Wireless Illuminated Keyboard',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'sku' => 'PRD-FB-001',
                'name' => 'Kopi Kapal Api Murni 200g',
                'category_id' => $catFnbId,
                'base_unit_id' => $unitPcsId,
                'purchase_unit_id' => $unitDusId,
                'sale_unit_id' => $unitPcsId,
                'purchase_price' => 200000, // Per dus (isi 20 pcs)
                'sale_price' => 12500, // Per pcs
                'avg_price' => 10000, // Per pcs
                'min_stock' => 50,
                'default_warehouse_id' => $gudangUtamaId,
                'description' => 'Kopi bubuk Kapal Api Spesial Murni bungkus 200g',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'sku' => 'PRD-FB-002',
                'name' => 'Teh Pucuk Harum 350ml',
                'category_id' => $catFnbId,
                'base_unit_id' => $unitPcsId,
                'purchase_unit_id' => $unitDusId,
                'sale_unit_id' => $unitPcsId,
                'purchase_price' => 54000, // Per dus (isi 24 pcs)
                'sale_price' => 3500, // Per pcs
                'avg_price' => 2250, // Per pcs
                'min_stock' => 100,
                'default_warehouse_id' => $gudangUtamaId,
                'description' => 'Teh botol melati Teh Pucuk Harum 350ml',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'sku' => 'PRD-AT-001',
                'name' => 'Kertas HVS PaperOne A4 80g',
                'category_id' => $catAtkId,
                'base_unit_id' => $unitPcsId, // 1 Pcs = 1 Ream
                'purchase_unit_id' => $unitBoxId, // 1 Box = 5 Ream/Pcs
                'sale_unit_id' => $unitPcsId,
                'purchase_price' => 225000, // Per box
                'sale_price' => 52000, // Per ream
                'avg_price' => 45000,
                'min_stock' => 20,
                'default_warehouse_id' => $gudangUtamaId,
                'description' => 'Kertas fotokopi HVS A4 80gr PaperOne',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'sku' => 'PRD-AT-002',
                'name' => 'Pulpen Pilot G2 Gel 0.5',
                'category_id' => $catAtkId,
                'base_unit_id' => $unitPcsId,
                'purchase_unit_id' => $unitDusId, // 1 Dus = 12 Pcs
                'sale_unit_id' => $unitPcsId,
                'purchase_price' => 180000, // Per dus
                'sale_price' => 18000,
                'avg_price' => 15000,
                'min_stock' => 30,
                'default_warehouse_id' => $gudangUtamaId,
                'description' => 'Pulpen gel hitam Pilot G-2 ukuran mata pena 0.5',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($products as $p) {
            DB::table('products')->insert($p);
        }

        // Get products for reference
        $pAsusId = DB::table('products')->where('sku', 'PRD-EL-001')->value('id');
        $pSamsungId = DB::table('products')->where('sku', 'PRD-EL-002')->value('id');
        $pLogitechId = DB::table('products')->where('sku', 'PRD-EL-003')->value('id');
        $pKopiId = DB::table('products')->where('sku', 'PRD-FB-001')->value('id');
        $pTehId = DB::table('products')->where('sku', 'PRD-FB-002')->value('id');
        $pKertasId = DB::table('products')->where('sku', 'PRD-AT-001')->value('id');
        $pPulpenId = DB::table('products')->where('sku', 'PRD-AT-002')->value('id');

        // 7. Seed Stocks
        $stocks = [
            // Gudang Utama
            ['product_id' => $pAsusId, 'warehouse_id' => $gudangUtamaId, 'qty' => 15, 'created_at' => now(), 'updated_at' => now()],
            ['product_id' => $pSamsungId, 'warehouse_id' => $gudangUtamaId, 'qty' => 20, 'created_at' => now(), 'updated_at' => now()],
            ['product_id' => $pLogitechId, 'warehouse_id' => $gudangUtamaId, 'qty' => 30, 'created_at' => now(), 'updated_at' => now()],
            ['product_id' => $pKopiId, 'warehouse_id' => $gudangUtamaId, 'qty' => 120, 'created_at' => now(), 'updated_at' => now()],
            ['product_id' => $pTehId, 'warehouse_id' => $gudangUtamaId, 'qty' => 240, 'created_at' => now(), 'updated_at' => now()],
            ['product_id' => $pKertasId, 'warehouse_id' => $gudangUtamaId, 'qty' => 50, 'created_at' => now(), 'updated_at' => now()],
            ['product_id' => $pPulpenId, 'warehouse_id' => $gudangUtamaId, 'qty' => 120, 'created_at' => now(), 'updated_at' => now()],

            // Gudang Cabang
            ['product_id' => $pAsusId, 'warehouse_id' => $gudangCabangId, 'qty' => 5, 'created_at' => now(), 'updated_at' => now()],
            ['product_id' => $pSamsungId, 'warehouse_id' => $gudangCabangId, 'qty' => 8, 'created_at' => now(), 'updated_at' => now()],
            ['product_id' => $pLogitechId, 'warehouse_id' => $gudangCabangId, 'qty' => 10, 'created_at' => now(), 'updated_at' => now()],
            ['product_id' => $pKopiId, 'warehouse_id' => $gudangCabangId, 'qty' => 40, 'created_at' => now(), 'updated_at' => now()],
            ['product_id' => $pTehId, 'warehouse_id' => $gudangCabangId, 'qty' => 90, 'created_at' => now(), 'updated_at' => now()],
            ['product_id' => $pKertasId, 'warehouse_id' => $gudangCabangId, 'qty' => 15, 'created_at' => now(), 'updated_at' => now()],
            ['product_id' => $pPulpenId, 'warehouse_id' => $gudangCabangId, 'qty' => 30, 'created_at' => now(), 'updated_at' => now()],
        ];
        DB::table('stocks')->insert($stocks);

        // 8. Seed Stock Ins (Barang Masuk)
        // Completed Stock In
        $stockIn1Id = DB::table('stock_ins')->insertGetId([
            'transaction_no' => 'BM-20260710-0001',
            'transaction_date' => '2026-07-10',
            'supplier_id' => $supplierElektronikId,
            'warehouse_id' => $gudangUtamaId,
            'reference_no' => 'INV/MEGA/10892',
            'notes' => 'Penerimaan berkala peralatan elektronik IT',
            'status' => 'completed',
            'created_by' => $adminUser->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('stock_in_items')->insert([
            ['stock_in_id' => $stockIn1Id, 'product_id' => $pAsusId, 'unit_id' => $unitPcsId, 'qty' => 10, 'qty_base_unit' => 10, 'price' => 12500000, 'subtotal' => 125000000, 'created_at' => now(), 'updated_at' => now()],
            ['stock_in_id' => $stockIn1Id, 'product_id' => $pSamsungId, 'unit_id' => $unitPcsId, 'qty' => 15, 'qty_base_unit' => 15, 'price' => 1400000, 'subtotal' => 21000000, 'created_at' => now(), 'updated_at' => now()],
            ['stock_in_id' => $stockIn1Id, 'product_id' => $pLogitechId, 'unit_id' => $unitPcsId, 'qty' => 20, 'qty_base_unit' => 20, 'price' => 1600000, 'subtotal' => 32000000, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Draft Stock In
        $stockIn2Id = DB::table('stock_ins')->insertGetId([
            'transaction_no' => 'BM-20260715-0002',
            'transaction_date' => '2026-07-15',
            'supplier_id' => $supplierFnbId,
            'warehouse_id' => $gudangUtamaId,
            'reference_no' => 'SJ/PML/7762',
            'notes' => 'Draft penerimaan stok kopi & teh bulanan',
            'status' => 'draft',
            'created_by' => $adminUser->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('stock_in_items')->insert([
            ['stock_in_id' => $stockIn2Id, 'product_id' => $pKopiId, 'unit_id' => $unitPcsId, 'qty' => 50, 'qty_base_unit' => 50, 'price' => 10000, 'subtotal' => 500000, 'created_at' => now(), 'updated_at' => now()],
            ['stock_in_id' => $stockIn2Id, 'product_id' => $pTehId, 'unit_id' => $unitPcsId, 'qty' => 100, 'qty_base_unit' => 100, 'price' => 2250, 'subtotal' => 225000, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 9. Seed Stock Outs (Barang Keluar)
        // Completed Stock Out
        $stockOut1Id = DB::table('stock_outs')->insertGetId([
            'transaction_no' => 'BK-20260712-0001',
            'transaction_date' => '2026-07-12',
            'recipient' => 'Retail Toko Utama',
            'warehouse_id' => $gudangUtamaId,
            'reference_no' => 'REQ/TOKO/8821',
            'notes' => 'Pengiriman barang ke unit retail',
            'status' => 'completed',
            'created_by' => $adminUser->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('stock_out_items')->insert([
            ['stock_out_id' => $stockOut1Id, 'product_id' => $pAsusId, 'unit_id' => $unitPcsId, 'qty' => 2, 'qty_base_unit' => 2, 'price' => 15000000, 'subtotal' => 30000000, 'created_at' => now(), 'updated_at' => now()],
            ['stock_out_id' => $stockOut1Id, 'product_id' => $pSamsungId, 'unit_id' => $unitPcsId, 'qty' => 3, 'qty_base_unit' => 3, 'price' => 1750000, 'subtotal' => 5250000, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Draft Stock Out
        $stockOut2Id = DB::table('stock_outs')->insertGetId([
            'transaction_no' => 'BK-20260716-0002',
            'transaction_date' => '2026-07-16',
            'recipient' => 'Project Office Bandung',
            'warehouse_id' => $gudangUtamaId,
            'reference_no' => 'REQ/PROJ/009',
            'notes' => 'Permintaan keyboard proyek Bandung',
            'status' => 'draft',
            'created_by' => $adminUser->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('stock_out_items')->insert([
            ['stock_out_id' => $stockOut2Id, 'product_id' => $pLogitechId, 'unit_id' => $unitPcsId, 'qty' => 5, 'qty_base_unit' => 5, 'price' => 2100000, 'subtotal' => 10500000, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 10. Seed Stock Transfers
        // Completed Stock Transfer
        $transfer1Id = DB::table('stock_transfers')->insertGetId([
            'transaction_no' => 'TR-20260713-0001',
            'transaction_date' => '2026-07-13',
            'from_warehouse_id' => $gudangUtamaId,
            'to_warehouse_id' => $gudangCabangId,
            'notes' => 'Distribusi stok ke gudang cabang Bandung',
            'status' => 'completed',
            'created_by' => $adminUser->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('stock_transfer_items')->insert([
            ['stock_transfer_id' => $transfer1Id, 'product_id' => $pLogitechId, 'unit_id' => $unitPcsId, 'qty' => 10, 'qty_base_unit' => 10, 'created_at' => now(), 'updated_at' => now()],
            ['stock_transfer_id' => $transfer1Id, 'product_id' => $pKopiId, 'unit_id' => $unitPcsId, 'qty' => 20, 'qty_base_unit' => 20, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Draft Stock Transfer
        $transfer2Id = DB::table('stock_transfers')->insertGetId([
            'transaction_no' => 'TR-20260716-0002',
            'transaction_date' => '2026-07-16',
            'from_warehouse_id' => $gudangUtamaId,
            'to_warehouse_id' => $gudangCabangId,
            'notes' => 'Draft transfer monitor cadangan',
            'status' => 'draft',
            'created_by' => $adminUser->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('stock_transfer_items')->insert([
            ['stock_transfer_id' => $transfer2Id, 'product_id' => $pSamsungId, 'unit_id' => $unitPcsId, 'qty' => 2, 'qty_base_unit' => 2, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 11. Seed Stock Opnames
        // Completed Stock Opname
        $opname1Id = DB::table('stock_opnames')->insertGetId([
            'transaction_no' => 'SO-20260714-0001',
            'opname_date' => '2026-07-14',
            'warehouse_id' => $gudangUtamaId,
            'notes' => 'Stock opname rutin tengah bulan',
            'status' => 'completed',
            'created_by' => $adminUser->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('stock_opname_items')->insert([
            ['stock_opname_id' => $opname1Id, 'product_id' => $pLogitechId, 'system_qty' => 30, 'physical_qty' => 28, 'difference' => -2, 'notes' => '2 unit rusak fisik / patah', 'created_at' => now(), 'updated_at' => now()],
            ['stock_opname_id' => $opname1Id, 'product_id' => $pKopiId, 'system_qty' => 100, 'physical_qty' => 105, 'difference' => 5, 'notes' => 'Lebih kirim dari supplier', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Draft Stock Opname
        $opname2Id = DB::table('stock_opnames')->insertGetId([
            'transaction_no' => 'SO-20260716-0002',
            'opname_date' => '2026-07-16',
            'warehouse_id' => $gudangCabangId,
            'notes' => 'Opname pengecekan HP Asus',
            'status' => 'draft',
            'created_by' => $adminUser->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('stock_opname_items')->insert([
            ['stock_opname_id' => $opname2Id, 'product_id' => $pAsusId, 'system_qty' => 5, 'physical_qty' => 5, 'difference' => 0, 'notes' => 'Kondisi barang baik', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 12. Seed Stock Returns
        // Completed Stock Return (Return Out)
        $return1Id = DB::table('stock_returns')->insertGetId([
            'transaction_no' => 'RT-20260715-0001',
            'return_date' => '2026-07-15',
            'return_type' => 'return_out',
            'reference_type' => 'stock_in',
            'reference_id' => $stockIn1Id,
            'warehouse_id' => $gudangUtamaId,
            'reason' => 'Barang rusak pabrik saat unboxing',
            'status' => 'completed',
            'created_by' => $adminUser->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('stock_return_items')->insert([
            ['stock_return_id' => $return1Id, 'product_id' => $pAsusId, 'unit_id' => $unitPcsId, 'qty' => 1, 'qty_base_unit' => 1, 'notes' => 'Layar LCD bergaris hijau', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Draft Stock Return (Return In)
        $return2Id = DB::table('stock_returns')->insertGetId([
            'transaction_no' => 'RT-20260716-0002',
            'return_date' => '2026-07-16',
            'return_type' => 'return_in',
            'reference_type' => 'stock_out',
            'reference_id' => $stockOut1Id,
            'warehouse_id' => $gudangUtamaId,
            'reason' => 'Customer menukar tipe monitor',
            'status' => 'draft',
            'created_by' => $adminUser->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('stock_return_items')->insert([
            ['stock_return_id' => $return2Id, 'product_id' => $pSamsungId, 'unit_id' => $unitPcsId, 'qty' => 1, 'qty_base_unit' => 1, 'notes' => 'Kotak tersegel, customer ingin monitor 27"', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 13. Seed Stock Ledgers (Audit Logs / Kartu Stok)
        // We will seed the audit path for completed transactions to show realistic histories
        $ledgers = [
            // Asus ROG Phone (PRD-EL-001) in Gudang Utama (Balance history: 0 -> +10 -> -2 -> -1 = 7)
            [
                'product_id' => $pAsusId,
                'warehouse_id' => $gudangUtamaId,
                'transaction_type' => 'in',
                'reference_type' => 'stock_in',
                'reference_id' => $stockIn1Id,
                'unit_id' => $unitPcsId,
                'qty_in' => 10,
                'qty_out' => 0,
                'balance' => 10,
                'price' => 12500000,
                'notes' => 'Barang Masuk #BM-20260710-0001',
                'created_by' => $adminUser->id,
                'transaction_date' => '2026-07-10',
                'created_at' => now(),
            ],
            [
                'product_id' => $pAsusId,
                'warehouse_id' => $gudangUtamaId,
                'transaction_type' => 'out',
                'reference_type' => 'stock_out',
                'reference_id' => $stockOut1Id,
                'unit_id' => $unitPcsId,
                'qty_in' => 0,
                'qty_out' => 2,
                'balance' => 8,
                'price' => 15000000,
                'notes' => 'Barang Keluar #BK-20260712-0001',
                'created_by' => $adminUser->id,
                'transaction_date' => '2026-07-12',
                'created_at' => now(),
            ],
            [
                'product_id' => $pAsusId,
                'warehouse_id' => $gudangUtamaId,
                'transaction_type' => 'return_out',
                'reference_type' => 'stock_return',
                'reference_id' => $return1Id,
                'unit_id' => $unitPcsId,
                'qty_in' => 0,
                'qty_out' => 1,
                'balance' => 7,
                'price' => 12500000,
                'notes' => 'Retur Keluar #RT-20260715-0001',
                'created_by' => $adminUser->id,
                'transaction_date' => '2026-07-15',
                'created_at' => now(),
            ],

            // Samsung Monitor (PRD-EL-002) in Gudang Utama (Balance history: 0 -> +15 -> -3 = 12)
            [
                'product_id' => $pSamsungId,
                'warehouse_id' => $gudangUtamaId,
                'transaction_type' => 'in',
                'reference_type' => 'stock_in',
                'reference_id' => $stockIn1Id,
                'unit_id' => $unitPcsId,
                'qty_in' => 15,
                'qty_out' => 0,
                'balance' => 15,
                'price' => 1400000,
                'notes' => 'Barang Masuk #BM-20260710-0001',
                'created_by' => $adminUser->id,
                'transaction_date' => '2026-07-10',
                'created_at' => now(),
            ],
            [
                'product_id' => $pSamsungId,
                'warehouse_id' => $gudangUtamaId,
                'transaction_type' => 'out',
                'reference_type' => 'stock_out',
                'reference_id' => $stockOut1Id,
                'unit_id' => $unitPcsId,
                'qty_in' => 0,
                'qty_out' => 3,
                'balance' => 12,
                'price' => 1750000,
                'notes' => 'Barang Keluar #BK-20260712-0001',
                'created_by' => $adminUser->id,
                'transaction_date' => '2026-07-12',
                'created_at' => now(),
            ],

            // Logitech Keyboard (PRD-EL-003) (Balance history Gudang Utama: 0 -> +20 -> -10 -> -2 = 8)
            [
                'product_id' => $pLogitechId,
                'warehouse_id' => $gudangUtamaId,
                'transaction_type' => 'in',
                'reference_type' => 'stock_in',
                'reference_id' => $stockIn1Id,
                'unit_id' => $unitPcsId,
                'qty_in' => 20,
                'qty_out' => 0,
                'balance' => 20,
                'price' => 1600000,
                'notes' => 'Barang Masuk #BM-20260710-0001',
                'created_by' => $adminUser->id,
                'transaction_date' => '2026-07-10',
                'created_at' => now(),
            ],
            [
                'product_id' => $pLogitechId,
                'warehouse_id' => $gudangUtamaId,
                'transaction_type' => 'transfer_out',
                'reference_type' => 'stock_transfer',
                'reference_id' => $transfer1Id,
                'unit_id' => $unitPcsId,
                'qty_in' => 0,
                'qty_out' => 10,
                'balance' => 10,
                'price' => 1600000,
                'notes' => 'Transfer Keluar #TR-20260713-0001',
                'created_by' => $adminUser->id,
                'transaction_date' => '2026-07-13',
                'created_at' => now(),
            ],
            [
                'product_id' => $pLogitechId,
                'warehouse_id' => $gudangUtamaId,
                'transaction_type' => 'adjustment',
                'reference_type' => 'stock_opname',
                'reference_id' => $opname1Id,
                'unit_id' => $unitPcsId,
                'qty_in' => 0,
                'qty_out' => 2,
                'balance' => 8,
                'price' => 0,
                'notes' => 'Penyesuaian Opname #SO-20260714-0001',
                'created_by' => $adminUser->id,
                'transaction_date' => '2026-07-14',
                'created_at' => now(),
            ],

            // Logitech Keyboard (PRD-EL-003) (Balance history Gudang Cabang: 0 -> +10 = 10)
            [
                'product_id' => $pLogitechId,
                'warehouse_id' => $gudangCabangId,
                'transaction_type' => 'transfer_in',
                'reference_type' => 'stock_transfer',
                'reference_id' => $transfer1Id,
                'unit_id' => $unitPcsId,
                'qty_in' => 10,
                'qty_out' => 0,
                'balance' => 10,
                'price' => 1600000,
                'notes' => 'Transfer Masuk #TR-20260713-0001',
                'created_by' => $adminUser->id,
                'transaction_date' => '2026-07-13',
                'created_at' => now(),
            ],
        ];

        DB::table('stock_ledgers')->insert($ledgers);
    }
}
