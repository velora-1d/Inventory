import { test, expect } from '@playwright/test';

test.describe('Halaman Login', () => {
    test('tampil dengan benar', async ({ page }) => {
        await page.goto('/login');
        await expect(page).toHaveTitle(/Masuk ke Sistem/);
        await expect(page.locator('h2')).toContainText('Selamat datang');
        await expect(page.locator('#email')).toBeVisible();
        await expect(page.locator('#password')).toBeVisible();
        await expect(page.getByRole('button', { name: /Masuk ke Sistem/i })).toBeVisible();
    });

    test('password - toggle show/hide', async ({ page }) => {
        await page.goto('/login');
        const passwordInput = page.locator('#password');
        // Default type = password
        await expect(passwordInput).toHaveAttribute('type', 'password');
        // Click eye button
        await page.locator('button[type="button"]').last().click();
        await expect(passwordInput).toHaveAttribute('type', 'text');
    });

    test('login sukses redirect ke dashboard', async ({ page }) => {
        await page.goto('/login');
        await page.locator('#email').fill('admin@inventory.com');
        await page.locator('#password').fill('password');
        await page.getByRole('button', { name: /Masuk ke Sistem/i }).click();
        await expect(page).toHaveURL(/dashboard/, { timeout: 8000 });
    });
});

test.describe('Dashboard (authenticated)', () => {
    test.beforeEach(async ({ page }) => {
        // Login dulu
        await page.goto('/login');
        await page.locator('#email').fill('admin@inventory.com');
        await page.locator('#password').fill('password');
        await page.getByRole('button', { name: /Masuk ke Sistem/i }).click();
        await page.waitForURL(/dashboard/);
    });

    test('sidebar tampil dengan menu navigasi', async ({ page }) => {
        // Strict mode fix: target sidebar desktop saja
        const desktopSidebar = page.locator('aside.hidden.lg\\:flex');
        await expect(desktopSidebar).toBeVisible();
        await expect(desktopSidebar.getByText('INVENTORY')).toBeVisible();
    });

    test('card statistik tampil', async ({ page }) => {
        // Gunakan selector spesifik untuk menghindari ambiguity
        await expect(page.locator('main').getByText('Total SKU Aktif', { exact: false })).toBeVisible();
        await expect(page.locator('main').getByText('Nilai Persediaan', { exact: false })).toBeVisible();
    });

    test('navigasi ke Data Barang', async ({ page }) => {
        const desktopSidebar = page.locator('aside.hidden.lg\\:flex');
        await desktopSidebar.getByRole('link', { name: 'Data Barang' }).click();
        await expect(page).toHaveURL(/products/);
    });

    test('navigasi ke Kategori Barang', async ({ page }) => {
        const desktopSidebar = page.locator('aside.hidden.lg\\:flex');
        await desktopSidebar.getByRole('link', { name: 'Kategori Barang' }).click();
        await expect(page).toHaveURL(/categories/);
    });
});
