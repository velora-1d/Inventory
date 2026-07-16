<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

interface Warehouse { id: number; name: string; code: string; }
interface Category  { id: number; name: string; }
interface StockRow {
    product_id: number; warehouse_id: number; qty: number; total_value: number;
    product: { name: string; sku: string; avg_price: number; category: { name: string } | null; baseUnit: { symbol: string } | null };
    warehouse: Warehouse;
}
interface Pagination<T> { data: T[]; current_page: number; last_page: number; per_page: number; total: number; links: { url: string | null; label: string; active: boolean }[]; }

const props = defineProps<{
    stocks: Pagination<StockRow>;
    totalValue: number;
    warehouses: Warehouse[];
    categories: Category[];
    filters: { warehouse_id?: string; category_id?: string; search?: string };
}>();

const search          = ref(props.filters.search ?? '');
const warehouseFilter = ref(props.filters.warehouse_id ?? '');
const categoryFilter  = ref(props.filters.category_id ?? '');

const applyFilters = () => {
    router.get(route('reports.valuation'), {
        search:       search.value || undefined,
        warehouse_id: warehouseFilter.value || undefined,
        category_id:  categoryFilter.value || undefined,
    }, { preserveState: true, replace: true });
};

const formatNumber   = (n: number) => new Intl.NumberFormat('id-ID').format(n ?? 0);
const formatCurrency = (n: number) => 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(n ?? 0));

// percentage of total for progress bar
const pct = (v: number) => props.totalValue > 0 ? Math.min((v / props.totalValue) * 100, 100) : 0;
</script>

<template>
    <Head title="Valuasi Stok" />
    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">Valuasi Stok</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Nilai total persediaan berdasarkan harga rata-rata (average cost)</p>
            </div>
        </template>

        <div class="py-6 px-4 sm:px-6 lg:px-8 space-y-5">
            <!-- Total Value Hero Card -->
            <div class="bg-gradient-to-br from-indigo-600 to-violet-600 rounded-2xl p-6 text-white shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-indigo-200 text-sm font-medium">Total Nilai Persediaan</p>
                        <p class="text-4xl font-bold mt-1">{{ formatCurrency(totalValue) }}</p>
                        <p class="text-indigo-200 text-xs mt-2">{{ stocks.total }} item produk-gudang</p>
                    </div>
                    <div class="w-20 h-20 bg-surface-warm/10 rounded-2xl flex items-center justify-center">
                        <svg class="w-10 h-10 text-white/70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-surface-warm dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-4">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <input v-model="search" @keyup.enter="applyFilters" type="text" placeholder="Cari produk / SKU..."
                        class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500"/>
                    <select v-model="warehouseFilter" @change="applyFilters"
                        class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Semua Gudang</option>
                        <option v-for="w in warehouses" :key="w.id" :value="w.id">{{ w.name }}</option>
                    </select>
                    <select v-model="categoryFilter" @change="applyFilters"
                        class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500">
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
                                <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Stok</th>
                                <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Harga Rata-rata</th>
                                <th class="text-right px-4 py-3 text-xs font-semibold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider">Nilai Total</th>
                                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-32">Proporsi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 dark:divide-gray-700/50">
                            <tr v-if="stocks.data.length === 0">
                                <td colspan="7" class="text-center py-16 text-gray-400 dark:text-gray-500">
                                    <svg class="w-12 h-12 mx-auto mb-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <p class="font-medium">Belum ada data valuasi</p>
                                </td>
                            </tr>
                            <tr v-for="s in stocks.data" :key="`${s.product_id}-${s.warehouse_id}`" class="hover:bg-indigo-50/30 dark:hover:bg-indigo-900/10 transition-colors">
                                <td class="px-4 py-3">
                                    <p class="font-medium text-gray-800 dark:text-gray-200">{{ s.product?.name }}</p>
                                    <p class="text-xs text-gray-400 font-mono">{{ s.product?.sku }}</p>
                                </td>
                                <td class="px-4 py-3">
                                    <p class="text-gray-700 dark:text-gray-300">{{ s.warehouse?.name }}</p>
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-500 dark:text-gray-400">{{ s.product?.category?.name ?? '-' }}</td>
                                <td class="px-4 py-3 text-right font-semibold text-gray-800 dark:text-gray-200">{{ formatNumber(s.qty) }} <span class="text-xs text-gray-400 font-normal">{{ s.product?.baseUnit?.symbol }}</span></td>
                                <td class="px-4 py-3 text-right text-gray-600 dark:text-gray-300">{{ formatCurrency(s.product?.avg_price ?? 0) }}</td>
                                <td class="px-4 py-3 text-right font-bold text-indigo-600 dark:text-indigo-400">{{ formatCurrency(s.total_value) }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1 bg-gray-100 dark:bg-gray-700 rounded-full h-1.5">
                                            <div class="bg-indigo-500 h-1.5 rounded-full transition-all duration-500" :style="{ width: pct(s.total_value) + '%' }"/>
                                        </div>
                                        <span class="text-xs text-gray-400 w-10 text-right">{{ pct(s.total_value).toFixed(1) }}%</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-if="stocks.last_page > 1" class="px-4 py-3 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ stocks.total }} item</p>
                    <div class="flex gap-1">
                        <button v-for="link in stocks.links" :key="link.label" :disabled="!link.url"
                            @click="link.url && router.get(link.url)" v-html="link.label"
                            :class="['px-3 py-1 text-xs rounded-lg border transition-colors', link.active ? 'bg-indigo-600 border-indigo-600 text-white' : 'border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-40']"/>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
