/**
 * Format angka dengan pemisah ribuan (titik) — tanpa desimal
 * Contoh: 1600000 → "1.600.000"
 */
export const formatNumber = (n: number | null | undefined): string => {
    const val = Number(n ?? 0);
    const isInteger = Math.abs(val % 1) < 0.005;
    return new Intl.NumberFormat('id-ID', {
        minimumFractionDigits: 0,
        maximumFractionDigits: isInteger ? 0 : 2,
    }).format(isInteger ? Math.round(val) : val);
};

/**
 * Format mata uang Rupiah — tanpa desimal, pakai titik ribuan
 * Contoh: 1600000 → "Rp 1.600.000"
 */
export const formatCurrency = (n: number | null | undefined): string => {
    return 'Rp ' + formatNumber(n);
};

/**
 * Format angka qty — tampilkan desimal hanya jika memang ada
 * Contoh: 1.5 → "1,5" | 2.0 → "2"
 */
export const formatQty = (n: number | null | undefined): string => {
    const val = Number(n ?? 0);
    const isInteger = Math.abs(val % 1) < 0.005;
    return new Intl.NumberFormat('id-ID', {
        maximumFractionDigits: isInteger ? 0 : 2,
    }).format(val);
};
