<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { exportToExcel, exportToDocs, exportToPDF } from '@/exportHelper';

interface Warehouse { id: number; name: string; code: string; }
interface Category  { id: number; name: string; }
interface LowStockRow {
    product_id: number; warehouse_id: number; qty: number;
    product: { name: string; sku: string; min_stock: number; category: { name: string } | null; baseUnit: { symbol: string } | null };
    warehouse: Warehouse;
}
interface Pagination<T> { data: T[]; current_page: number; last_page: number; per_page: number; total: number; links: { url: string | null; label: string; active: boolean }[]; }

const props = defineProps<{
    lowStocks: Pagination<LowStockRow>;
    summary: { critical: number; below_min: number; total: number };
    warehouses: Warehouse[];
    categories: Category[];
    filters: { warehouse_id?: string; category_id?: string; severity?: string; search?: string };
}>();

const search          = ref(props.filters.search ?? '');
const warehouseFilter = ref(props.filters.warehouse_id ?? '');
const categoryFilter  = ref(props.filters.category_id ?? '');
const severityFilter  = ref(props.filters.severity ?? '');

const applyFilters = () => {
    router.get(route('reports.low-stock'), {
        search:       search.value || undefined,
        warehouse_id: warehouseFilter.value || undefined,
        category_id:  categoryFilter.value || undefined,
        severity:     severityFilter.value || undefined,
    }, { preserveState: true, replace: true });
};

const formatNumber = (n: number) => new Intl.NumberFormat('id-ID').format(n ?? 0);

const stockClass = (qty: number) => qty === 0
    ? 'text-red-600 dark:text-red-400 font-bold'
    : 'text-amber-600 dark:text-amber-400 font-semibold';

const rowClass = (qty: number) => qty === 0
    ? 'bg-red-50/30 dark:bg-red-900/10 hover:bg-red-50/50 dark:hover:bg-red-900/20'
    : 'hover:bg-amber-50/30 dark:hover:bg-amber-900/10';

const severityBadge = (qty: number) => qty === 0
    ? 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300'
    : 'bg-amber-100 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400';

const fillPct = (qty: number, min: number) => min > 0 ? Math.min((qty / min) * 100, 100) : 0;

const triggerExport = (format: 'excel' | 'docs' | 'pdf') => {
    if (format === 'pdf') {
        exportToPDF();
        return;
    }
    const headers = ['Produk', 'SKU', 'Gudang', 'Kategori', 'Stok Saat Ini', 'Stok Minimum', 'Status'];
    const rows = props.lowStocks.data.map(l => [
        l.product?.name,
        l.product?.sku,
        l.warehouse?.name,
        l.product?.category?.name || 'Tanpa Kategori',
        `${l.qty} ${l.product?.baseUnit?.symbol || ''}`,
        `${l.product?.min_stock} ${l.product?.baseUnit?.symbol || ''}`,
        l.qty === 0 ? 'Habis (Kritis)' : 'Di Bawah Minimum'
    ]);
    if (format === 'excel') {
        exportToExcel('Laporan Stok Rendah', headers, rows, 'Stok_Rendah');
    } else {
        exportToDocs('Laporan Stok Rendah', headers, rows, 'Stok_Rendah');
    }
};
</script>

