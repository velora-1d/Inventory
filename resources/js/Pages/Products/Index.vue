<script setup lang="ts">
import { showConfirm } from '@/confirm';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

defineOptions({ layout: AuthenticatedLayout });


interface Category {
    id: number;
    name: string;
}

interface Unit {
    id: number;
    name: string;
    symbol: string;
}

interface Warehouse {
    id: number;
    name: string;
}

interface Product {
    id: number;
    sku: string;
    name: string;
    category_id: number;
    base_unit_id: number;
    purchase_unit_id: number | null;
    sale_unit_id: number | null;
    purchase_price: number;
    sale_price: number;
    avg_price: number;
    min_stock: number;
    default_warehouse_id: number | null;
    description: string | null;
    photo: string | null;
    status: 'active' | 'inactive';
    category?: Category;
    base_unit?: Unit;
    default_warehouse?: Warehouse;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface PaginatedProducts {
    data: Product[];
    links: PaginationLink[];
    current_page: number;
    last_page: number;
    total: number;
}

const props = defineProps<{
    products: PaginatedProducts;
    categories: Category[];
    units: Unit[];
    warehouses: Warehouse[];
    filters: {
        search?: string;
        category_id?: string;
        warehouse_id?: string;
        status?: string;
    };
}>();

// Search & Filter
const searchQuery = ref(props.filters.search || '');
const categoryFilter = ref(props.filters.category_id || '');
const warehouseFilter = ref(props.filters.warehouse_id || '');
const statusFilter = ref(props.filters.status || '');

const applyFilters = () => {
    router.get(route('products.index'), {
        search: searchQuery.value,
        category_id: categoryFilter.value,
        warehouse_id: warehouseFilter.value,
        status: statusFilter.value
    }, {
        preserveState: true,
        preserveScroll: true
    });
};

const resetFilters = () => {
    searchQuery.value = '';
    categoryFilter.value = '';
    warehouseFilter.value = '';
    statusFilter.value = '';
    applyFilters();
};

// Modal State
const isModalOpen = ref(false);
const modalMode = ref<'create' | 'edit'>('create');
const selectedProductId = ref<number | null>(null);
const photoPreview = ref<string | null>(null);

const form = useForm({
    sku: '',
    name: '',
    category_id: '' as number | string,
    base_unit_id: '' as number | string,
    purchase_unit_id: '' as number | string | null,
    sale_unit_id: '' as number | string | null,
    purchase_price: 0,
    sale_price: 0,
    min_stock: 0,
    default_warehouse_id: '' as number | string | null,
    description: '',
    status: 'active' as 'active' | 'inactive',
    photo_file: null as File | null,
});

const handlePhotoChange = (e: Event) => {
    const target = e.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        const file = target.files[0];
        form.photo_file = file;
        photoPreview.value = URL.createObjectURL(file);
    }
};

const openCreateModal = () => {
    modalMode.value = 'create';
    form.reset();
    form.clearErrors();
    photoPreview.value = null;
    form.sku = 'SKU-' + Math.floor(100000 + Math.random() * 900000);
    isModalOpen.value = true;
};

const openEditModal = (product: Product) => {
    modalMode.value = 'edit';
    selectedProductId.value = product.id;
    form.sku = product.sku;
    form.name = product.name;
    form.category_id = product.category_id;
    form.base_unit_id = product.base_unit_id;
    form.purchase_unit_id = product.purchase_unit_id || '';
    form.sale_unit_id = product.sale_unit_id || '';
    form.purchase_price = Number(product.purchase_price);
    form.sale_price = Number(product.sale_price);
    form.min_stock = Number(product.min_stock);
    form.default_warehouse_id = product.default_warehouse_id || '';
    form.description = product.description || '';
    form.status = product.status;
    form.photo_file = null;
    photoPreview.value = product.photo;
    form.clearErrors();
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    selectedProductId.value = null;
    photoPreview.value = null;
    form.reset();
};

