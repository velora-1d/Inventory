<script setup lang="ts">
import { showConfirm } from '@/confirm';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed, reactive } from 'vue';

defineOptions({ layout: AuthenticatedLayout });


interface Warehouse { id: number; name: string; code: string; }
interface Unit      { id: number; name: string; symbol: string; }
interface Product   {
    id: number; name: string; sku: string;
    sale_price: number; avg_price: number;
    base_unit_id: number;
    baseUnit: { id: number; name: string; symbol: string } | null;
}
interface StockRecord { product_id: number; warehouse_id: number; qty: number; }
interface StockOutItem {
    id: number; product_id: number; unit_id: number;
    qty: number; price: number; subtotal: number;
    product: Product; unit: Unit;
}
interface StockOut {
    id: number; transaction_no: string; transaction_date: string;
    reference_no: string | null; recipient: string | null; notes: string | null;
    status: 'draft' | 'completed';
    items_count: number;
    warehouse: Warehouse;
    creator: { name: string };
}
interface Pagination<T> {
    data: T[]; current_page: number; last_page: number;
    per_page: number; total: number;
    links: { url: string | null; label: string; active: boolean }[];
}

const props = defineProps<{
    stockOuts: Pagination<StockOut>;
    warehouses: Warehouse[];
    products: Product[];
    units: Unit[];
    stocks: StockRecord[];
    filters: { search?: string; status?: string; warehouse_id?: string; date_from?: string; date_to?: string };
}>();

// ─── Filters ──────────────────────────────────────────────
const search          = ref(props.filters.search ?? '');
const statusFilter    = ref(props.filters.status ?? '');
const warehouseFilter = ref(props.filters.warehouse_id ?? '');
const dateFrom        = ref(props.filters.date_from ?? '');
const dateTo          = ref(props.filters.date_to ?? '');

const applyFilters = () => {
    router.get(route('stock-outs.index'), {
        search: search.value || undefined,
        status: statusFilter.value || undefined,
        warehouse_id: warehouseFilter.value || undefined,
        date_from: dateFrom.value || undefined,
        date_to: dateTo.value || undefined,
    }, { preserveState: true, replace: true });
};

// ─── Modal ────────────────────────────────────────────────
const showModal   = ref(false);
const showDetail  = ref(false);
const detailData  = ref<StockOut | null>(null);
const detailItems = ref<StockOutItem[]>([]);
const isLoadingDetail = ref(false);

// ─── Form ─────────────────────────────────────────────────
const isEditing = ref(false);
const editingId = ref<number | null>(null);

const form = reactive({
    transaction_date: new Date().toISOString().slice(0, 10),
    warehouse_id: '' as number | string,
    reference_no: '',
    recipient: '',
    notes: '',
    items: [] as { product_id: number | string; unit_id: number | string; qty: number; price: number; subtotal: number; availableStock: number }[],
});
const formErrors   = ref<Record<string, string>>({});
const isSubmitting = ref(false);

// Get available stock for a product in selected warehouse
const getAvailableStock = (productId: number | string): number => {
    if (!form.warehouse_id || !productId) return 0;
    const s = props.stocks.find(
        s => s.product_id === Number(productId) && s.warehouse_id === Number(form.warehouse_id)
    );
    return s?.qty ?? 0;
};

const addItem = () => {
    form.items.push({ product_id: '', unit_id: '', qty: 1, price: 0, subtotal: 0, availableStock: 0 });
};

const removeItem = (idx: number) => form.items.splice(idx, 1);

const onProductChange = (idx: number) => {
    const item = form.items[idx];
    const product = props.products.find(p => p.id === Number(item.product_id));
    if (product) {
        item.unit_id        = product.base_unit_id;
        item.price          = product.sale_price ?? product.avg_price ?? 0;
        item.availableStock = getAvailableStock(item.product_id);
        item.subtotal       = item.qty * item.price;
    }
};

const onWarehouseChange = () => {
    // Refresh available stock for all items
    form.items.forEach(item => {
        item.availableStock = getAvailableStock(item.product_id);
    });
};

const recalcSubtotal = (idx: number) => {
    form.items[idx].subtotal = form.items[idx].qty * form.items[idx].price;
};

const stockWarning = (idx: number): boolean => {
    const item = form.items[idx];
    return item.product_id !== '' && item.qty > item.availableStock;
};

const grandTotal = computed(() =>
    form.items.reduce((sum, i) => sum + (i.subtotal ?? 0), 0)
);

