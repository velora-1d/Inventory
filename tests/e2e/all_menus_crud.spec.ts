import { test, expect } from '@playwright/test';

const adminCreds = { email: 'admin@inventory.com', password: 'password' };

test.describe('E2E All Menus & CRUD Testing Flow', () => {

    test.beforeEach(async ({ page }) => {
        await page.goto('/login');
        await page.locator('#email').fill(adminCreds.email);
        await page.locator('#password').fill(adminCreds.password);
        await page.getByRole('button', { name: /Masuk ke Sistem/i }).click();
        await page.waitForURL(/dashboard/);
    });

    test('1. CRUD Data Barang', async ({ page }) => {
        const desktopSidebar = page.locator('aside.hidden.lg\\:flex');
        await desktopSidebar.getByRole('link', { name: 'Data Barang', exact: true }).click();
        await page.waitForURL(/products/);

        // CREATE
        await page.getByRole('button', { name: /Tambah Barang/i }).click();
        const sku = 'SKU-' + Date.now().toString().slice(-6);
        const name = 'Barang E2E ' + Date.now();
        await page.locator('#modal-sku').fill(sku);
        await page.locator('#modal-name').fill(name);
        await page.locator('#modal-unit').selectOption({ index: 1 });
        await page.locator('#modal-category').selectOption({ index: 1 });
        await page.locator('#modal-minstock').fill('10');
        await page.locator('#modal-buyprice').fill('50000');
        await page.locator('#modal-sellprice').fill('75000');
        await page.locator('button[type="submit"]').click();

        // Cek input
        await expect(page.locator('tbody').getByText(name)).toBeVisible({ timeout: 10000 });

        // UPDATE
        const row = page.locator('tbody tr').filter({ hasText: name });
        await row.locator('button[title="Ubah"]').click();
        const updatedName = name + ' Updated';
        await page.locator('#modal-name').fill(updatedName);
        await page.locator('button[type="submit"]').click();
        await expect(page.locator('tbody').getByText(updatedName)).toBeVisible();

        // DELETE
        const updatedRow = page.locator('tbody tr').filter({ hasText: updatedName });
        await updatedRow.locator('button[title="Hapus"]').click();
        await page.locator('.fixed.inset-0.z-50 button:has-text("Ya, Lanjutkan")').click();
        await expect(page.locator('tbody').getByText(updatedName)).not.toBeVisible();
    });

    test('2. CRUD Barang Masuk', async ({ page }) => {
        const desktopSidebar = page.locator('aside.hidden.lg\\:flex');
        await desktopSidebar.getByRole('link', { name: 'Barang Masuk' }).click();
        await page.waitForURL(/stock-ins/);

        // CREATE DRAFT
        await page.getByRole('button', { name: /Buat Transaksi/i }).click();
        await page.locator('select').first().selectOption({ index: 1 }); // Warehouse
        await page.locator('select').nth(1).selectOption({ index: 1 }); // Supplier
        await page.locator('button:has-text("Tambah Baris")').click();
        await page.locator('table select').first().selectOption({ index: 1 }); // Product
        await page.locator('table input[type="number"]').first().fill('10'); // Qty
        await page.locator('button:has-text("Simpan Draft")').click();

        // Tampil di tabel sebagai Draft
        await expect(page.locator('tbody tr').first().locator('text=Draft')).toBeVisible({ timeout: 10000 });
    });

    test('3. CRUD Barang Keluar', async ({ page }) => {
        const desktopSidebar = page.locator('aside.hidden.lg\\:flex');
        await desktopSidebar.getByRole('link', { name: 'Barang Keluar' }).click();
        await page.waitForURL(/stock-outs/);

        // CREATE DRAFT
        await page.getByRole('button', { name: /Buat Transaksi/i }).click();
        await page.locator('select').first().selectOption({ index: 1 }); // Warehouse
        await page.locator('button:has-text("Tambah Baris")').click();
        await page.locator('table select').first().selectOption({ index: 1 }); // Product
        await page.locator('table input[type="number"]').first().fill('5'); // Qty
        await page.locator('button:has-text("Simpan Draft")').click();

        // Tampil di tabel sebagai Draft
        await expect(page.locator('tbody tr').first().locator('text=Draft')).toBeVisible({ timeout: 10000 });
    });

    test('4. CRUD Transfer Stok', async ({ page }) => {
        const desktopSidebar = page.locator('aside.hidden.lg\\:flex');
        await desktopSidebar.getByRole('link', { name: 'Transfer Stok' }).click();
        await page.waitForURL(/stock-transfers/);

        // CREATE DRAFT
        await page.getByRole('button', { name: /Buat Transfer/i }).click();
        await page.locator('select').first().selectOption({ index: 1 }); // Dari Gudang
        await page.locator('select').nth(2).selectOption({ index: 2 }); // Ke Gudang
        await page.locator('button:has-text("Tambah Baris")').click();
        await page.locator('table select').first().selectOption({ index: 1 }); // Product
        await page.locator('table input[type="number"]').first().fill('2'); // Qty
        await page.locator('button:has-text("Simpan Draft")').click();

        // Tampil di tabel sebagai Draft
        await expect(page.locator('tbody tr').first().locator('text=Draft')).toBeVisible({ timeout: 10000 });
    });

    test('5. CRUD Stock Opname', async ({ page }) => {
        const desktopSidebar = page.locator('aside.hidden.lg\\:flex');
        await desktopSidebar.getByRole('link', { name: 'Stock Opname' }).click();
        await page.waitForURL(/stock-opnames/);

        // CREATE DRAFT
        await page.getByRole('button', { name: /Buat Opname/i }).click();
        await page.locator('select').first().selectOption({ index: 1 }); // Warehouse
        await page.waitForTimeout(2000); // Tunggu watcher memuat items barang di local/prod
        await page.locator('button:has-text("Simpan Draft")').click();

        // Tampil di tabel
        await expect(page.locator('tbody tr').first().locator('text=Draft')).toBeVisible({ timeout: 10000 });
    });

    test('6. CRUD Retur Barang', async ({ page }) => {
        const desktopSidebar = page.locator('aside.hidden.lg\\:flex');
        await desktopSidebar.getByRole('link', { name: 'Retur Barang' }).click();
        await page.waitForURL(/stock-returns/);

        // CREATE DRAFT
        await page.getByRole('button', { name: /Buat Retur/i }).click();
        await page.locator('button:has-text("Retur Keluar")').click(); // Type
        await page.locator('select').first().selectOption({ index: 1 }); // Warehouse
        await page.locator('button:has-text("Tambah Baris")').click();
        await page.locator('table select').first().selectOption({ index: 1 }); // Product
        await page.locator('table input[type="number"]').first().fill('1'); // Qty
        await page.locator('button:has-text("Simpan Draft Retur")').click();

        // Tampil di tabel
        await expect(page.locator('tbody tr').first().locator('text=Draft')).toBeVisible({ timeout: 10000 });
    });

    test('7. Laporan Kartu Stok & Mutasi Barang', async ({ page }) => {
        const desktopSidebar = page.locator('aside.hidden.lg\\:flex');

        // Kartu Stok
        await desktopSidebar.getByRole('link', { name: 'Kartu Stok' }).click();
        await page.waitForURL(/reports\/ledger/);
        await expect(page.locator('h2:has-text("Buku Besar Stok")')).toBeVisible();

        // Mutasi Barang
        await desktopSidebar.getByRole('link', { name: 'Mutasi Barang' }).click();
        await page.waitForURL(/reports\/mutations/);
        await expect(page.locator('h2:has-text("Mutasi Stok")')).toBeVisible();
    });

    test('8. Laporan Nilai Persediaan & Stok Minimum', async ({ page }) => {
        const desktopSidebar = page.locator('aside.hidden.lg\\:flex');

        // Nilai Persediaan
        await desktopSidebar.getByRole('link', { name: 'Nilai Persediaan' }).click();
        await page.waitForURL(/reports\/valuation/);
        await expect(page.locator('h2:has-text("Valuasi Stok")')).toBeVisible();

        // Stok Minimum
        await desktopSidebar.getByRole('link', { name: 'Stok Minimum' }).click();
        await page.waitForURL(/reports\/low-stock/);
        await expect(page.locator('h2:has-text("Stok Minimum & Peringatan")')).toBeVisible();
    });

    test('9. Manajemen User & Role Permission', async ({ page }) => {
        const desktopSidebar = page.locator('aside.hidden.lg\\:flex');

        // Manajemen User
        await desktopSidebar.getByRole('link', { name: 'Manajemen User' }).click();
        await page.waitForURL(/users/);
        await expect(page.locator('h2:has-text("Manajemen User")')).toBeVisible();

        // Role & Permission
        await desktopSidebar.getByRole('link', { name: 'Role & Permission' }).click();
        await page.waitForURL(/roles/);
        await expect(page.locator('h2:has-text("Role & Permission")')).toBeVisible();
    });

    test('10. Notifikasi Stok & Pengaturan Akun', async ({ page }) => {
        const desktopSidebar = page.locator('aside.hidden.lg\\:flex');

        // Notifikasi Stok
        await desktopSidebar.getByRole('link', { name: 'Notifikasi Stok' }).click();
        await page.waitForURL(/notifications\/settings/);
        await expect(page.locator('h2:has-text("Pengaturan Notifikasi")')).toBeVisible();

        // Pengaturan Akun
        await page.locator('#dark-mode-toggle').click(); // Test toggle dark mode button
    });
});