const submitForm = () => {
    // Transform empty strings to null for optional foreign keys
    const prepareData = () => {
        form.purchase_unit_id = form.purchase_unit_id === '' ? null : form.purchase_unit_id;
        form.sale_unit_id = form.sale_unit_id === '' ? null : form.sale_unit_id;
        form.default_warehouse_id = form.default_warehouse_id === '' ? null : form.default_warehouse_id;
    };

    prepareData();

    if (modalMode.value === 'create') {
        form.post(route('products.store'), {
            onSuccess: () => closeModal(),
        });
    } else if (modalMode.value === 'edit' && selectedProductId.value) {
        // Workaround for Inertia file upload in PUT requests
        form.transform((data) => ({
            ...data,
            _method: 'PUT'
        })).post(route('products.update', selectedProductId.value), {
            onSuccess: () => closeModal(),
        });
    }
};

const deleteProduct = async (id: number) => {
    if (await showConfirm('Apakah Anda yakin ingin menghapus barang ini?')) {
        router.delete(route('products.destroy', id), {
            preserveScroll: true,
        });
    }
};

const formatIDR = (value: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(value);
};

const formatNumber = (value: number) => {
    return new Intl.NumberFormat('id-ID').format(value ?? 0);
};
</script>

<template>
    <Head title="Data Barang" />
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-4 sm:space-y-0">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-slate-800 dark:text-slate-100">
                        Data Barang / SKU
                    </h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                        Kelola katalog produk, harga beli/jual, stok minimum, dan satuan barang.
                    </p>
                </div>

                <button 
                    @click="openCreateModal"
                    class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm shadow-sm transition-colors duration-200"
                >
                    <svg class="h-5 w-5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Tambah Barang
                </button>
            </div>

        <!-- Filters Section -->
        <div class="bg-surface-warm dark:bg-surface-warm p-4 rounded-2xl border border-border-warm dark:border-border-warm shadow-sm mb-6 flex flex-col xl:flex-row xl:items-center gap-4">
            <div class="flex-1">
                <input 
                    type="text" 
                    v-model="searchQuery" 
                    placeholder="Cari SKU, nama barang, atau deskripsi..." 
                    @keyup.enter="applyFilters"
                    class="w-full rounded-xl border-border-warm dark:border-border-warm bg-surface-warm dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200/50 shadow-sm"
                />
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 w-full xl:w-[600px]">
                <select 
                    v-model="categoryFilter" 
                    @change="applyFilters"
                    class="rounded-xl border-border-warm dark:border-border-warm bg-surface-warm dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200/50 shadow-sm"
                >
                    <option value="">Semua Kategori</option>
                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                        {{ cat.name }}
                    </option>
                </select>

                <select 
                    v-model="warehouseFilter" 
                    @change="applyFilters"
                    class="rounded-xl border-border-warm dark:border-border-warm bg-surface-warm dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200/50 shadow-sm"
                >
                    <option value="">Semua Gudang</option>
                    <option v-for="wh in warehouses" :key="wh.id" :value="wh.id">
                        {{ wh.name }}
                    </option>
                </select>

                <select 
                    v-model="statusFilter" 
                    @change="applyFilters"
                    class="rounded-xl border-border-warm dark:border-border-warm bg-surface-warm dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200/50 shadow-sm"
                >
                    <option value="">Semua Status</option>
                    <option value="active">Aktif</option>
                    <option value="inactive">Nonaktif</option>
                </select>
            </div>
            <div class="flex gap-2 shrink-0">
                <button 
                    @click="applyFilters"
                    class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-750 text-slate-700 dark:text-slate-300 font-medium text-sm transition-colors"
                >
                    Cari
                </button>
                <button 
                    @click="resetFilters"
                    class="px-4 py-2 rounded-xl border border-border-warm dark:border-border-warm hover:bg-slate-50 dark:hover:bg-slate-850 text-slate-500 dark:text-slate-455 font-medium text-sm transition-colors"
                >
                    Reset
                </button>
            </div>
        </div>

        <!-- Products Table Card -->
        <div class="bg-surface-warm dark:bg-surface-warm rounded-2xl border border-border-warm dark:border-border-warm shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/50 dark:bg-surface-warm/50 text-slate-400 dark:text-slate-500 text-xs font-semibold uppercase">
                            <th class="p-4 w-16">No</th>
                            <th class="p-4 w-16">Foto</th>
                            <th class="p-4 w-32">SKU</th>
                            <th class="p-4">Nama Barang</th>
                            <th class="p-4">Kategori</th>
                            <th class="p-4 text-right">Harga Beli</th>
                            <th class="p-4 text-right">Harga Jual</th>
                            <th class="p-4 text-right">Stok Min</th>
                            <th class="p-4 w-32">Status</th>
                            <th class="p-4 w-32 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-150 dark:divide-slate-800 text-sm">
                        <tr v-for="(product, idx) in products.data" :key="product.id" class="text-slate-700 dark:text-slate-300 hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors">
                            <td class="p-4 text-slate-400">
                                {{ (products.current_page - 1) * 10 + idx + 1 }}
                            </td>
                            <td class="p-4">
                                <img 
                                    v-if="product.photo" 
                                    :src="product.photo" 
                                    alt="photo" 
                                    class="w-10 h-10 rounded-full object-cover ring-2 ring-slate-100 dark:ring-slate-850"
                                />
                                <div v-else class="w-10 h-10 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-400 flex items-center justify-center font-bold text-xs uppercase">
                                    {{ product.name.charAt(0) }}
                                </div>
                            </td>
                            <td class="p-4 font-mono font-semibold text-slate-500 dark:text-slate-455">
                                {{ product.sku }}
                            </td>
                            <td class="p-4 font-semibold text-slate-800 dark:text-slate-100">
                                <div>{{ product.name }}</div>
                                <div class="text-xs text-slate-450 mt-0.5 line-clamp-1 max-w-xs">{{ product.description || '-' }}</div>
                            </td>
                            <td class="p-4">
                                <span class="bg-orange-100 text-text-primary dark:bg-orange-950/30 dark:text-gray-250 px-2 py-0.5 rounded text-xs font-semibold">
                                    {{ product.category?.name || 'Tanpa Kategori' }}
                                </span>
                            </td>
                            <td class="p-4 text-right font-mono">{{ formatIDR(product.purchase_price) }}</td>
                            <td class="p-4 text-right font-mono">{{ formatIDR(product.sale_price) }}</td>
                            <td class="p-4 text-right font-semibold">
                                {{ formatNumber(product.min_stock) }} <span class="text-xs text-slate-450">{{ product.base_unit?.symbol }}</span>
                            </td>
                            <td class="p-4">
                                <span 
                                    :class="[
                                        product.status === 'active' 
                                            ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-455' 
                                            : 'bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-455',
                                        'px-2 py-0.5 rounded-full text-xs font-semibold tracking-wide border border-transparent'
                                    ]"
                                >
                                    {{ product.status === 'active' ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="p-4 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button 
                                        @click="openEditModal(product)"
                                        class="p-1.5 text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-lg transition-colors"
                                        title="Ubah"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>
                                    <button 
                                        @click="deleteProduct(product.id)"
                                        class="p-1.5 text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-900/30 rounded-lg transition-colors"
                                        title="Hapus"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="products.data.length === 0">
                            <td colspan="10" class="p-8 text-center text-slate-450 dark:text-slate-500">
                                Data Barang tidak ditemukan.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Inline Pagination -->
            <div v-if="products.last_page > 1" class="p-4 border-t border-border-warm dark:border-border-warm flex items-center justify-between">
                <span class="text-xs text-slate-500 dark:text-slate-400">
                    Menampilkan {{ products.data.length }} dari {{ products.total }} data
                </span>
                <div class="flex space-x-1">
                    <Link
                        v-for="link in products.links"
                        :key="link.label"
                        :href="link.url || '#'"
                        v-html="link.label"
                        :class="[
                            link.active 
                                ? 'bg-indigo-600 text-white font-semibold' 
                                : 'text-slate-650 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800',
                            !link.url ? 'opacity-50 cursor-not-allowed' : '',
                            'px-3 py-1.5 rounded-lg text-xs transition-colors'
                        ]"
                    />
                </div>
            </div>
        </div>

        <!-- Form Modal Dialog -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="closeModal"></div>
            
            <div class="bg-surface-warm dark:bg-surface-warm border border-border-warm dark:border-border-warm rounded-2xl max-w-2xl w-full shadow-2xl overflow-hidden relative z-10">
                <div class="p-6 border-b border-border-warm dark:border-border-warm flex items-center justify-between">
                    <h3 class="font-bold text-lg text-slate-800 dark:text-slate-200">
                        {{ modalMode === 'create' ? 'Tambah Barang' : 'Edit Barang' }}
                    </h3>
                    <button @click="closeModal" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="submitForm" class="p-6 space-y-4 max-h-[75vh] overflow-y-auto custom-scrollbar">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Photo Upload / Preview -->
                        <div class="col-span-1 md:col-span-2 flex items-center space-x-4 p-3 bg-slate-50 dark:bg-slate-950 rounded-xl border border-border-warm dark:border-slate-850">
                            <div class="relative w-16 h-16 rounded-full bg-slate-200 dark:bg-slate-800 flex items-center justify-center text-slate-550 dark:text-slate-400 overflow-hidden ring-2 ring-indigo-500/20">
                                <img v-if="photoPreview" :src="photoPreview" class="w-full h-full object-cover" alt="preview" />
                                <svg v-else class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <div class="space-y-1">
                                <label class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">Foto Barang</label>
                                <input type="file" @change="handlePhotoChange" accept="image/*" class="text-xs text-slate-500 file:mr-4 file:py-1 file:px-2 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 dark:file:bg-slate-800 dark:file:text-slate-350" />
                                <p class="text-[10px] text-slate-400">Format: JPG, PNG, GIF (Maks. 2MB)</p>
                            </div>
                        </div>

                        <!-- SKU -->
                        <div class="space-y-1">
                            <label for="modal-sku" class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">
                                SKU / Kode Barang
                            </label>
                            <input 
                                id="modal-sku"
                                type="text" 
                                v-model="form.sku" 
                                required
                                class="w-full rounded-xl border-border-warm dark:border-border-warm bg-surface-warm dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200/50"
                            />
                            <span v-if="form.errors.sku" class="text-xs text-rose-500 mt-1 block">
                                {{ form.errors.sku }}
                            </span>
                        </div>

                        <!-- Name -->
                        <div class="space-y-1">
                            <label for="modal-name" class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">
                                Nama Barang
                            </label>
                            <input 
                                id="modal-name"
                                type="text" 
                                v-model="form.name" 
                                required
                                class="w-full rounded-xl border-border-warm dark:border-border-warm bg-surface-warm dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200/50"
                            />
                            <span v-if="form.errors.name" class="text-xs text-rose-500 mt-1 block">
                                {{ form.errors.name }}
                            </span>
                        </div>

                        <!-- Category -->
                        <div class="space-y-1">
                            <label for="modal-category" class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">
                                Kategori
                            </label>
                            <select 
                                id="modal-category"
                                v-model="form.category_id" 
                                required
                                class="w-full rounded-xl border-border-warm dark:border-border-warm bg-surface-warm dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200/50"
                            >
                                <option value="">Pilih Kategori</option>
                                <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                                    {{ cat.name }}
                                </option>
                            </select>
                            <span v-if="form.errors.category_id" class="text-xs text-rose-500 mt-1 block">
                                {{ form.errors.category_id }}
                            </span>
                        </div>

                        <!-- Default Warehouse -->
                        <div class="space-y-1">
                            <label for="modal-wh" class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">
                                Gudang Default
                            </label>
                            <select 
                                id="modal-wh"
                                v-model="form.default_warehouse_id" 
                                class="w-full rounded-xl border-border-warm dark:border-border-warm bg-surface-warm dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200/50"
                            >
                                <option value="">Pilih Gudang Default (Opsional)</option>
                                <option v-for="wh in warehouses" :key="wh.id" :value="wh.id">
                                    {{ wh.name }}
                                </option>
                            </select>
                            <span v-if="form.errors.default_warehouse_id" class="text-xs text-rose-500 mt-1 block">
                                {{ form.errors.default_warehouse_id }}
                            </span>
                        </div>

                        <!-- Base Unit -->
                        <div class="space-y-1">
                            <label for="modal-unit" class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">
                                Satuan Dasar (Base Unit)
                            </label>
                            <select 
                                id="modal-unit"
                                v-model="form.base_unit_id" 
                                required
                                class="w-full rounded-xl border-border-warm dark:border-border-warm bg-surface-warm dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200/50"
                            >
                                <option value="">Pilih Satuan Dasar</option>
                                <option v-for="u in units" :key="u.id" :value="u.id">
                                    {{ u.name }} ({{ u.symbol }})
                                </option>
                            </select>
                            <span v-if="form.errors.base_unit_id" class="text-xs text-rose-500 mt-1 block">
                                {{ form.errors.base_unit_id }}
                            </span>
                        </div>

                        <!-- Min Stock -->
                        <div class="space-y-1">
                            <label for="modal-minstock" class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">
                                Stok Minimum (Alert)
                            </label>
                            <input 
                                id="modal-minstock"
                                type="number" 
                                v-model="form.min_stock" 
                                required
                                min="0"
                                class="w-full rounded-xl border-border-warm dark:border-border-warm bg-surface-warm dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200/50"
                            />
                            <span v-if="form.errors.min_stock" class="text-xs text-rose-500 mt-1 block">
                                {{ form.errors.min_stock }}
                            </span>
                        </div>

                        <!-- Purchase Price -->
                        <div class="space-y-1">
                            <label for="modal-buyprice" class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">
                                Harga Beli (IDR)
                            </label>
                            <input 
                                id="modal-buyprice"
                                type="number" 
                                v-model="form.purchase_price" 
                                required
                                min="0"
                                class="w-full rounded-xl border-border-warm dark:border-border-warm bg-surface-warm dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200/50"
                            />
                            <span v-if="form.errors.purchase_price" class="text-xs text-rose-500 mt-1 block">
                                {{ form.errors.purchase_price }}
                            </span>
                        </div>

                        <!-- Sale Price -->
                        <div class="space-y-1">
                            <label for="modal-sellprice" class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">
                                Harga Jual (IDR)
                            </label>
                            <input 
                                id="modal-sellprice"
                                type="number" 
                                v-model="form.sale_price" 
                                required
                                min="0"
                                class="w-full rounded-xl border-border-warm dark:border-border-warm bg-surface-warm dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200/50"
                            />
                            <span v-if="form.errors.sale_price" class="text-xs text-rose-500 mt-1 block">
                                {{ form.errors.sale_price }}
                            </span>
                        </div>

                        <!-- Purchase Unit -->
                        <div class="space-y-1">
                            <label for="modal-purunit" class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">
                                Satuan Beli (Opsional)
                            </label>
                            <select 
                                id="modal-purunit"
                                v-model="form.purchase_unit_id" 
                                class="w-full rounded-xl border-border-warm dark:border-border-warm bg-surface-warm dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200/50"
                            >
                                <option value="">Sama dengan Satuan Dasar</option>
                                <option v-for="u in units" :key="u.id" :value="u.id">
                                    {{ u.name }} ({{ u.symbol }})
                                </option>
                            </select>
                        </div>

                        <!-- Sale Unit -->
                        <div class="space-y-1">
                            <label for="modal-saleunit" class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">
                                Satuan Jual (Opsional)
                            </label>
                            <select 
                                id="modal-saleunit"
                                v-model="form.sale_unit_id" 
                                class="w-full rounded-xl border-border-warm dark:border-border-warm bg-surface-warm dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200/50"
                            >
                                <option value="">Sama dengan Satuan Dasar</option>
                                <option v-for="u in units" :key="u.id" :value="u.id">
                                    {{ u.name }} ({{ u.symbol }})
                                </option>
                            </select>
                        </div>

                        <!-- Description -->
                        <div class="col-span-1 md:col-span-2 space-y-1">
                            <label for="modal-desc" class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">
                                Deskripsi Barang
                            </label>
                            <textarea 
                                id="modal-desc"
                                v-model="form.description" 
                                rows="2"
                                class="w-full rounded-xl border-border-warm dark:border-border-warm bg-surface-warm dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200/50"
                            ></textarea>
                        </div>

                        <!-- Status -->
                        <div class="col-span-1 md:col-span-2 space-y-1">
                            <label for="modal-status" class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">
                                Status
                            </label>
                            <select 
                                id="modal-status"
                                v-model="form.status" 
                                class="w-full rounded-xl border-border-warm dark:border-border-warm bg-surface-warm dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200/50"
                            >
                                <option value="active">Aktif</option>
                                <option value="inactive">Nonaktif</option>
                            </select>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex justify-end space-x-2 pt-4 border-t border-border-warm dark:border-border-warm sticky bottom-0 bg-surface-warm dark:bg-surface-warm">
                        <button 
                            type="button" 
                            @click="closeModal"
                            class="px-4 py-2 rounded-xl border border-border-warm dark:border-border-warm hover:bg-slate-50 dark:hover:bg-slate-850 text-slate-500 dark:text-slate-400 font-medium text-sm transition-colors"
                        >
                            Batal
                        </button>
                        <button 
                            type="submit" 
                            :disabled="form.processing"
                            class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white font-semibold text-sm shadow-sm transition-colors"
                        >
                            {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
</template>