const hasStockWarning = computed(() =>
    form.items.some((_, idx) => stockWarning(idx))
);

const resetForm = () => {
    isEditing.value = false;
    editingId.value = null;
    form.transaction_date = new Date().toISOString().slice(0, 10);
    form.warehouse_id = '';
    form.reference_no = '';
    form.recipient = '';
    form.notes = '';
    form.items = [];
    formErrors.value = {};
};

const openModal = () => { resetForm(); addItem(); showModal.value = true; };

const editTransaction = async (so: any) => {
    resetForm();
    isEditing.value = true;
    editingId.value = so.id;

    // Fetch detail items
    const res = await fetch(`/stock-outs/${so.id}`, {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    });
    const data = await res.json();

    // Populate form
    form.transaction_date = so.transaction_date;
    form.warehouse_id = so.warehouse_id;
    form.reference_no = so.reference_no ?? '';
    form.recipient = so.recipient ?? '';
    form.notes = so.notes ?? '';
    form.items = (data.items ?? []).map((item: any) => {
        return {
            product_id: item.product_id,
            unit_id: item.unit_id,
            qty: Number(item.qty),
            price: Number(item.price),
            subtotal: Number(item.subtotal),
            availableStock: getAvailableStock(item.product_id)
        };
    });

    showModal.value = true;
};

const submitForm = () => {
    isSubmitting.value = true;
    const url = isEditing.value 
        ? route('stock-outs.update', editingId.value!) 
        : route('stock-outs.store');
    
    const method = isEditing.value ? 'put' : 'post';

    router[method](url, {
        transaction_date: form.transaction_date,
        warehouse_id: form.warehouse_id,
        reference_no: form.reference_no,
        recipient: form.recipient,
        notes: form.notes,
        items: form.items.map(i => ({
            product_id: i.product_id,
            unit_id: i.unit_id,
            qty: i.qty,
            price: i.price,
        })),
    }, {
        onSuccess: () => { showModal.value = false; resetForm(); },
        onError: (errors) => { formErrors.value = errors; },
        onFinish: () => { isSubmitting.value = false; },
    });
};

