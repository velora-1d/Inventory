<script setup lang="ts">
import { showConfirm } from '@/confirm';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed, reactive } from 'vue';

interface Warehouse { id: number; name: string; code: string; }
interface Supplier  { id: number; name: string; code: string; }
interface Unit      { id: number; name: string; symbol: string; }
interface Product   { id: number; name: string; sku: string; base_unit_id: number; avg_price: number; purchase_price: number; baseUnit: { name: string; symbol: string } | null; }
interface StockRecord { product_id: number; warehouse_id: number; qty: number; }
interface ReturnItemDetail { id: number; product_id: number; unit_id: number; qty: number; price: number; subtotal: number; condition: string; notes: string | null; product: Product; unit: Unit; }
interface StockReturn {
    id: number; transaction_no: string; return_date: string;
    return_type: 'return_in' | 'return_out';
    reason: string | null; status: 'draft' | 'completed'; items_count: number;
    warehouse: Warehouse; creator: { name: string };
}
interface Pagination<T> { data: T[]; current_page: number; last_page: number; per_page: number; total: number; links: { url: string | null; label: string; active: boolean }[]; }

const props = defineProps<{
    returns: Pagination<StockReturn>;
    warehouses: Warehouse[];
    suppliers: Supplier[];
    products: Product[];
    units: Unit[];
    stocks: StockRecord[];
    filters: { search?: string; status?: string; return_type?: string; warehouse_id?: string; date_from?: string; date_to?: string };
}>();

// ─── Filters ──────────────────────────────────────────────
const search          = ref(props.filters.search ?? '');
const statusFilter    = ref(props.filters.status ?? '');
const returnTypeFilter = ref(props.filters.return_type ?? '');
const warehouseFilter = ref(props.filters.warehouse_id ?? '');
const dateFrom        = ref(props.filters.date_from ?? '');
const dateTo          = ref(props.filters.date_to ?? '');

const applyFilters = () => {
    router.get(route('stock-returns.index'), {
        search: search.value || undefined,
        status: statusFilter.value || undefined,
        return_type: returnTypeFilter.value || undefined,
        warehouse_id: warehouseFilter.value || undefined,
        date_from: dateFrom.value || undefined,
        date_to: dateTo.value || undefined,
    }, { preserveState: true, replace: true });
};

// ─── Modal ────────────────────────────────────────────────
const showModal       = ref(false);
const showDetail      = ref(false);
const detailData      = ref<StockReturn | null>(null);
const detailItems     = ref<ReturnItemDetail[]>([]);
const isLoadingDetail = ref(false);

// ─── Form ─────────────────────────────────────────────────
const isEditing = ref(false);
const editingId = ref<number | null>(null);

const form = reactive({
    return_date:  new Date().toISOString().slice(0, 10),
    return_type:  'return_in' as 'return_in' | 'return_out',
    warehouse_id: '' as number | string,
    reason: '',
    items: [] as { product_id: number | string; unit_id: number | string; qty: number; price: number; subtotal: number; condition: string; notes: string; availableStock: number }[],
});
const formErrors   = ref<Record<string, string>>({});
const isSubmitting = ref(false);

const getAvailableStock = (productId: number | string): number => {
    if (!form.warehouse_id || !productId) return 0;
    const s = props.stocks.find(s => s.product_id === Number(productId) && s.warehouse_id === Number(form.warehouse_id));
    return s?.qty ?? 0;
};

const addItem = () => form.items.push({ product_id: '', unit_id: '', qty: 1, price: 0, subtotal: 0, condition: 'good', notes: '', availableStock: 0 });
const removeItem = (idx: number) => form.items.splice(idx, 1);

const onProductChange = (idx: number) => {
    const item = form.items[idx];
    const product = props.products.find(p => p.id === Number(item.product_id));
    if (product) {
        item.unit_id = product.base_unit_id;
        item.price   = form.return_type === 'return_in' ? product.avg_price ?? 0 : product.purchase_price ?? 0;
        item.availableStock = getAvailableStock(item.product_id);
        item.subtotal = item.qty * item.price;
    }
};

const onWarehouseChange = () => {
    form.items.forEach(item => { item.availableStock = getAvailableStock(item.product_id); });
};

const recalcSubtotal = (idx: number) => {
    form.items[idx].subtotal = form.items[idx].qty * form.items[idx].price;
};