<template>
    <Head title="Stok Rendah" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between w-full">
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Laporan Stok Rendah</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Produk yang membutuhkan segera diisi ulang</p>
                </div>
                <div class="flex items-center gap-2 print-hidden">
                    <button @click="triggerExport('excel')" class="px-3.5 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl transition-all shadow-sm active:scale-95 duration-100 flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Excel
                    </button>
                    <button @click="triggerExport('docs')" class="px-3.5 py-2 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-xl transition-all shadow-sm active:scale-95 duration-100 flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Word
                    </button>
                    <button @click="triggerExport('pdf')" class="px-3.5 py-2 text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 rounded-xl transition-all shadow-sm active:scale-95 duration-100 flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        PDF
                    </button>
                </div>
            </div>
        </template>

        <div class="py-6 px-4 sm:px-6 lg:px-8 space-y-5">
            <!-- Summary Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <!-- Critical: stock = 0 -->
                <div class="bg-surface-warm dark:bg-gray-800 border border-gray-150 dark:border-gray-700 rounded-2xl p-5 shadow-sm relative overflow-hidden">
                    <div class="absolute top-0 left-0 right-0 h-1 bg-red-500"></div>
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 rounded-xl bg-red-50 dark:bg-red-950/30 flex items-center justify-center">
                            <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.962-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Kritis (Habis)</p>
                        </div>
                    </div>
                    <p class="text-4xl font-black text-text-primary">{{ summary.critical }}</p>
                    <p class="text-xs text-gray-400 mt-1">Stok = 0, perlu restok segera</p>
                </div>

                <!-- Below minimum -->
                <div class="bg-surface-warm dark:bg-gray-800 border border-gray-150 dark:border-gray-700 rounded-2xl p-5 shadow-sm relative overflow-hidden">
                    <div class="absolute top-0 left-0 right-0 h-1 bg-amber-500"></div>
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-950/30 flex items-center justify-center">
                            <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/></svg>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Di Bawah Minimum</p>
                        </div>
                    </div>
                    <p class="text-4xl font-black text-text-primary">{{ summary.below_min }}</p>
                    <p class="text-xs text-gray-400 mt-1">Stok rendah, segera rencanakan</p>
                </div>

                <!-- Total -->
                <div class="bg-surface-warm dark:bg-gray-800 border border-gray-150 dark:border-gray-700 rounded-2xl p-5 shadow-sm relative overflow-hidden">
                    <div class="absolute top-0 left-0 right-0 h-1 bg-gray-400"></div>
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 rounded-xl bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                            <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Item Dipantau</p>
                        </div>
                    </div>
                    <p class="text-4xl font-black text-text-primary">{{ summary.total }}</p>
                    <p class="text-xs text-gray-400 mt-1">Total produk-gudang terdata</p>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-surface-warm dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-4 print-hidden">
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                    <input v-model="search" @keyup.enter="applyFilters" type="text" placeholder="Cari produk / SKU..."
                        class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-orange-500"/>
                    <select v-model="warehouseFilter" @change="applyFilters"
                        class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-orange-500">
                        <option value="">Semua Gudang</option>
                        <option v-for="w in warehouses" :key="w.id" :value="w.id">{{ w.name }}</option>
                    </select>
                    <select v-model="categoryFilter" @change="applyFilters"
                        class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-orange-500">
                        <option value="">Semua Kategori</option>
                        <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
                    </select>
                    <select v-model="severityFilter" @change="applyFilters"
                        class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-orange-500">
                        <option value="">Semua Tingkat Keparahan</option>
                        <option value="critical">Habis (Kritis)</option>
                        <option value="warning">Di Bawah Minimum</option>
                    </select>
                </div>
            </div>

            <!-- Table -->
            <div class="bg-surface-warm dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-100 dark:border-gray-700">
                            <tr>
                                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Produk</th>
                                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Gudang</th>
                                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Kategori</th>
                                <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Stok Saat Ini</th>
                                <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Batas Minimum</th>
                                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-36 print-hidden">Pemenuhan</th>
                                <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 dark:divide-gray-700/50">
                            <tr v-if="lowStocks.data.length === 0">
                                <td colspan="7" class="text-center py-16 text-gray-400 dark:text-gray-500">
                                    <svg class="w-12 h-12 mx-auto mb-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    <p class="font-medium">Stok dalam kondisi aman</p>
                                    <p class="text-xs mt-1">Tidak ada produk di bawah batas minimum</p>
                                </td>
                            </tr>
                            <tr v-for="l in lowStocks.data" :key="`${l.product_id}-${l.warehouse_id}`" :class="rowClass(l.qty)" class="transition-colors">
                                <td class="px-4 py-3">
                                    <p class="font-medium text-gray-800 dark:text-gray-200">{{ l.product?.name }}</p>
                                    <p class="text-xs text-gray-400 font-mono">{{ l.product?.sku }}</p>
                                </td>
                                <td class="px-4 py-3 text-gray-700 dark:text-gray-300">
                                    <p>{{ l.warehouse?.name }}</p>
                                    <p class="text-xs text-gray-404 font-mono">{{ l.warehouse?.code }}</p>
                                </td>
                                <td class="px-4 py-3 text-gray-600 dark:text-gray-400">
                                    <span class="px-2 py-0.5 rounded bg-gray-100 dark:bg-gray-750 text-xs font-semibold">
                                        {{ l.product?.category?.name || 'Tanpa Kategori' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right" :class="stockClass(l.qty)">
                                    {{ formatNumber(l.qty) }} <span class="text-xs font-normal text-gray-404">{{ l.product?.baseUnit?.symbol }}</span>
                                </td>
                                <td class="px-4 py-3 text-right font-semibold text-gray-800 dark:text-gray-250">
                                    {{ formatNumber(l.product?.min_stock) }} <span class="text-xs font-normal text-gray-404">{{ l.product?.baseUnit?.symbol }}</span>
                                </td>
                                <td class="px-4 py-3 print-hidden">
                                    <div class="flex items-center gap-2">
                                        <div class="w-full bg-gray-200 dark:bg-gray-700 h-1.5 rounded-full overflow-hidden">
                                            <div :class="l.qty === 0 ? 'bg-red-500' : 'bg-amber-500'" class="h-1.5 rounded-full transition-all duration-500" :style="{ width: fillPct(l.qty, l.product?.min_stock) + '%' }" />
                                        </div>
                                        <span class="text-xs font-semibold text-gray-404 w-8 text-right">{{ fillPct(l.qty, l.product?.min_stock).toFixed(0) }}%</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span :class="severityBadge(l.qty)" class="px-2 py-0.5 rounded-full text-xs font-semibold whitespace-nowrap">
                                        {{ l.qty === 0 ? 'Habis' : 'Rendah' }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <!-- Pagination -->
                <div v-if="lowStocks.last_page > 1" class="px-4 py-3 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between print-hidden">
                    <p class="text-xs text-gray-500 dark:text-gray-400">Menampilkan {{ lowStocks.data.length }} dari {{ lowStocks.total }} records</p>
                    <div class="flex gap-1">
                        <button v-for="link in lowStocks.links" :key="link.label" :disabled="!link.url"
                            @click="link.url && router.get(link.url)" v-html="link.label"
                            :class="['px-3 py-1 text-xs rounded-lg border transition-colors', link.active ? 'bg-orange-600 border-orange-600 text-white' : 'border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-40']"/>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