const viewDetail = async (stockOut: StockOut) => {
    isLoadingDetail.value = true;
    showDetail.value = true;
    detailData.value = stockOut;
    try {
        const res = await fetch(`/stock-outs/${stockOut.id}`, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await res.json();
        detailItems.value = data.items ?? [];
    } finally {
        isLoadingDetail.value = false;
    }
};

const confirmTransaction = async (id: number) => {
    if (!await showConfirm('Konfirmasi transaksi ini? Stok akan dikurangi dan tidak dapat diubah.')) return;
    router.post(route('stock-outs.confirm', id), {}, {
        onSuccess: () => { showDetail.value = false; }
    });
};

const unconfirmTransaction = async (id: number) => {
    if (!await showConfirm('Batalkan konfirmasi transaksi ini? Status akan kembali menjadi Draft dan jumlah stok gudang akan disesuaikan kembali.')) return;
    router.post(route('stock-outs.unconfirm', id), {}, {
        onSuccess: () => { showDetail.value = false; }
    });
};

const deleteTransaction = async (id: number) => {
    if (!await showConfirm('Hapus transaksi ini?')) return;
    router.delete(route('stock-outs.destroy', id));
};

const formatNumber = (n: number) => new Intl.NumberFormat('id-ID').format(n ?? 0);
const statusBadge = (status: string) =>
    status === 'completed'
        ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300'
        : 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300';
</script>

<template>
    <Head title="Barang Keluar" />
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Barang Keluar</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Kelola transaksi pengeluaran barang dari gudang</p>
                </div>
                <button @click="openModal"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-sm font-semibold rounded-xl shadow transition-all duration-200 hover:shadow-md active:scale-95">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Buat Transaksi
                </button>
            </div>

        <div class="py-6 px-4 sm:px-6 lg:px-8 space-y-5">

            <!-- Filters -->
            <div class="bg-surface-warm dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
                    <input v-model="search" @keyup.enter="applyFilters" type="text"
                        placeholder="Cari no. transaksi, penerima..."
                        class="col-span-1 lg:col-span-2 px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-rose-500"/>
                    <select v-model="statusFilter" @change="applyFilters"
                        class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-rose-500">
                        <option value="">Semua Status</option>
                        <option value="draft">Draft</option>
                        <option value="completed">Selesai</option>
                    </select>
                    <select v-model="warehouseFilter" @change="applyFilters"
                        class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-rose-500">
                        <option value="">Semua Gudang</option>
                        <option v-for="w in warehouses" :key="w.id" :value="w.id">{{ w.name }}</option>
                    </select>
                    <input v-model="dateFrom" @change="applyFilters" type="date"
                            class=" px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-rose-500"/>
                    <input v-model="dateTo" @change="applyFilters" type="date"
                            class=" px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-rose-500"/>
                </div>
            </div>

            <!-- Table -->
            <div class="bg-surface-warm dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-100 dark:border-gray-700">
                            <tr>
                                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">No. Transaksi</th>
                                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tanggal</th>
                                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Gudang Asal</th>
                                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Penerima</th>
                                <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Items</th>
                                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Ref.</th>
                                <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 dark:divide-gray-700/50">
                            <tr v-if="stockOuts.data.length === 0">
                                <td colspan="8" class="text-center py-16 text-gray-400 dark:text-gray-500">
                                    <svg class="w-12 h-12 mx-auto mb-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                    </svg>
                                    <p class="font-medium">Belum ada transaksi barang keluar</p>
                                    <p class="text-xs mt-1">Klik "Buat Transaksi" untuk memulai</p>
                                </td>
                            </tr>
                            <tr v-for="so in stockOuts.data" :key="so.id"
                                class="hover:bg-rose-50/40 dark:hover:bg-rose-900/10 transition-colors duration-100">
                                <td class="px-4 py-3">
                                    <span class="font-mono font-semibold text-rose-600 dark:text-rose-400 text-xs">{{ so.transaction_no }}</span>
                                </td>
                                <td class="px-4 py-3 text-gray-600 dark:text-gray-300">
                                    {{ new Date(so.transaction_date).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-800 dark:text-gray-200">{{ so.warehouse.name }}</div>
                                    <div class="text-xs text-gray-400">{{ so.warehouse.code }}</div>
                                </td>
                                <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ so.recipient ?? '-' }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-rose-100 dark:bg-rose-900/40 text-rose-700 dark:text-rose-300 text-xs font-bold">
                                        {{ so.items_count }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-gray-500 dark:text-gray-400 text-xs font-mono">{{ so.reference_no ?? '-' }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span :class="statusBadge(so.status)" class="px-2.5 py-1 rounded-full text-xs font-semibold">
                                        {{ so.status === 'completed' ? 'Selesai' : 'Draft' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-center gap-1">
                                        <button @click="viewDetail(so)" title="Lihat Detail"
                                            class="p-1.5 rounded-lg text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-rose-600 dark:hover:text-rose-400 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </button>
                                        <!-- Edit (draft only) -->
                                        <button v-if="so.status === 'draft'" @click="editTransaction(so)" title="Edit Draft"
                                            class="p-1.5 rounded-lg text-gray-500 hover:bg-blue-100 dark:hover:bg-blue-900/30 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                        </button>
                                        <button v-if="so.status === 'draft'" @click="confirmTransaction(so.id)" title="Konfirmasi"
                                            class="p-1.5 rounded-lg text-gray-500 hover:bg-emerald-100 dark:hover:bg-emerald-900/30 hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        </button>
                                        <button v-if="so.status === 'draft'" @click="deleteTransaction(so.id)" title="Hapus"
                                            class="p-1.5 rounded-lg text-gray-500 hover:bg-red-100 dark:hover:bg-red-900/30 hover:text-red-600 dark:hover:text-red-400 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <!-- Pagination -->
                <div v-if="stockOuts.last_page > 1" class="px-4 py-3 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <p class="text-xs text-gray-500 dark:text-gray-400">Menampilkan {{ stockOuts.data.length }} dari {{ stockOuts.total }} transaksi</p>
                    <div class="flex gap-1">
                        <button v-for="link in stockOuts.links" :key="link.label" :disabled="!link.url"
                            @click="link.url && router.get(link.url)" v-html="link.label"
                            :class="['px-3 py-1 text-xs rounded-lg border transition-colors', link.active ? 'bg-rose-600 border-rose-600 text-white' : 'border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-40 disabled:cursor-not-allowed']"/>
                    </div>
                </div>
            </div>
        </div>

        <!-- ═══ MODAL: BUAT TRANSAKSI ═══ -->
        <Transition enter-from-class="opacity-0" enter-active-class="transition duration-200" leave-to-class="opacity-0" leave-active-class="transition duration-150">
        <div v-if="showModal" class="fixed inset-0 z-50 flex items-start justify-center bg-black/50 backdrop-blur-sm p-4 overflow-y-auto">
            <div class="bg-surface-warm dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-4xl my-6">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ isEditing ? 'Edit Transaksi Barang Keluar' : 'Buat Transaksi Barang Keluar' }}</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ isEditing ? 'Perbarui data transaksi draft' : 'Stok dikurangi saat transaksi dikonfirmasi' }}</p>
                    </div>
                    <button @click="showModal = false" class="p-2 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-400 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="p-6 space-y-5">
                    <!-- Stock warning banner -->
                    <div v-if="hasStockWarning" class="flex items-center gap-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl px-4 py-3">
                        <svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.962-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                        <p class="text-sm text-red-700 dark:text-red-400 font-medium">Beberapa item melebihi stok tersedia. Periksa qty sebelum menyimpan.</p>
                    </div>

                    <!-- Header Fields -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1.5">Tanggal <span class="text-red-500">*</span></label>
                            <input v-model="form.transaction_date" type="date"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-rose-500"/>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1.5">Gudang Asal <span class="text-red-500">*</span></label>
                            <select v-model="form.warehouse_id" @change="onWarehouseChange"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-rose-500">
                                <option value="">-- Pilih Gudang --</option>
                                <option v-for="w in warehouses" :key="w.id" :value="w.id">{{ w.name }}</option>
                            </select>
                            <p v-if="formErrors.warehouse_id" class="text-red-500 text-xs mt-1">{{ formErrors.warehouse_id }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1.5">Penerima / Tujuan</label>
                            <input v-model="form.recipient" type="text" placeholder="Nama penerima / departemen"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-rose-500"/>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1.5">No. Referensi</label>
                            <input v-model="form.reference_no" type="text" placeholder="Opsional"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-rose-500"/>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1.5">Catatan</label>
                            <input v-model="form.notes" type="text" placeholder="Catatan tambahan (opsional)"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-rose-500"/>
                        </div>
                    </div>

                    <!-- Items -->
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-sm font-bold text-gray-800 dark:text-gray-200">Detail Barang</h4>
                            <button @click="addItem"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold bg-rose-50 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400 rounded-lg hover:bg-rose-100 dark:hover:bg-rose-900/50 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                Tambah Baris
                            </button>
                        </div>
                        <p v-if="formErrors.items" class="text-red-500 text-xs mb-2">{{ formErrors.items }}</p>

                        <div class="border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden">
                            <table class="w-full text-sm">
                                <thead class="bg-gray-50 dark:bg-gray-700/50">
                                    <tr>
                                        <th class="text-left px-3 py-2 text-xs font-semibold text-gray-500 dark:text-gray-400">Barang</th>
                                        <th class="text-left px-3 py-2 text-xs font-semibold text-gray-500 dark:text-gray-400 w-24">Satuan</th>
                                        <th class="text-right px-3 py-2 text-xs font-semibold text-gray-500 dark:text-gray-400 w-28">Qty / Stok</th>
                                        <th class="text-right px-3 py-2 text-xs font-semibold text-gray-500 dark:text-gray-400 w-32">Harga</th>
                                        <th class="text-right px-3 py-2 text-xs font-semibold text-gray-500 dark:text-gray-400 w-32">Subtotal</th>
                                        <th class="w-10"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                    <tr v-if="form.items.length === 0">
                                        <td colspan="6" class="text-center py-8 text-gray-400 dark:text-gray-500 text-xs">
                                            Belum ada item. Klik "Tambah Baris" untuk menambahkan barang.
                                        </td>
                                    </tr>
                                    <tr v-for="(item, idx) in form.items" :key="idx"
                                        :class="['hover:bg-gray-50/50 dark:hover:bg-gray-700/30', stockWarning(idx) ? 'bg-red-50/50 dark:bg-red-900/10' : '']">
                                        <td class="px-2 py-2">
                                            <select v-model="item.product_id" @change="onProductChange(idx)"
                                                class="w-full px-2 py-1.5 text-xs rounded-lg border border-gray-200 dark:border-gray-600 bg-surface-warm dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-rose-500">
                                                <option value="">-- Pilih Barang --</option>
                                                <option v-for="p in products" :key="p.id" :value="p.id">{{ p.sku }} — {{ p.name }}</option>
                                            </select>
                                        </td>
                                        <td class="px-2 py-2">
                                            <select v-model="item.unit_id"
                                                class="w-full px-2 py-1.5 text-xs rounded-lg border border-gray-200 dark:border-gray-600 bg-surface-warm dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-rose-500">
                                                <option value="">Satuan</option>
                                                <option v-for="u in units" :key="u.id" :value="u.id">{{ u.symbol }}</option>
                                            </select>
                                        </td>
                                        <td class="px-2 py-2">
                                            <input v-model.number="item.qty" @input="recalcSubtotal(idx)" type="number" min="0.01" step="0.01"
                                                :class="['w-full px-2 py-1.5 text-xs text-right rounded-lg border focus:outline-none focus:ring-2', stockWarning(idx) ? 'border-red-400 focus:ring-red-400 bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-300' : 'border-gray-200 dark:border-gray-600 bg-surface-warm dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:ring-rose-500']"/>
                                            <div v-if="item.product_id" class="text-right mt-0.5">
                                                <span :class="['text-xs', stockWarning(idx) ? 'text-red-500 font-semibold' : 'text-gray-400']">
                                                    Stok: {{ item.availableStock }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="px-2 py-2">
                                            <input v-model.number="item.price" @input="recalcSubtotal(idx)" type="number" min="0"
                                                class="w-full px-2 py-1.5 text-xs text-right rounded-lg border border-gray-200 dark:border-gray-600 bg-surface-warm dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-rose-500"/>
                                        </td>
                                        <td class="px-3 py-2 text-right text-xs font-semibold text-gray-700 dark:text-gray-300">
                                            {{ formatNumber(item.subtotal) }}
                                        </td>
                                        <td class="px-2 py-2 text-center">
                                            <button @click="removeItem(idx)" class="p-1 rounded text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot v-if="form.items.length > 0" class="bg-gray-50 dark:bg-gray-700/50 border-t border-gray-200 dark:border-gray-700">
                                    <tr>
                                        <td colspan="4" class="px-3 py-2 text-xs font-bold text-gray-600 dark:text-gray-400 text-right">Total</td>
                                        <td class="px-3 py-2 text-sm font-bold text-rose-600 dark:text-rose-400 text-right">Rp {{ formatNumber(grandTotal) }}</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-100 dark:border-gray-700">
                    <button @click="showModal = false"
                        class="px-4 py-2 text-sm font-semibold text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-xl transition-colors">
                        Batal
                    </button>
                    <button @click="submitForm" :disabled="isSubmitting"
                        class="px-5 py-2 text-sm font-semibold bg-rose-600 hover:bg-rose-700 text-white rounded-xl shadow transition-all duration-200 disabled:opacity-60 disabled:cursor-not-allowed active:scale-95">
                        <span v-if="isSubmitting" class="inline-flex items-center gap-2">
                            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
                            Menyimpan...
                        </span>
                        <span v-else>{{ isEditing ? 'Simpan Perubahan' : 'Simpan Draft' }}</span>
                    </button>
                </div>
            </div>
        </div>
        </Transition>

        <!-- ═══ MODAL: DETAIL ═══ -->
        <Transition enter-from-class="opacity-0" enter-active-class="transition duration-200" leave-to-class="opacity-0" leave-active-class="transition duration-150">
        <div v-if="showDetail && detailData" class="fixed inset-0 z-50 flex items-start justify-center bg-black/50 backdrop-blur-sm p-4 overflow-y-auto">
            <div class="bg-surface-warm dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-3xl my-6">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                    <div class="flex items-center gap-3">
                        <span class="font-mono font-bold text-rose-600 dark:text-rose-400">{{ detailData.transaction_no }}</span>
                        <span :class="statusBadge(detailData.status)" class="px-2.5 py-1 rounded-full text-xs font-semibold">
                            {{ detailData.status === 'completed' ? 'Selesai' : 'Draft' }}
                        </span>
                    </div>
                    <button @click="showDetail = false" class="p-2 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-400 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="p-6 space-y-5">
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-3">
                            <p class="text-xs text-gray-400 mb-1">Tanggal</p>
                            <p class="text-sm font-semibold text-gray-800 dark:text-gray-200">
                                {{ new Date(detailData.transaction_date).toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' }) }}
                            </p>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-3">
                            <p class="text-xs text-gray-400 mb-1">Gudang Asal</p>
                            <p class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ detailData.warehouse.name }}</p>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-3">
                            <p class="text-xs text-gray-400 mb-1">Penerima</p>
                            <p class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ detailData.recipient ?? '-' }}</p>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-3">
                            <p class="text-xs text-gray-400 mb-1">No. Referensi</p>
                            <p class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ detailData.reference_no ?? '-' }}</p>
                        </div>
                    </div>

                    <div class="border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden">
                        <div v-if="isLoadingDetail" class="text-center py-10 text-gray-400">
                            <svg class="w-6 h-6 animate-spin mx-auto" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
                        </div>
                        <table v-else class="w-full text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-700/50">
                                <tr>
                                    <th class="text-left px-4 py-2 text-xs font-semibold text-gray-500 dark:text-gray-400">Barang</th>
                                    <th class="text-center px-4 py-2 text-xs font-semibold text-gray-500 dark:text-gray-400">Qty</th>
                                    <th class="text-center px-4 py-2 text-xs font-semibold text-gray-500 dark:text-gray-400">Satuan</th>
                                    <th class="text-right px-4 py-2 text-xs font-semibold text-gray-500 dark:text-gray-400">Harga</th>
                                    <th class="text-right px-4 py-2 text-xs font-semibold text-gray-500 dark:text-gray-400">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                <tr v-for="item in detailItems" :key="item.id" class="hover:bg-gray-50/50 dark:hover:bg-gray-700/30">
                                    <td class="px-4 py-3">
                                        <p class="font-medium text-gray-800 dark:text-gray-200">{{ item.product?.name }}</p>
                                        <p class="text-xs text-gray-400 font-mono">{{ item.product?.sku }}</p>
                                    </td>
                                    <td class="px-4 py-3 text-center font-semibold text-gray-700 dark:text-gray-300">{{ formatNumber(item.qty) }}</td>
                                    <td class="px-4 py-3 text-center text-gray-500 dark:text-gray-400">{{ item.unit?.symbol }}</td>
                                    <td class="px-4 py-3 text-right text-gray-600 dark:text-gray-300">{{ formatNumber(item.price) }}</td>
                                    <td class="px-4 py-3 text-right font-semibold text-gray-800 dark:text-gray-200">{{ formatNumber(item.subtotal) }}</td>
                                </tr>
                            </tbody>
                            <tfoot class="bg-gray-50 dark:bg-gray-700/50 border-t border-gray-200 dark:border-gray-700">
                                <tr>
                                    <td colspan="4" class="px-4 py-2 text-xs font-bold text-gray-600 dark:text-gray-400 text-right">Total</td>
                                    <td class="px-4 py-2 text-sm font-bold text-rose-600 dark:text-rose-400 text-right">
                                        Rp {{ formatNumber(detailItems.reduce((s, i) => s + i.subtotal, 0)) }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <div v-if="detailData.notes" class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl p-3">
                        <p class="text-xs font-semibold text-amber-700 dark:text-amber-400 mb-1">Catatan</p>
                        <p class="text-sm text-amber-800 dark:text-amber-300">{{ detailData.notes }}</p>
                    </div>
                </div>
                <div class="flex items-center justify-between px-6 py-4 border-t border-gray-100 dark:border-gray-700">
                    <p class="text-xs text-gray-400 dark:text-gray-500">Dibuat oleh: <span class="font-semibold text-gray-600 dark:text-gray-300">{{ detailData.creator?.name }}</span></p>
                    <div class="flex gap-2">
                        <button v-if="detailData.status === 'draft'" @click="deleteTransaction(detailData.id)"
                            class="px-3 py-1.5 text-xs font-semibold text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/20 hover:bg-red-100 rounded-lg transition-colors">Hapus</button>
                        <button v-if="detailData.status === 'draft'" @click="confirmTransaction(detailData.id)"
                            class="px-4 py-1.5 text-xs font-semibold bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg shadow transition-all active:scale-95">
                            ✓ Konfirmasi & Kurangi Stok
                        </button>
                        <div v-else class="flex gap-2">
                            <button @click="unconfirmTransaction(detailData.id)"
                                class="px-4 py-1.5 text-xs font-semibold text-amber-700 bg-amber-50 dark:bg-amber-950/30 hover:bg-amber-100 dark:hover:bg-amber-900/50 rounded-lg transition-colors">
                                ↺ Ubah ke Draft (Rollback)
                            </button>
                            <button @click="showDetail = false"
                                class="px-4 py-1.5 text-xs font-semibold bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors">
                                Tutup
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </Transition>
</template>
