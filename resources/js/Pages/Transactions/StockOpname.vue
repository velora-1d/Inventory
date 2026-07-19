<script setup lang="ts">
import { formatNumber, formatCurrency } from '@/utils/format';
import { showConfirm } from '@/confirm';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed, reactive, watch } from 'vue';

defineOptions({ layout: AuthenticatedLayout });


interface Warehouse { id: number; name: string; code: string; }
interface Unit      { id: number; name: string; symbol: string; }
interface Product   { id: number; name: string; sku: string; base_unit_id: number; baseUnit: { name: string; symbol: string } | null; }
interface StockRecord { product_id: number; warehouse_id: number; qty: number; }
interface OpnameItem  { id: number; product_id: number; unit_id: number; system_qty: number; physical_qty: number; difference: number; notes: string | null; product: Product; unit: Unit; }
interface Opname {
    id: number; transaction_no: string; opname_date: string; notes: string | null;
    status: 'draft' | 'completed'; items_count: number;
    warehouse: Warehouse; creator: { name: string };
}
interface Pagination<T> { data: T[]; current_page: number; last_page: number; per_page: number; total: number; links: { url: string | null; label: string; active: boolean }[]; }

const props = defineProps<{
    opnames: Pagination<Opname>;
    warehouses: Warehouse[];
    products: Product[];
    stocks: StockRecord[];
    units: Unit[];
    filters: { search?: string; status?: string; warehouse_id?: string; date_from?: string; date_to?: string };
}>();

// ─── Filters ──────────────────────────────────────────────
const search          = ref(props.filters.search ?? '');
const statusFilter    = ref(props.filters.status ?? '');
const warehouseFilter = ref(props.filters.warehouse_id ?? '');
const dateFrom        = ref(props.filters.date_from ?? '');
const dateTo          = ref(props.filters.date_to ?? '');

const applyFilters = () => {
    router.get(route('stock-opnames.index'), {
        search: search.value || undefined,
        status: statusFilter.value || undefined,
        warehouse_id: warehouseFilter.value || undefined,
        date_from: dateFrom.value || undefined,
        date_to: dateTo.value || undefined,
    }, { preserveState: true, replace: true });
};

// ─── Modal ────────────────────────────────────────────────
const showModal       = ref(false);
const showDetail      = ref(false);
const detailData      = ref<Opname | null>(null);
const detailItems     = ref<OpnameItem[]>([]);
const isLoadingDetail = ref(false);

// ─── Form ─────────────────────────────────────────────────
const isEditing = ref(false);
const editingId = ref<number | null>(null);
const isPopulatingEdit = ref(false);

const form = reactive({
    opname_date:  new Date().toISOString().slice(0, 10),
    warehouse_id: '' as number | string,
    notes: '',
    items: [] as { product_id: number; unit_id: number; system_qty: number; physical_qty: number; difference: number; notes: string }[],
});
const formErrors   = ref<Record<string, string>>({});
const isSubmitting = ref(false);

// Auto-load all products from selected warehouse
const loadWarehouseProducts = () => {
    if (isEditing.value || isPopulatingEdit.value) return; // Don't auto-load when editing because we fetch saved items
    if (!form.warehouse_id) { form.items = []; return; }
    const warehouseProducts = props.stocks
        .filter(s => s.warehouse_id === Number(form.warehouse_id) && s.qty > 0);

    form.items = warehouseProducts.map(s => {
        const product = props.products.find(p => p.id === s.product_id);
        return {
            product_id: s.product_id,
            unit_id: product?.base_unit_id ?? 0,
            system_qty: s.qty,
            physical_qty: s.qty, // default sama dulu, user edit nanti
            difference: 0,
            notes: '',
        };
    });
};

watch(() => form.warehouse_id, loadWarehouseProducts);

const recalcDifference = (idx: number) => {
    form.items[idx].difference = form.items[idx].physical_qty - form.items[idx].system_qty;
};

