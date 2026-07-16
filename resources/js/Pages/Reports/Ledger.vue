<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

interface Product   { id: number; name: string; sku: string; }
interface Warehouse { id: number; name: string; code: string; }
interface Unit      { id: number; name: string; symbol: string; }
interface Ledger {
    id: number; transaction_date: string; transaction_type: string;
    reference_type: string; reference_id: number;
    qty_in: number; qty_out: number; balance: number; price: number; notes: string | null;
    product: Product; warehouse: Warehouse; unit: Unit | null; creator: { name: string } | null;
}
interface Pagination<T> { data: T[]; current_page: number; last_page: number; per_page: number; total: number; links: { url: string | null; label: string; active: boolean }[]; }

const props = defineProps<{
    ledgers: Pagination<Ledger>;
    products: Product[];
    warehouses: Warehouse[];
    filters: { product_id?: string; warehouse_id?: string; type?: string; date_from?: string; date_to?: string };
}>();

const productFilter   = ref(props.filters.product_id ?? '');
const warehouseFilter = ref(props.filters.warehouse_id ?? '');
const typeFilter      = ref(props.filters.type ?? '');
const dateFrom        = ref(props.filters.date_from ?? '');
const dateTo          = ref(props.filters.date_to ?? '');

const applyFilters = () => {
    router.get(route('reports.ledger'), {
        product_id:   productFilter.value || undefined,
        warehouse_id: warehouseFilter.value || undefined,
        type:         typeFilter.value || undefined,
        date_from:    dateFrom.value || undefined,
        date_to:      dateTo.value || undefined,
    }, { preserveState: true, replace: true });
};

const resetFilters = () => {
    productFilter.value = ''; warehouseFilter.value = ''; typeFilter.value = '';
    dateFrom.value = ''; dateTo.value = '';
    router.get(route('reports.ledger'), {}, { preserveState: true, replace: true });
};

