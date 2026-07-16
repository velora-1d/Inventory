<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

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
    : 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300';

const fillPct = (qty: number, min: number) => min > 0 ? Math.min((qty / min) * 100, 100) : 0;
</script>

<template>
    <Head title="Stok Rendah" />
    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">Laporan Stok Rendah</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Produk yang membutuhkan segera diisi ulang</p>
            </div>
        </template>

        <div class="py-6 px-4 sm:px-6 lg:px-8 space-y-5">
            <!-- Summary Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <!-- Critical: stock = 0 -->
                <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-2xl p-5">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 rounded-xl bg-red-100 dark:bg-red-900/50 flex items-center justify-center">
                            <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.962-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-red-600 dark:text-red-400 uppercase tracking-wider">Kritis (Habis)</p>
                        </div>
                    </div>
                    <p class="text-4xl font-black text-red-700 dark:text-red-300">{{ summary.critical }}</p>
                    <p class="text-xs text-red-500 dark:text-red-400 mt-1">Stok = 0, perlu restok segera</p>
                </div>

                <!-- Below minimum -->
                <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-2xl p-5">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-900/50 flex items-center justify-center">
                            <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/></svg>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-amber-600 dark:text-amber-400 uppercase tracking-wider">Di Bawah Minimum</p>
                        </div>
                    </div>
                    <p class="text-4xl font-black text-amber-700 dark:text-amber-300">{{ summary.below_min }}</p>
                    <p class="text-xs text-amber-500 dark:text-amber-400 mt-1">Stok rendah, segera rencanakan</p>
                </div>

                <!-- Total -->
                <div class="bg-surface-warm dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl p-5">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 rounded-xl bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                            <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Perlu Restok</p>
                        </div>
                    </div>
                    <p class="text-4xl font-black text-gray-800 dark:text-gray-200">{{ summary.total }}</p>
                    <p class="text-xs text-gray-400 mt-1">Gabungan kritis + di bawah min</p>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-surface-warm dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                    <input v-model="search" @keyup.enter="applyFilters" type="text" placeholder="Cari produk / SKU..."
                        class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-red-500"/>
                    <select v-model="severityFilter" @change="applyFilters"
                        class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-red-500">
                        <option value="">Semua Tingkat</option>
                        <option value="critical">Kritis (Stok Habis)</option>
                        <option value="low">Di Bawah Minimum</option>
                    </select>
                    <select v-model="warehouseFilter" @change="applyFilters"
                        class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-red-500">
                        <option value="">Semua Gudang</option>
                        <option v-for="w in warehouses" :key="w.id" :value="w.id">{{ w.name }}</option>
                    </select>
                    <select v-model="categoryFilter" @change="applyFilters"
                        class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-red-500">
                        <option value="">Semua Kategori</option>
                        <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
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
                                <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                <th class="text-right px-4 py-3 text-xs font-semibold text-red-600 dark:text-red-400 uppercase tracking-wider">Stok Saat Ini</th>
                                <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Stok Minimum</th>
                                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-36">Level</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 dark:divide-gray-700/50">
                            <tr v-if="lowStocks.data.length === 0">
                                <td colspan="7" class="text-center py-16 text-gray-400 dark:text-gray-500">
                                    <div class="w-16 h-16 mx-auto mb-4 bg-emerald-100 dark:bg-emerald-900/30 rounded-2xl flex items-center justify-center">
                                        <svg class="w-8 h-8 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </div>
                                    <p class="font-semibold text-gray-700 dark:text-gray-300">Stok semua produk dalam kondisi baik! 🎉</p>
                                    <p class="text-xs mt-1">Tidak ada produk yang berada di bawah stok minimum</p>
                                </td>
                            </tr>
                            <tr v-for="s in lowStocks.data" :key="`${s.product_id}-${s.warehouse_id}`" :class="['transition-colors', rowClass(s.qty)]">
                                <td class="px-4 py-3">
                                    <p class="font-medium text-gray-800 dark:text-gray-200">{{ s.product?.name }}</p>
                                    <p class="text-xs text-gray-400 font-mono">{{ s.product?.sku }}</p>
                                </td>
                                <td class="px-4 py-3">
                                    <p class="text-gray-700 dark:text-gray-300">{{ s.warehouse?.name }}</p>
                                    <p class="text-xs text-gray-400">{{ s.warehouse?.code }}</p>
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-500 dark:text-gray-400">{{ s.product?.category?.name ?? '-' }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span :class="severityBadge(s.qty)" class="px-2.5 py-1 rounded-full text-xs font-semibold">
                                        {{ s.qty === 0 ? '⚠ Kritis' : '▼ Rendah' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <span :class="stockClass(s.qty)">{{ formatNumber(s.qty) }}</span>
                                    <span class="text-xs text-gray-400 font-normal ml-1">{{ s.product?.baseUnit?.symbol }}</span>
                                </td>
                                <td class="px-4 py-3 text-right text-gray-500 dark:text-gray-400">{{ formatNumber(s.product?.min_stock ?? 0) }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1 bg-gray-100 dark:bg-gray-700 rounded-full h-2">
                                            <div :class="s.qty === 0 ? 'bg-red-500' : 'bg-amber-500'" class="h-2 rounded-full transition-all duration-500"
                                                :style="{ width: fillPct(s.qty, s.product?.min_stock ?? 1) + '%' }"/>
                                        </div>
                                        <span class="text-xs text-gray-400 w-8 text-right">{{ fillPct(s.qty, s.product?.min_stock ?? 1).toFixed(0) }}%</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-if="lowStocks.last_page > 1" class="px-4 py-3 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ lowStocks.total }} item stok rendah</p>
                    <div class="flex gap-1">
                        <button v-for="link in lowStocks.links" :key="link.label" :disabled="!link.url"
                            @click="link.url && router.get(link.url)" v-html="link.label"
                            :class="['px-3 py-1 text-xs rounded-lg border transition-colors', link.active ? 'bg-red-600 border-red-600 text-white' : 'border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-40']"/>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
