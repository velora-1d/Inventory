import { test, expect } from '@playwright/test';

const adminCreds = { email: 'admin@inventory.com', password: 'password' };

test.describe('E2E Full Menu & CRUD Testing Flow', () => {

    test.beforeEach(async ({ page }) => {
        await page.goto('/login');
        await page.locator('#email').fill(adminCreds.email);
        await page.locator('#password').fill(adminCreds.password);
        await page.getByRole('button', { name: /Masuk ke Sistem/i }).click();
        await page.waitForURL(/dashboard/);
    });

    test('1. CRUD Kategori Barang', async ({ page }) => {
        const desktopSidebar = page.locator('aside.hidden.lg\\:flex');
        await desktopSidebar.getByRole('link', { name: 'Kategori Barang' }).click();
        await page.waitForURL(/categories/);

        // --- CREATE ---
        await page.getByRole('button', { name: /Tambah Kategori/i }).click();
        
        const catName = 'Kategori Test ' + Date.now();
        await page.locator('#modal-name').fill(catName);
        await page.locator('textarea').fill('Deskripsi kategori test e2e');
        await page.locator('button[type="submit"]').click();

        // Verifikasi hasil input
        await expect(page.locator('tbody').getByText(catName)).toBeVisible({ timeout: 10000 });

        // --- FILTER / SEARCH ---
        await page.locator('input[placeholder*="Cari kategori"]').fill(catName);
        await page.keyboard.press('Enter'); // trigger keyup.enter filters
        await expect(page.locator('tbody').getByText(catName)).toBeVisible();

        // --- UPDATE ---
        // Cari baris kategori, lalu klik tombol dengan attribute title="Ubah"
        const row = page.locator('tbody tr').filter({ hasText: catName });
        await row.locator('button[title="Ubah"]').click();
        const updatedName = catName + ' Updated';
        await page.locator('#modal-name').fill(updatedName);
        await page.locator('button[type="submit"]').click();
        await expect(page.locator('tbody').getByText(updatedName)).toBeVisible();

        // --- DELETE ---
        const updatedRow = page.locator('tbody tr').filter({ hasText: updatedName });
        await updatedRow.locator('button[title="Hapus"]').click();
        
        // Klik tombol konfirmasi modal dengan text 'Hapus'
        await page.locator('.fixed.inset-0.z-50 button:has-text("Hapus")').click();
        await expect(page.locator('tbody').getByText(updatedName)).not.toBeVisible();
    });

    test('2. CRUD Satuan Barang', async ({ page }) => {
        const desktopSidebar = page.locator('aside.hidden.lg\\:flex');
        await desktopSidebar.getByRole('link', { name: 'Satuan Barang' }).click();
        await page.waitForURL(/units/);

        // --- CREATE ---
        await page.getByRole('button', { name: /Tambah Satuan/i }).click();
        const unitName = 'Unit' + String(Date.now()).slice(-4);
        await page.locator('#modal-name').fill(unitName);
        await page.locator('#modal-symbol').fill(unitName.toLowerCase());
        await page.locator('button[type="submit"]').click();
        
        // Cek secara spesifik pada kolom Nama untuk menghindari ambiguity dengan simbol
        await expect(page.locator('tbody tr td').first().locator(`text=${unitName}`)).toBeVisible({ timeout: 10000 });

        // --- UPDATE ---
        const row = page.locator('tbody tr').filter({ hasText: unitName });
        await row.locator('button[title="Ubah"]').click();
        const updatedUnit = unitName + 'U';
        await page.locator('#modal-name').fill(updatedUnit);
        await page.locator('button[type="submit"]').click();
        await expect(page.locator('tbody tr td').locator(`text=${updatedUnit}`)).toBeVisible();

        // --- DELETE ---
        const updatedRow = page.locator('tbody tr').filter({ hasText: updatedUnit });
        await updatedRow.locator('button[title="Hapus"]').click();
        await page.locator('.fixed.inset-0.z-50 button:has-text("Hapus")').click();
        await expect(page.locator('tbody').getByText(updatedUnit)).not.toBeVisible();
    });

    test('3. CRUD Supplier', async ({ page }) => {
        const desktopSidebar = page.locator('aside.hidden.lg\\:flex');
        await desktopSidebar.getByRole('link', { name: 'Supplier' }).click();
        await page.waitForURL(/suppliers/);

        // --- CREATE ---
        await page.getByRole('button', { name: /Tambah Supplier/i }).click();
        const supName = 'SupTest ' + Date.now();
        await page.locator('#modal-name').fill(supName);
        await page.locator('#modal-phone').fill('08123456789');
        await page.locator('#modal-email').fill('suptest@example.com');
        await page.locator('#modal-address').fill('Alamat Supplier Test E2E');
        await page.locator('button[type="submit"]').click();
        await expect(page.locator('tbody').getByText(supName)).toBeVisible({ timeout: 10000 });

        // --- UPDATE ---
        const row = page.locator('tbody tr').filter({ hasText: supName });
        await row.locator('button[title="Ubah"]').click();
        const updatedSup = supName + ' Mod';
        await page.locator('#modal-name').fill(updatedSup);
        await page.locator('button[type="submit"]').click();
        await expect(page.locator('tbody').getByText(updatedSup)).toBeVisible();

        // --- DELETE ---
        const updatedRow = page.locator('tbody tr').filter({ hasText: updatedSup });
        await updatedRow.locator('button[title="Hapus"]').click();
        await page.locator('.fixed.inset-0.z-50 button:has-text("Hapus")').click();
        await expect(page.locator('tbody').getByText(updatedSup)).not.toBeVisible();
    });

    test('4. CRUD Gudang', async ({ page }) => {
        const desktopSidebar = page.locator('aside.hidden.lg\\:flex');
        await desktopSidebar.getByRole('link', { name: 'Gudang/Lokasi' }).click();
        await page.waitForURL(/warehouses/);

        // --- CREATE ---
        await page.getByRole('button', { name: /Tambah Gudang/i }).click();
        const gName = 'GudangTest ' + Date.now();
        await page.locator('#modal-name').fill(gName);
        await page.locator('#modal-code').fill('GDG-' + String(Date.now()).slice(-4));
        await page.locator('#modal-address').fill('Alamat Gudang Test E2E');
        await page.locator('button[type="submit"]').click();
        await expect(page.locator('tbody').getByText(gName)).toBeVisible({ timeout: 10000 });

        // --- UPDATE ---
        const row = page.locator('tbody tr').filter({ hasText: gName });
        await row.locator('button[title="Ubah"]').click();
        const updatedG = gName + ' Mod';
        await page.locator('#modal-name').fill(updatedG);
        await page.locator('button[type="submit"]').click();
        await expect(page.locator('tbody').getByText(updatedG)).toBeVisible();

        // --- DELETE ---
        const updatedRow = page.locator('tbody tr').filter({ hasText: updatedG });
        await updatedRow.locator('button[title="Hapus"]').click();
        await page.locator('.fixed.inset-0.z-50 button:has-text("Hapus")').click();
        await expect(page.locator('tbody').getByText(updatedG)).not.toBeVisible();
    });
});
