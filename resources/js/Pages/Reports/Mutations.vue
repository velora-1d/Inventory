<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

interface Product   { id: number; name: string; sku: string; }
interface Warehouse { id: number; name: string; code: string; }
interface MutationRow {
    product_id: number; warehouse_id: number;
    total_in: number; total_out: number; net_change: number;
    latest_balance: number; transaction_count: number;
    product: { name: string; sku: string; baseUnit: { symbol: string } | null };
    warehouse: Warehouse;
}
interface Pagination<T> { data: T[]; current_page: number; last_page: number; per_page: number; total: number; links: { url: string | null; label: string; active: boolean }[]; }

const props = defineProps<{
    mutations: Pagination<MutationRow>;
    summary: { total_in: number; total_out: number; transactions: number };
    products: Product[];
    warehouses: Warehouse[];
    filters: { product_id?: string; warehouse_id?: string; date_from?: string; date_to?: string };
}>();

const productFilter   = ref(props.filters.product_id ?? '');
const warehouseFilter = ref(props.filters.warehouse_id ?? '');
const dateFrom        = ref(props.filters.date_from ?? '');
const dateTo          = ref(props.filters.date_to ?? '');

const applyFilters = () => {
    router.get(route('reports.mutations'), {
        product_id:   productFilter.value || undefined,
        warehouse_id: warehouseFilter.value || undefined,
        date_from:    dateFrom.value || undefined,
        date_to:      dateTo.value || undefined,
    }, { preserveState: true, replace: true });
};

const formatNumber = (n: number) => new Intl.NumberFormat('id-ID').format(n ?? 0);
const netClass = (n: number) => n > 0 ? 'text-emerald-600 dark:text-emerald-400' : n < 0 ? 'text-rose-600 dark:text-rose-400' : 'text-gray-400';
</script>

<template>
    <Head title="Mutasi Stok" />
    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">Laporan Mutasi Stok</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Ringkasan pergerakan stok masuk dan keluar per produk</p>
            </div>
        </template>

        <div class="py-6 px-4 sm:px-6 lg:px-8 space-y-5">
            <!-- Summary Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-surface-warm dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-5 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"/></svg>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Total Masuk</p>
                        <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ formatNumber(summary.total_in) }}</p>
                    </div>
                </div>
                <div class="bg-surface-warm dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-5 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-rose-100 dark:bg-rose-900/30 flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 text-rose-600 dark:text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"/></svg>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Total Keluar</p>
                        <p class="text-2xl font-bold text-rose-600 dark:text-rose-400">{{ formatNumber(summary.total_out) }}</p>
                    </div>
                </div>
                <div class="bg-surface-warm dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-5 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Jumlah Transaksi</p>
                        <p class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">{{ formatNumber(summary.transactions) }}</p>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-surface-warm dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                    <select v-model="productFilter" @change="applyFilters"
                        class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Semua Produk</option>
                        <option v-for="p in products" :key="p.id" :value="p.id">{{ p.sku }} — {{ p.name }}</option>
                    </select>
                    <select v-model="warehouseFilter" @change="applyFilters"
                        class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Semua Gudang</option>
                        <option v-for="w in warehouses" :key="w.id" :value="w.id">{{ w.name }}</option>
                    </select>
                    <input v-model="dateFrom" @change="applyFilters" type="date" placeholder="Dari tanggal"
                        class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500"/>
                    <input v-model="dateTo" @change="applyFilters" type="date" placeholder="Sampai tanggal"
                        class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500"/>
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
                                <th class="text-right px-4 py-3 text-xs font-semibold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider">Total Masuk</th>
                                <th class="text-right px-4 py-3 text-xs font-semibold text-rose-600 dark:text-rose-400 uppercase tracking-wider">Total Keluar</th>
                                <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Net Change</th>
                                <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Saldo Akhir</th>
                                <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Transaksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 dark:divide-gray-700/50">
                            <tr v-if="mutations.data.length === 0">
                                <td colspan="7" class="text-center py-16 text-gray-400 dark:text-gray-500">
                                    <svg class="w-12 h-12 mx-auto mb-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    <p class="font-medium">Belum ada data mutasi</p>
                                </td>
                            </tr>
                            <tr v-for="m in mutations.data" :key="`${m.product_id}-${m.warehouse_id}`" class="hover:bg-indigo-50/30 dark:hover:bg-indigo-900/10 transition-colors">
                                <td class="px-4 py-3">
                                    <p class="font-medium text-gray-800 dark:text-gray-200">{{ m.product?.name }}</p>
                                    <p class="text-xs text-gray-400 font-mono">{{ m.product?.sku }}</p>
                                </td>
                                <td class="px-4 py-3">
                                    <p class="text-gray-700 dark:text-gray-300">{{ m.warehouse?.name }}</p>
                                    <p class="text-xs text-gray-400">{{ m.warehouse?.code }}</p>
                                </td>
                                <td class="px-4 py-3 text-right font-semibold text-emerald-600 dark:text-emerald-400">+{{ formatNumber(m.total_in) }}</td>
                                <td class="px-4 py-3 text-right font-semibold text-rose-600 dark:text-rose-400">-{{ formatNumber(m.total_out) }}</td>
                                <td class="px-4 py-3 text-right">
                                    <span :class="['font-bold', netClass(m.net_change)]">{{ m.net_change >= 0 ? '+' : '' }}{{ formatNumber(m.net_change) }}</span>
                                </td>
                                <td class="px-4 py-3 text-right font-bold text-gray-800 dark:text-gray-200">{{ formatNumber(m.latest_balance) }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-indigo-100 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 text-xs font-bold">{{ m.transaction_count }}</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-if="mutations.last_page > 1" class="px-4 py-3 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ mutations.total }} produk-gudang</p>
                    <div class="flex gap-1">
                        <button v-for="link in mutations.links" :key="link.label" :disabled="!link.url"
                            @click="link.url && router.get(link.url)" v-html="link.label"
                            :class="['px-3 py-1 text-xs rounded-lg border transition-colors', link.active ? 'bg-indigo-600 border-indigo-600 text-white' : 'border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-40']"/>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