const stockWarning = (idx: number) => form.return_type === 'return_out' && form.items[idx].product_id !== '' && form.items[idx].qty > form.items[idx].availableStock;
const hasStockWarning = computed(() => form.items.some((_, idx) => stockWarning(idx)));
const grandTotal = computed(() => form.items.reduce((sum, i) => sum + (i.subtotal ?? 0), 0));

const resetForm = () => {
    isEditing.value = false;
    editingId.value = null;
    form.return_date = new Date().toISOString().slice(0, 10);
    form.return_type = 'return_in';
    form.warehouse_id = '';
    form.reason = '';
    form.items = [];
    formErrors.value = {};
};

const openModal = () => { resetForm(); addItem(); showModal.value = true; };

const editReturn = async (ret: any) => {
    resetForm();
    isEditing.value = true;
    editingId.value = ret.id;

    // Fetch detail items
    const res = await fetch(`/stock-returns/${ret.id}`, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
    const data = await res.json();

    // Populate form
    form.return_date = ret.return_date;
    form.return_type = ret.return_type;
    form.warehouse_id = ret.warehouse_id;
    form.reason = ret.reason ?? '';
    form.items = (data.items ?? []).map((item: any) => {
        return {
            product_id: item.product_id,
            unit_id: item.unit_id,
            qty: Number(item.qty),
            price: Number(item.price),
            subtotal: Number(item.subtotal),
            condition: item.condition,
            notes: item.notes ?? '',
            availableStock: getAvailableStock(item.product_id)
        };
    });

    showModal.value = true;
};

const submitForm = () => {
    isSubmitting.value = true;
    const url = isEditing.value 
        ? route('stock-returns.update', editingId.value!) 
        : route('stock-returns.store');
    
    const method = isEditing.value ? 'put' : 'post';

    router[method](url, {
        return_date:  form.return_date,
        return_type:  form.return_type,
        warehouse_id: form.warehouse_id,
        reason: form.reason,
        items: form.items.map(i => ({
            product_id: i.product_id, unit_id: i.unit_id,
            qty: i.qty, price: i.price, condition: i.condition, notes: i.notes,
        })),
    }, {
        onSuccess: () => { showModal.value = false; resetForm(); },
        onError: (e) => { formErrors.value = e; },
        onFinish: () => { isSubmitting.value = false; },
    });
};

const viewDetail = async (ret: StockReturn) => {
    isLoadingDetail.value = true; showDetail.value = true; detailData.value = ret;
    try {
        const res = await fetch(`/stock-returns/${ret.id}`, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
        const data = await res.json();
        detailItems.value = data.items ?? [];
    } finally { isLoadingDetail.value = false; }
};

const confirmReturn = async (id: number) => {
    if (!await showConfirm('Konfirmasi retur ini? Stok akan diperbarui.')) return;
    router.post(route('stock-returns.confirm', id), {}, { onSuccess: () => { showDetail.value = false; } });
};

const unconfirmReturn = async (id: number) => {
    if (!await showConfirm('Batalkan konfirmasi retur ini? Status akan kembali menjadi Draft dan jumlah stok gudang akan disesuaikan kembali.')) return;
    router.post(route('stock-returns.unconfirm', id), {}, { onSuccess: () => { showDetail.value = false; } });
};

const deleteReturn = async (id: number) => {
    if (!await showConfirm('Hapus retur ini?')) return;
    router.delete(route('stock-returns.destroy', id));
};

const formatNumber = (n: number) => new Intl.NumberFormat('id-ID').format(n ?? 0);
const statusBadge = (s: string) => s === 'completed'
    ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300'
    : 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300';
const typeBadge = (t: string) => t === 'return_in'
    ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300'
    : 'bg-orange-100 text-orange-700 dark:bg-orange-900/40 dark:text-orange-300';
const typeLabel = (t: string) => t === 'return_in' ? 'Retur Masuk' : 'Retur Keluar';
</script>

<template>
    <Head title="Retur Barang" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Retur Barang</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Kelola retur barang masuk (dari customer) dan retur keluar (ke supplier)</p>
                </div>
                <button @click="openModal"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white text-sm font-semibold rounded-xl shadow transition-all duration-200 hover:shadow-md active:scale-95">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                    Buat Retur
                </button>
            </div>
        </template>

        <div class="py-6 px-4 sm:px-6 lg:px-8 space-y-5">
            <!-- Filters -->
            <div class="bg-surface-warm dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
                    <input v-model="search" @keyup.enter="applyFilters" type="text" placeholder="Cari no. retur..."
                        class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-orange-500"/>
                    <select v-model="returnTypeFilter" @change="applyFilters"
                        class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-orange-500">
                        <option value="">Semua Tipe</option>
                        <option value="return_in">Retur Masuk</option>
                        <option value="return_out">Retur Keluar</option>
                    </select>
                    <select v-model="statusFilter" @change="applyFilters"
                        class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-orange-500">
                        <option value="">Semua Status</option>
                        <option value="draft">Draft</option>
                        <option value="completed">Selesai</option>
                    </select>
                    <select v-model="warehouseFilter" @change="applyFilters"
                        class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-orange-500">
                        <option value="">Semua Gudang</option>
                        <option v-for="w in warehouses" :key="w.id" :value="w.id">{{ w.name }}</option>
                    </select>
                    <input v-model="dateFrom" @change="applyFilters" type="date" class=" px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-orange-500"/>
                    <input v-model="dateTo"   @change="applyFilters" type="date" class=" px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-orange-500"/>
                </div>
            </div>

            <!-- Table -->
            <div class="bg-surface-warm dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-100 dark:border-gray-700">
                            <tr>
                                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">No. Retur</th>
                                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tanggal</th>
                                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tipe</th>
                                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Gudang</th>
                                <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Items</th>
                                <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 dark:divide-gray-700/50">
                            <tr v-if="returns.data.length === 0">
                                <td colspan="7" class="text-center py-16 text-gray-400 dark:text-gray-500">
                                    <svg class="w-12 h-12 mx-auto mb-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                                    <p class="font-medium">Belum ada data retur barang</p>
                                    <p class="text-xs mt-1">Klik "Buat Retur" untuk memulai</p>
                                </td>
                            </tr>
                            <tr v-for="ret in returns.data" :key="ret.id" class="hover:bg-orange-50/40 dark:hover:bg-orange-900/10 transition-colors duration-100">
                                <td class="px-4 py-3"><span class="font-mono font-semibold text-orange-600 dark:text-orange-400 text-xs">{{ ret.transaction_no }}</span></td>
                                <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ new Date(ret.return_date).toLocaleDateString('id-ID', { day:'2-digit', month:'short', year:'numeric' }) }}</td>
                                <td class="px-4 py-3">
                                    <span :class="typeBadge(ret.return_type)" class="px-2.5 py-1 rounded-full text-xs font-semibold">{{ typeLabel(ret.return_type) }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <p class="font-medium text-gray-800 dark:text-gray-200">{{ ret.warehouse.name }}</p>
                                    <p class="text-xs text-gray-400">{{ ret.warehouse.code }}</p>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-orange-100 dark:bg-orange-900/40 text-orange-700 dark:text-orange-300 text-xs font-bold">{{ ret.items_count }}</span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span :class="statusBadge(ret.status)" class="px-2.5 py-1 rounded-full text-xs font-semibold">{{ ret.status === 'completed' ? 'Selesai' : 'Draft' }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-center gap-1">
                                        <button @click="viewDetail(ret)" class="p-1.5 rounded-lg text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-orange-600 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </button>
                                        <!-- Edit (draft only) -->
                                        <button v-if="ret.status === 'draft'" @click="editReturn(ret)" title="Edit Draft"
                                            class="p-1.5 rounded-lg text-gray-500 hover:bg-blue-100 dark:hover:bg-blue-900/30 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                        </button>
                                        <button v-if="ret.status === 'draft'" @click="confirmReturn(ret.id)" class="p-1.5 rounded-lg text-gray-500 hover:bg-emerald-100 dark:hover:bg-emerald-900/30 hover:text-emerald-600 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        </button>
                                        <button v-if="ret.status === 'draft'" @click="deleteReturn(ret.id)" class="p-1.5 rounded-lg text-gray-500 hover:bg-red-100 dark:hover:bg-red-900/30 hover:text-red-600 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-if="returns.last_page > 1" class="px-4 py-3 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <p class="text-xs text-gray-500 dark:text-gray-400">Total {{ returns.total }} retur</p>
                    <div class="flex gap-1">
                        <button v-for="link in returns.links" :key="link.label" :disabled="!link.url"
                            @click="link.url && router.get(link.url)" v-html="link.label"
                            :class="['px-3 py-1 text-xs rounded-lg border transition-colors', link.active ? 'bg-orange-600 border-orange-600 text-white' : 'border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-40']"/>
                    </div>
                </div>
            </div>
        </div>

        <!-- ═══ MODAL: BUAT RETUR ═══ -->
        <Transition enter-from-class="opacity-0" enter-active-class="transition duration-200" leave-to-class="opacity-0" leave-active-class="transition duration-150">
        <div v-if="showModal" class="fixed inset-0 z-50 flex items-start justify-center bg-black/50 backdrop-blur-sm p-4 overflow-y-auto">
            <div class="bg-surface-warm dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-4xl my-6">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ isEditing ? 'Edit Retur Barang' : 'Buat Retur Barang' }}</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ isEditing ? 'Perbarui data retur draft' : 'Retur Masuk = stok bertambah | Retur Keluar = stok berkurang' }}</p>
                    </div>
                    <button @click="showModal = false" class="p-2 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-400 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="p-6 space-y-5">
                    <!-- Return type selector -->
                    <div class="grid grid-cols-2 gap-3">
                        <button @click="form.return_type = 'return_in'"
                            :class="['flex items-center gap-3 p-4 rounded-xl border-2 transition-all', form.return_type === 'return_in' ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-gray-200 dark:border-gray-600 hover:border-blue-300']">
                            <div :class="['w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0', form.return_type === 'return_in' ? 'bg-blue-600' : 'bg-gray-200 dark:bg-gray-600']">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                            </div>
                            <div class="text-left">
                                <p class="font-semibold text-sm text-gray-800 dark:text-gray-200">Retur Masuk</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Barang dari customer kembali → stok +</p>
                            </div>
                        </button>
                        <button @click="form.return_type = 'return_out'"
                            :class="['flex items-center gap-3 p-4 rounded-xl border-2 transition-all', form.return_type === 'return_out' ? 'border-orange-500 bg-orange-50 dark:bg-orange-900/20' : 'border-gray-200 dark:border-gray-600 hover:border-orange-300']">
                            <div :class="['w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0', form.return_type === 'return_out' ? 'bg-orange-600' : 'bg-gray-200 dark:bg-gray-600']">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 10H11a8 8 0 00-8 8v2m18-10l-6 6m6-6l-6-6"/></svg>
                            </div>
                            <div class="text-left">
                                <p class="font-semibold text-sm text-gray-800 dark:text-gray-200">Retur Keluar</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Barang dikembalikan ke supplier → stok -</p>
                            </div>
                        </button>
                    </div>

                    <div v-if="hasStockWarning" class="flex items-center gap-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl px-4 py-3">
                        <svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.962-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                        <p class="text-sm text-red-700 dark:text-red-400 font-medium">Beberapa item melebihi stok tersedia di gudang.</p>
                    </div>

                    <!-- Header Fields -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1.5">Tanggal <span class="text-red-500">*</span></label>
                            <input v-model="form.return_date" type="date"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-orange-500"/>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1.5">Gudang <span class="text-red-500">*</span></label>
                            <select v-model="form.warehouse_id" @change="onWarehouseChange"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-orange-500">
                                <option value="">-- Pilih Gudang --</option>
                                <option v-for="w in warehouses" :key="w.id" :value="w.id">{{ w.name }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1.5">Alasan / Keterangan</label>
                            <input v-model="form.reason" type="text" placeholder="Alasan retur (opsional)"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-orange-500"/>
                        </div>
                    </div>

                    <!-- Items -->
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-sm font-bold text-gray-800 dark:text-gray-200">Detail Barang</h4>
                            <button @click="addItem"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold bg-orange-50 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400 rounded-lg hover:bg-orange-100 dark:hover:bg-orange-900/50 transition-colors">
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
                                        <th class="text-right px-3 py-2 text-xs font-semibold text-gray-500 dark:text-gray-400 w-24">Qty</th>
                                        <th class="text-right px-3 py-2 text-xs font-semibold text-gray-500 dark:text-gray-400 w-32">Harga</th>
                                        <th class="text-left px-3 py-2 text-xs font-semibold text-gray-500 dark:text-gray-400 w-28">Kondisi</th>
                                        <th class="text-right px-3 py-2 text-xs font-semibold text-gray-500 dark:text-gray-400 w-28">Subtotal</th>
                                        <th class="w-10"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                    <tr v-if="form.items.length === 0">
                                        <td colspan="7" class="text-center py-8 text-gray-400 dark:text-gray-500 text-xs">Belum ada item. Klik "Tambah Baris".</td>
                                    </tr>
                                    <tr v-for="(item, idx) in form.items" :key="idx"
                                        :class="['hover:bg-gray-50/50 dark:hover:bg-gray-700/30', stockWarning(idx) ? 'bg-red-50/50 dark:bg-red-900/10' : '']">
                                        <td class="px-2 py-2">
                                            <select v-model="item.product_id" @change="onProductChange(idx)"
                                                class="w-full px-2 py-1.5 text-xs rounded-lg border border-gray-200 dark:border-gray-600 bg-surface-warm dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-orange-500">
                                                <option value="">-- Pilih Barang --</option>
                                                <option v-for="p in products" :key="p.id" :value="p.id">{{ p.sku }} — {{ p.name }}</option>
                                            </select>
                                        </td>
                                        <td class="px-2 py-2">
                                            <select v-model="item.unit_id"
                                                class="w-full px-2 py-1.5 text-xs rounded-lg border border-gray-200 dark:border-gray-600 bg-surface-warm dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-orange-500">
                                                <option value="">Satuan</option>
                                                <option v-for="u in units" :key="u.id" :value="u.id">{{ u.symbol }}</option>
                                            </select>
                                        </td>
                                        <td class="px-2 py-2">
                                            <input v-model.number="item.qty" @input="recalcSubtotal(idx)" type="number" min="0.01" step="0.01"
                                                :class="['w-full px-2 py-1.5 text-xs text-right rounded-lg border focus:outline-none focus:ring-2', stockWarning(idx) ? 'border-red-400 focus:ring-red-400 bg-red-50 dark:bg-red-900/20 text-red-700' : 'border-gray-200 dark:border-gray-600 bg-surface-warm dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:ring-orange-500']"/>
                                            <div v-if="form.return_type === 'return_out' && item.product_id" class="text-right mt-0.5">
                                                <span :class="['text-xs', stockWarning(idx) ? 'text-red-500 font-semibold' : 'text-gray-400']">Stok: {{ item.availableStock }}</span>
                                            </div>
                                        </td>
                                        <td class="px-2 py-2">
                                            <input v-model.number="item.price" @input="recalcSubtotal(idx)" type="number" min="0"
                                                class="w-full px-2 py-1.5 text-xs text-right rounded-lg border border-gray-200 dark:border-gray-600 bg-surface-warm dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-orange-500"/>
                                        </td>
                                        <td class="px-2 py-2">
                                            <select v-model="item.condition"
                                                class="w-full px-2 py-1.5 text-xs rounded-lg border border-gray-200 dark:border-gray-600 bg-surface-warm dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-orange-500">
                                                <option value="good">Baik</option>
                                                <option value="damaged">Rusak</option>
                                                <option value="expired">Kadaluarsa</option>
                                            </select>
                                        </td>
                                        <td class="px-3 py-2 text-right text-xs font-semibold text-gray-700 dark:text-gray-300">{{ formatNumber(item.subtotal) }}</td>
                                        <td class="px-2 py-2 text-center">
                                            <button @click="removeItem(idx)" class="p-1 rounded text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot v-if="form.items.length > 0" class="bg-gray-50 dark:bg-gray-700/50 border-t border-gray-200 dark:border-gray-700">
                                    <tr>
                                        <td colspan="5" class="px-3 py-2 text-xs font-bold text-gray-600 dark:text-gray-400 text-right">Total Nilai Retur</td>
                                        <td class="px-3 py-2 text-sm font-bold text-orange-600 dark:text-orange-400 text-right">Rp {{ formatNumber(grandTotal) }}</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-100 dark:border-gray-700">
                    <button @click="showModal = false" class="px-4 py-2 text-sm font-semibold text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-xl transition-colors">Batal</button>
                    <button @click="submitForm" :disabled="isSubmitting"
                        class="px-5 py-2 text-sm font-semibold bg-orange-600 hover:bg-orange-700 text-white rounded-xl shadow transition-all duration-200 disabled:opacity-60 disabled:cursor-not-allowed active:scale-95">
                        <span v-if="isSubmitting" class="inline-flex items-center gap-2">
                            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
                            Menyimpan...
                        </span>
                        <span v-else>{{ isEditing ? 'Simpan Perubahan' : 'Simpan Draft Retur' }}</span>
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
                        <span class="font-mono font-bold text-orange-600 dark:text-orange-400">{{ detailData.transaction_no }}</span>
                        <span :class="typeBadge(detailData.return_type)" class="px-2.5 py-1 rounded-full text-xs font-semibold">{{ typeLabel(detailData.return_type) }}</span>
                        <span :class="statusBadge(detailData.status)" class="px-2.5 py-1 rounded-full text-xs font-semibold">{{ detailData.status === 'completed' ? 'Selesai' : 'Draft' }}</span>
                    </div>
                    <button @click="showDetail = false" class="p-2 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-400 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="p-6 space-y-5">
                    <div class="grid grid-cols-3 gap-4">
                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-3"><p class="text-xs text-gray-400 mb-1">Tanggal</p><p class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ new Date(detailData.return_date).toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' }) }}</p></div>
                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-3"><p class="text-xs text-gray-400 mb-1">Gudang</p><p class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ detailData.warehouse.name }}</p></div>
                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-3"><p class="text-xs text-gray-400 mb-1">Alasan</p><p class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ detailData.reason ?? '-' }}</p></div>
                    </div>
                    <div class="border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden">
                        <div v-if="isLoadingDetail" class="text-center py-10"><svg class="w-6 h-6 animate-spin mx-auto text-gray-400" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg></div>
                        <table v-else class="w-full text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-700/50">
                                <tr>
                                    <th class="text-left px-4 py-2 text-xs font-semibold text-gray-500 dark:text-gray-400">Barang</th>
                                    <th class="text-center px-4 py-2 text-xs font-semibold text-gray-500 dark:text-gray-400">Qty</th>
                                    <th class="text-center px-4 py-2 text-xs font-semibold text-gray-500 dark:text-gray-400">Satuan</th>
                                    <th class="text-center px-4 py-2 text-xs font-semibold text-gray-500 dark:text-gray-400">Kondisi</th>
                                    <th class="text-right px-4 py-2 text-xs font-semibold text-gray-500 dark:text-gray-400">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                <tr v-for="item in detailItems" :key="item.id" class="hover:bg-gray-50/50 dark:hover:bg-gray-700/30">
                                    <td class="px-4 py-3"><p class="font-medium text-gray-800 dark:text-gray-200">{{ item.product?.name }}</p><p class="text-xs text-gray-400 font-mono">{{ item.product?.sku }}</p></td>
                                    <td class="px-4 py-3 text-center font-semibold text-gray-700 dark:text-gray-300">{{ formatNumber(item.qty) }}</td>
                                    <td class="px-4 py-3 text-center text-gray-500 dark:text-gray-400">{{ item.unit?.symbol }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span :class="item.condition === 'good' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300'" class="px-2 py-0.5 rounded-full text-xs font-semibold capitalize">{{ item.condition === 'good' ? 'Baik' : item.condition === 'damaged' ? 'Rusak' : 'Kadaluarsa' }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-right font-semibold text-gray-800 dark:text-gray-200">{{ formatNumber(item.subtotal) }}</td>
                                </tr>
                            </tbody>
                            <tfoot class="bg-gray-50 dark:bg-gray-700/50 border-t border-gray-200 dark:border-gray-700">
                                <tr>
                                    <td colspan="4" class="px-4 py-2 text-xs font-bold text-gray-600 dark:text-gray-400 text-right">Total</td>
                                    <td class="px-4 py-2 text-sm font-bold text-orange-600 dark:text-orange-400 text-right">Rp {{ formatNumber(detailItems.reduce((s, i) => s + i.subtotal, 0)) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="flex items-center justify-between px-6 py-4 border-t border-gray-100 dark:border-gray-700">
                    <p class="text-xs text-gray-400 dark:text-gray-500">Dibuat oleh: <span class="font-semibold text-gray-600 dark:text-gray-300">{{ detailData.creator?.name }}</span></p>
                    <div class="flex gap-2">
                        <button v-if="detailData.status === 'draft'" @click="deleteReturn(detailData.id)"
                            class="px-3 py-1.5 text-xs font-semibold text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/20 hover:bg-red-100 rounded-lg transition-colors">Hapus</button>
                        <button v-if="detailData.status === 'draft'" @click="confirmReturn(detailData.id)"
                            class="px-4 py-1.5 text-xs font-semibold bg-orange-600 hover:bg-orange-700 text-white rounded-lg shadow transition-all active:scale-95">✓ Konfirmasi Retur</button>
                        <div v-else class="flex gap-2">
                            <button @click="unconfirmReturn(detailData.id)"
                                class="px-4 py-1.5 text-xs font-semibold text-amber-700 bg-amber-50 dark:bg-amber-950/30 hover:bg-amber-100 dark:hover:bg-amber-900/50 rounded-lg transition-colors">
                                ↺ Ubah ke Draft (Rollback)
                            </button>
                            <button @click="showDetail = false"
                                class="px-4 py-1.5 text-xs font-semibold bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors">Tutup</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </Transition>
    </AuthenticatedLayout>
</template>