const totalDifferences = computed(() => form.items.filter(i => i.difference !== 0).length);
const hasDiscrepancy   = computed(() => form.items.some(i => i.difference !== 0));

const resetForm = () => {
    isEditing.value = false;
    editingId.value = null;
    form.opname_date  = new Date().toISOString().slice(0, 10);
    form.warehouse_id = '';
    form.notes = '';
    form.items = [];
    formErrors.value = {};
};

const openModal = () => { resetForm(); showModal.value = true; };

const editOpname = async (opname: any) => {
    resetForm();
    isEditing.value = true;
    editingId.value = opname.id;
    isPopulatingEdit.value = true;

    // Fetch detail items
    const res = await fetch(`/stock-opnames/${opname.id}`, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
    const data = await res.json();

    // Populate form
    form.opname_date = opname.opname_date;
    form.warehouse_id = opname.warehouse_id;
    form.notes = opname.notes ?? '';
    form.items = (data.items ?? []).map((item: any) => {
        return {
            product_id: item.product_id,
            unit_id: item.unit_id,
            system_qty: Number(item.system_qty),
            physical_qty: Number(item.physical_qty),
            difference: Number(item.difference),
            notes: item.notes ?? ''
        };
    });

    isPopulatingEdit.value = false;
    showModal.value = true;
};

const submitForm = () => {
    if (form.items.length === 0) {
        formErrors.value = { items: 'Pilih gudang terlebih dahulu untuk memuat daftar barang.' };
        return;
    }
    isSubmitting.value = true;
    const url = isEditing.value 
        ? route('stock-opnames.update', editingId.value!) 
        : route('stock-opnames.store');
    
    const method = isEditing.value ? 'put' : 'post';

    router[method](url, {
        opname_date: form.opname_date,
        warehouse_id: form.warehouse_id,
        notes: form.notes,
        items: form.items.map(i => ({
            product_id: i.product_id,
            unit_id: i.unit_id,
            system_qty: i.system_qty,
            physical_qty: i.physical_qty,
            notes: i.notes,
        })),
    }, {
        onSuccess: () => { showModal.value = false; resetForm(); },
        onError: (e) => { formErrors.value = e; },
        onFinish: () => { isSubmitting.value = false; },
    });
};

const viewDetail = async (opname: Opname) => {
    isLoadingDetail.value = true;
    showDetail.value = true;
    detailData.value = opname;
    try {
        const res = await fetch(`/stock-opnames/${opname.id}`, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
        const data = await res.json();
        detailItems.value = data.items ?? [];
    } finally { isLoadingDetail.value = false; }
};

const confirmOpname = async (id: number) => {
    if (!await showConfirm('Konfirmasi opname ini? Stok sistem akan disesuaikan mengikuti stok fisik.')) return;
    router.post(route('stock-opnames.confirm', id), {}, { onSuccess: () => { showDetail.value = false; } });
};

const unconfirmOpname = async (id: number) => {
    if (!await showConfirm('Batalkan konfirmasi opname ini? Status akan kembali menjadi Draft dan jumlah stok gudang akan dikembalikan ke stok sistem awal.')) return;
    router.post(route('stock-opnames.unconfirm', id), {}, { onSuccess: () => { showDetail.value = false; } });
};

const deleteOpname = async (id: number) => {
    if (!await showConfirm('Hapus opname ini?')) return;
    router.delete(route('stock-opnames.destroy', id));
};

const productName = (id: number) => props.products.find(p => p.id === id)?.name ?? '-';
const unitSymbol  = (id: number) => props.units.find(u => u.id === id)?.symbol ?? '-';

const statusBadge = (s: string) => s === 'completed'
    ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300'
    : 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300';
const diffClass = (d: number) => d > 0 ? 'text-emerald-600 dark:text-emerald-400' : d < 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-400';
</script>

<template>
    <Head title="Stock Opname" />
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Stock Opname</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Penyesuaian stok sistem dengan kondisi fisik di gudang</p>
                </div>
                <button @click="openModal"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-cyan-600 hover:bg-cyan-700 text-white text-sm font-semibold rounded-xl shadow transition-all duration-200 hover:shadow-md active:scale-95">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                    Buat Opname
                </button>
            </div>

        <div class="py-6 px-4 sm:px-6 lg:px-8 space-y-5">
            <!-- Filters -->
            <div class="bg-surface-warm dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
                    <input v-model="search" @keyup.enter="applyFilters" type="text" placeholder="Cari no. opname..."
                        class="col-span-2 px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-cyan-500"/>
                    <select v-model="statusFilter" @change="applyFilters"
                        class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-cyan-500">
                        <option value="">Semua Status</option>
                        <option value="draft">Draft</option>
                        <option value="completed">Selesai</option>
                    </select>
                    <select v-model="warehouseFilter" @change="applyFilters"
                        class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-cyan-500">
                        <option value="">Semua Gudang</option>
                        <option v-for="w in warehouses" :key="w.id" :value="w.id">{{ w.name }}</option>
                    </select>
                    <input v-model="dateFrom" @change="applyFilters" type="date" class=" px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-cyan-500"/>
                    <input v-model="dateTo"   @change="applyFilters" type="date" class=" px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-cyan-500"/>
                </div>
            </div>

            <!-- Table -->
            <div class="bg-surface-warm dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-100 dark:border-gray-700">
                            <tr>
                                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">No. Opname</th>
                                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tanggal</th>
                                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Gudang</th>
                                <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Items</th>
                                <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 dark:divide-gray-700/50">
                            <tr v-if="opnames.data.length === 0">
                                <td colspan="6" class="text-center py-16 text-gray-400 dark:text-gray-500">
                                    <svg class="w-12 h-12 mx-auto mb-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                                    <p class="font-medium">Belum ada data stock opname</p>
                                    <p class="text-xs mt-1">Klik "Buat Opname" untuk memulai</p>
                                </td>
                            </tr>
                            <tr v-for="op in opnames.data" :key="op.id" class="hover:bg-cyan-50/40 dark:hover:bg-cyan-900/10 transition-colors duration-100">
                                <td class="px-4 py-3"><span class="font-mono font-semibold text-cyan-600 dark:text-cyan-400 text-xs">{{ op.transaction_no }}</span></td>
                                <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ new Date(op.opname_date).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) }}</td>
                                <td class="px-4 py-3">
                                    <p class="font-medium text-gray-800 dark:text-gray-200">{{ op.warehouse.name }}</p>
                                    <p class="text-xs text-gray-400">{{ op.warehouse.code }}</p>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-cyan-100 dark:bg-cyan-900/40 text-cyan-700 dark:text-cyan-300 text-xs font-bold">{{ op.items_count }}</span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span :class="statusBadge(op.status)" class="px-2.5 py-1 rounded-full text-xs font-semibold">{{ op.status === 'completed' ? 'Selesai' : 'Draft' }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-center gap-1">
                                        <button @click="viewDetail(op)" class="p-1.5 rounded-lg text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-cyan-600 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </button>
                                        <!-- Edit (draft only) -->
                                        <button v-if="op.status === 'draft'" @click="editOpname(op)" title="Edit Draft"
                                            class="p-1.5 rounded-lg text-gray-500 hover:bg-blue-100 dark:hover:bg-blue-900/30 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                        </button>
                                        <button v-if="op.status === 'draft'" @click="confirmOpname(op.id)" class="p-1.5 rounded-lg text-gray-500 hover:bg-emerald-100 dark:hover:bg-emerald-900/30 hover:text-emerald-600 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        </button>
                                        <button v-if="op.status === 'draft'" @click="deleteOpname(op.id)" class="p-1.5 rounded-lg text-gray-500 hover:bg-red-100 dark:hover:bg-red-900/30 hover:text-red-600 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-if="opnames.last_page > 1" class="px-4 py-3 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <p class="text-xs text-gray-500 dark:text-gray-400">Total {{ opnames.total }} opname</p>
                    <div class="flex gap-1">
                        <button v-for="link in opnames.links" :key="link.label" :disabled="!link.url"
                            @click="link.url && router.get(link.url)" v-html="link.label"
                            :class="['px-3 py-1 text-xs rounded-lg border transition-colors', link.active ? 'bg-cyan-600 border-cyan-600 text-white' : 'border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-40']"/>
                    </div>
                </div>
            </div>
        </div>

        <!-- ═══ MODAL: BUAT OPNAME ═══ -->
        <Transition enter-from-class="opacity-0" enter-active-class="transition duration-200" leave-to-class="opacity-0" leave-active-class="transition duration-150">
        <div v-if="showModal" class="fixed inset-0 z-50 flex items-start justify-center bg-black/50 backdrop-blur-sm p-4 overflow-y-auto">
            <div class="bg-surface-warm dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-5xl my-6">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ isEditing ? 'Edit Stock Opname' : 'Buat Stock Opname' }}</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ isEditing ? 'Perbarui data opname draft' : 'Pilih gudang → sistem otomatis memuat daftar barang & stok sistem' }}</p>
                    </div>
                    <button @click="showModal = false" class="p-2 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-400 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="p-6 space-y-5">
                    <!-- Discrepancy banner -->
                    <div v-if="hasDiscrepancy" class="flex items-center gap-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl px-4 py-3">
                        <svg class="w-5 h-5 text-amber-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.962-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                        <p class="text-sm text-amber-700 dark:text-amber-400 font-medium">
                            Ditemukan <strong>{{ totalDifferences }}</strong> barang dengan selisih antara stok sistem dan fisik.
                        </p>
                    </div>

                    <!-- Header -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1.5">Tanggal Opname <span class="text-red-500">*</span></label>
                            <input v-model="form.opname_date" type="date"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-cyan-500"/>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1.5">Gudang <span class="text-red-500">*</span></label>
                            <select v-model="form.warehouse_id"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-cyan-500">
                                <option value="">-- Pilih Gudang --</option>
                                <option v-for="w in warehouses" :key="w.id" :value="w.id">{{ w.name }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1.5">Catatan</label>
                            <input v-model="form.notes" type="text" placeholder="Catatan opname (opsional)"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-cyan-500"/>
                        </div>
                    </div>

                    <!-- Items Table -->
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-sm font-bold text-gray-800 dark:text-gray-200">
                                Daftar Barang
                                <span v-if="form.items.length" class="text-xs font-normal text-gray-400 ml-2">({{ form.items.length }} item dari gudang terpilih)</span>
                            </h4>
                        </div>
                        <p v-if="formErrors.items" class="text-red-500 text-xs mb-2">{{ formErrors.items }}</p>

                        <div v-if="!form.warehouse_id" class="text-center py-12 text-gray-400 dark:text-gray-500 border border-dashed border-gray-200 dark:border-gray-700 rounded-xl">
                            <svg class="w-10 h-10 mx-auto mb-2 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            <p class="text-sm font-medium">Pilih gudang untuk memuat daftar barang</p>
                        </div>

                        <div v-else-if="form.items.length === 0" class="text-center py-12 text-gray-400 dark:text-gray-500 border border-dashed border-gray-200 dark:border-gray-700 rounded-xl">
                            <p class="text-sm font-medium">Tidak ada barang dengan stok di gudang ini</p>
                        </div>

                        <div v-else class="border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden max-h-96 overflow-y-auto">
                            <table class="w-full text-sm">
                                <thead class="bg-gray-50 dark:bg-gray-700/50 sticky top-0">
                                    <tr>
                                        <th class="text-left px-3 py-2 text-xs font-semibold text-gray-500 dark:text-gray-400">Barang</th>
                                        <th class="text-center px-3 py-2 text-xs font-semibold text-gray-500 dark:text-gray-400 w-24">Stok Sistem</th>
                                        <th class="text-center px-3 py-2 text-xs font-semibold text-gray-500 dark:text-gray-400 w-28">Stok Fisik</th>
                                        <th class="text-center px-3 py-2 text-xs font-semibold text-gray-500 dark:text-gray-400 w-20">Selisih</th>
                                        <th class="text-left px-3 py-2 text-xs font-semibold text-gray-500 dark:text-gray-400">Catatan Item</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                    <tr v-for="(item, idx) in form.items" :key="idx"
                                        :class="['hover:bg-gray-50/50 dark:hover:bg-gray-700/30', item.difference !== 0 ? 'bg-amber-50/30 dark:bg-amber-900/10' : '']">
                                        <td class="px-3 py-2">
                                            <p class="font-medium text-gray-800 dark:text-gray-200 text-xs">{{ productName(item.product_id) }}</p>
                                            <p class="text-xs text-gray-400">{{ unitSymbol(item.unit_id) }}</p>
                                        </td>
                                        <td class="px-3 py-2 text-center">
                                            <span class="text-sm font-semibold text-gray-600 dark:text-gray-300">{{ formatNumber(item.system_qty) }}</span>
                                        </td>
                                        <td class="px-3 py-2">
                                            <input v-model.number="item.physical_qty" @input="recalcDifference(idx)" type="number" min="0" step="0.01"
                                                :class="['w-full px-2 py-1 text-sm text-center rounded-lg border focus:outline-none focus:ring-2', item.difference !== 0 ? 'border-amber-400 bg-amber-50 dark:bg-amber-900/20 focus:ring-amber-400' : 'border-gray-200 dark:border-gray-600 bg-surface-warm dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:ring-cyan-500']"/>
                                        </td>
                                        <td class="px-3 py-2 text-center">
                                            <span :class="['text-sm font-bold', diffClass(item.difference)]">
                                                {{ item.difference > 0 ? '+' : '' }}{{ formatNumber(item.difference) }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-2">
                                            <input v-model="item.notes" type="text" placeholder="Keterangan..."
                                                class="w-full px-2 py-1 text-xs rounded-lg border border-gray-200 dark:border-gray-600 bg-surface-warm dark:bg-gray-700 text-gray-700 dark:text-gray-300 focus:outline-none focus:ring-2 focus:ring-cyan-500"/>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-100 dark:border-gray-700">
                    <button @click="showModal = false"
                        class="px-4 py-2 text-sm font-semibold text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-xl transition-colors">Batal</button>
                    <button @click="submitForm" :disabled="isSubmitting || !form.warehouse_id"
                        class="px-5 py-2 text-sm font-semibold bg-cyan-600 hover:bg-cyan-700 text-white rounded-xl shadow transition-all duration-200 disabled:opacity-60 disabled:cursor-not-allowed active:scale-95">
                        <span v-if="isSubmitting" class="inline-flex items-center gap-2">
                            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
                            Menyimpan...
                        </span>
                        <span v-else>{{ isEditing ? 'Simpan Perubahan' : 'Simpan Draft Opname' }}</span>
                    </button>
                </div>
            </div>
        </div>
        </Transition>

        <!-- ═══ MODAL: DETAIL ═══ -->
        <Transition enter-from-class="opacity-0" enter-active-class="transition duration-200" leave-to-class="opacity-0" leave-active-class="transition duration-150">
        <div v-if="showDetail && detailData" class="fixed inset-0 z-50 flex items-start justify-center bg-black/50 backdrop-blur-sm p-4 overflow-y-auto">
            <div class="bg-surface-warm dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-4xl my-6">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                    <div class="flex items-center gap-3">
                        <span class="font-mono font-bold text-cyan-600 dark:text-cyan-400">{{ detailData.transaction_no }}</span>
                        <span :class="statusBadge(detailData.status)" class="px-2.5 py-1 rounded-full text-xs font-semibold">{{ detailData.status === 'completed' ? 'Selesai' : 'Draft' }}</span>
                    </div>
                    <button @click="showDetail = false" class="p-2 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-400 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="p-6 space-y-5">
                    <div class="grid grid-cols-3 gap-4">
                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-3"><p class="text-xs text-gray-400 mb-1">Tanggal</p><p class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ new Date(detailData.opname_date).toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' }) }}</p></div>
                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-3"><p class="text-xs text-gray-400 mb-1">Gudang</p><p class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ detailData.warehouse.name }}</p></div>
                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-3"><p class="text-xs text-gray-400 mb-1">Catatan</p><p class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ detailData.notes ?? '-' }}</p></div>
                    </div>
                    <div class="border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden">
                        <div v-if="isLoadingDetail" class="text-center py-10"><svg class="w-6 h-6 animate-spin mx-auto text-gray-400" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg></div>
                        <table v-else class="w-full text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-700/50">
                                <tr>
                                    <th class="text-left px-4 py-2 text-xs font-semibold text-gray-500 dark:text-gray-400">Barang</th>
                                    <th class="text-center px-4 py-2 text-xs font-semibold text-gray-500 dark:text-gray-400">Stok Sistem</th>
                                    <th class="text-center px-4 py-2 text-xs font-semibold text-gray-500 dark:text-gray-400">Stok Fisik</th>
                                    <th class="text-center px-4 py-2 text-xs font-semibold text-gray-500 dark:text-gray-400">Selisih</th>
                                    <th class="text-left px-4 py-2 text-xs font-semibold text-gray-500 dark:text-gray-400">Catatan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                <tr v-for="item in detailItems" :key="item.id"
                                    :class="['hover:bg-gray-50/50 dark:hover:bg-gray-700/30', item.difference !== 0 ? 'bg-amber-50/30 dark:bg-amber-900/10' : '']">
                                    <td class="px-4 py-3"><p class="font-medium text-gray-800 dark:text-gray-200">{{ item.product?.name }}</p><p class="text-xs text-gray-400 font-mono">{{ item.product?.sku }}</p></td>
                                    <td class="px-4 py-3 text-center font-semibold text-gray-600 dark:text-gray-300">{{ formatNumber(item.system_qty) }}</td>
                                    <td class="px-4 py-3 text-center font-semibold text-gray-600 dark:text-gray-300">{{ formatNumber(item.physical_qty) }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span :class="['font-bold text-sm', diffClass(item.difference)]">{{ item.difference > 0 ? '+' : '' }}{{ formatNumber(item.difference) }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-xs text-gray-500 dark:text-gray-400">{{ item.notes ?? '-' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="flex items-center justify-between px-6 py-4 border-t border-gray-100 dark:border-gray-700">
                    <p class="text-xs text-gray-400 dark:text-gray-500">Dibuat oleh: <span class="font-semibold text-gray-600 dark:text-gray-300">{{ detailData.creator?.name }}</span></p>
                    <div class="flex gap-2">
                        <button v-if="detailData.status === 'draft'" @click="deleteOpname(detailData.id)"
                            class="px-3 py-1.5 text-xs font-semibold text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/20 hover:bg-red-100 rounded-lg transition-colors">Hapus</button>
                        <button v-if="detailData.status === 'draft'" @click="confirmOpname(detailData.id)"
                            class="px-4 py-1.5 text-xs font-semibold bg-cyan-600 hover:bg-cyan-700 text-white rounded-lg shadow transition-all active:scale-95">✓ Konfirmasi & Sesuaikan Stok</button>
                        <div v-else class="flex gap-2">
                            <button @click="unconfirmOpname(detailData.id)"
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
</template>