const typeConfig: Record<string, { label: string; color: string }> = {
    in:           { label: 'Barang Masuk',   color: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300' },
    out:          { label: 'Barang Keluar',  color: 'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300' },
    transfer_in:  { label: 'Transfer Masuk', color: 'bg-violet-100 text-violet-700 dark:bg-violet-900/40 dark:text-violet-300' },
    transfer_out: { label: 'Transfer Keluar',color: 'bg-violet-100 text-violet-700 dark:bg-violet-900/40 dark:text-violet-300' },
    adjustment:   { label: 'Penyesuaian',    color: 'bg-cyan-100 text-cyan-700 dark:bg-cyan-900/40 dark:text-cyan-300' },
    return_in:    { label: 'Retur Masuk',    color: 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300' },
    return_out:   { label: 'Retur Keluar',   color: 'bg-orange-100 text-orange-700 dark:bg-orange-900/40 dark:text-orange-300' },
};
const getType = (t: string) => typeConfig[t] ?? { label: t, color: 'bg-gray-100 text-gray-600' };
const formatNumber = (n: number) => new Intl.NumberFormat('id-ID').format(n ?? 0);
const formatDate   = (d: string) => new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
</script>

<template>
    <Head title="Buku Besar Stok" />
    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">Buku Besar Stok</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Riwayat seluruh mutasi stok secara kronologis</p>
            </div>
        </template>

        <div class="py-6 px-4 sm:px-6 lg:px-8 space-y-5">
            <!-- Filters -->
            <div class="bg-surface-warm dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
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
                    <select v-model="typeFilter" @change="applyFilters"
                        class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Semua Tipe</option>
                        <option value="in">Barang Masuk</option>
                        <option value="out">Barang Keluar</option>
                        <option value="transfer_in">Transfer Masuk</option>
                        <option value="transfer_out">Transfer Keluar</option>
                        <option value="adjustment">Penyesuaian</option>
                        <option value="return_in">Retur Masuk</option>
                        <option value="return_out">Retur Keluar</option>
                    </select>
                    <input v-model="dateFrom" @change="applyFilters" type="date" class=" px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500"/>
                    <input v-model="dateTo"   @change="applyFilters" type="date" class=" px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500"/>
                    <button @click="resetFilters" class="px-3 py-2 text-sm text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-xl transition-colors">Reset Filter</button>
                </div>
            </div>

            <!-- Stats bar -->
            <div class="flex items-center gap-4 text-sm text-gray-600 dark:text-gray-400 bg-surface-warm dark:bg-gray-800 rounded-xl px-4 py-2.5 border border-gray-100 dark:border-gray-700">
                <span>Total records: <strong class="text-gray-800 dark:text-gray-200">{{ ledgers.total }}</strong></span>
                <span class="text-gray-300 dark:text-gray-600">|</span>
                <span>Halaman {{ ledgers.current_page }} dari {{ ledgers.last_page }}</span>
            </div>

            <!-- Table -->
            <div class="bg-surface-warm dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-100 dark:border-gray-700">
                            <tr>
                                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tanggal</th>
                                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Produk</th>
                                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Gudang</th>
                                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tipe</th>
                                <th class="text-right px-4 py-3 text-xs font-semibold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider">Masuk</th>
                                <th class="text-right px-4 py-3 text-xs font-semibold text-rose-600 dark:text-rose-400 uppercase tracking-wider">Keluar</th>
                                <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Saldo</th>
                                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 dark:divide-gray-700/50">
                            <tr v-if="ledgers.data.length === 0">
                                <td colspan="8" class="text-center py-16 text-gray-400 dark:text-gray-500">
                                    <svg class="w-12 h-12 mx-auto mb-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    <p class="font-medium">Belum ada data ledger</p>
                                    <p class="text-xs mt-1">Buat transaksi terlebih dahulu</p>
                                </td>
                            </tr>
                            <tr v-for="l in ledgers.data" :key="l.id" class="hover:bg-indigo-50/30 dark:hover:bg-indigo-900/10 transition-colors duration-100">
                                <td class="px-4 py-3 text-gray-600 dark:text-gray-300 whitespace-nowrap">{{ formatDate(l.transaction_date) }}</td>
                                <td class="px-4 py-3">
                                    <p class="font-medium text-gray-800 dark:text-gray-200">{{ l.product?.name }}</p>
                                    <p class="text-xs text-gray-400 font-mono">{{ l.product?.sku }}</p>
                                </td>
                                <td class="px-4 py-3">
                                    <p class="text-gray-700 dark:text-gray-300">{{ l.warehouse?.name }}</p>
                                    <p class="text-xs text-gray-400">{{ l.warehouse?.code }}</p>
                                </td>
                                <td class="px-4 py-3">
                                    <span :class="getType(l.transaction_type).color" class="px-2 py-0.5 rounded-full text-xs font-semibold whitespace-nowrap">
                                        {{ getType(l.transaction_type).label }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <span v-if="l.qty_in > 0" class="text-emerald-600 dark:text-emerald-400 font-semibold">+{{ formatNumber(l.qty_in) }}</span>
                                    <span v-else class="text-gray-300 dark:text-gray-600">—</span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <span v-if="l.qty_out > 0" class="text-rose-600 dark:text-rose-400 font-semibold">-{{ formatNumber(l.qty_out) }}</span>
                                    <span v-else class="text-gray-300 dark:text-gray-600">—</span>
                                </td>
                                <td class="px-4 py-3 text-right font-bold text-gray-800 dark:text-gray-200">{{ formatNumber(l.balance) }}</td>
                                <td class="px-4 py-3 text-xs text-gray-500 dark:text-gray-400 max-w-xs truncate">{{ l.notes ?? '-' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <!-- Pagination -->
                <div v-if="ledgers.last_page > 1" class="px-4 py-3 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <p class="text-xs text-gray-500 dark:text-gray-400">Menampilkan {{ ledgers.data.length }} dari {{ ledgers.total }} records</p>
                    <div class="flex gap-1 flex-wrap">
                        <button v-for="link in ledgers.links" :key="link.label" :disabled="!link.url"
                            @click="link.url && router.get(link.url)" v-html="link.label"
                            :class="['px-3 py-1 text-xs rounded-lg border transition-colors', link.active ? 'bg-indigo-600 border-indigo-600 text-white' : 'border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-40 disabled:cursor-not-allowed']"/>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
