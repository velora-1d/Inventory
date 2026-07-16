<script setup lang="ts">
import { showConfirm } from '@/confirm';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

interface Supplier {
    id: number;
    code: string;
    name: string;
    address: string | null;
    phone: string | null;
    email: string | null;
    contact_person: string | null;
    status: 'active' | 'inactive';
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface PaginatedSuppliers {
    data: Supplier[];
    links: PaginationLink[];
    current_page: number;
    last_page: number;
    total: number;
}

const props = defineProps<{
    suppliers: PaginatedSuppliers;
    filters: {
        search?: string;
        status?: string;
    };
}>();

// Search & Filter
const searchQuery = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || '');

const applyFilters = () => {
    router.get(route('suppliers.index'), {
        search: searchQuery.value,
        status: statusFilter.value
    }, {
        preserveState: true,
        preserveScroll: true
    });
};

const resetFilters = () => {
    searchQuery.value = '';
    statusFilter.value = '';
    applyFilters();
};

// Modal State
const isModalOpen = ref(false);
const modalMode = ref<'create' | 'edit'>('create');
const selectedSupplierId = ref<number | null>(null);

const form = useForm({
    code: '',
    name: '',
    address: '',
    phone: '',
    email: '',
    contact_person: '',
    status: 'active' as 'active' | 'inactive',
});

const openCreateModal = () => {
    modalMode.value = 'create';
    form.reset();
    form.clearErrors();
    
    // Auto-generate code if empty
    form.code = 'SPL-' + Math.floor(1000 + Math.random() * 9000);
    isModalOpen.value = true;
};

const openEditModal = (supplier: Supplier) => {
    modalMode.value = 'edit';
    selectedSupplierId.value = supplier.id;
    form.code = supplier.code;
    form.name = supplier.name;
    form.address = supplier.address || '';
    form.phone = supplier.phone || '';
    form.email = supplier.email || '';
    form.contact_person = supplier.contact_person || '';
    form.status = supplier.status;
    form.clearErrors();
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    selectedSupplierId.value = null;
    form.reset();
};

const submitForm = () => {
    if (modalMode.value === 'create') {
        form.post(route('suppliers.store'), {
            onSuccess: () => closeModal(),
        });
    } else if (modalMode.value === 'edit' && selectedSupplierId.value) {
        form.put(route('suppliers.update', selectedSupplierId.value), {
            onSuccess: () => closeModal(),
        });
    }
};

const deleteSupplier = async (id: number) => {
    if (await showConfirm('Apakah Anda yakin ingin menghapus supplier ini?')) {
        router.delete(route('suppliers.destroy', id), {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <Head title="Supplier" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-4 sm:space-y-0">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-slate-800 dark:text-slate-100">
                        Supplier
                    </h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                        Kelola data mitra penyuplai produk inventory.
                    </p>
                </div>

                <button 
                    @click="openCreateModal"
                    class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm shadow-sm transition-colors duration-200"
                >
                    <svg class="h-5 w-5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Tambah Supplier
                </button>
            </div>
        </template>

        <!-- Filters Section -->
        <div class="bg-surface-warm dark:bg-surface-warm p-4 rounded-2xl border border-border-warm dark:border-border-warm shadow-sm mb-6 flex flex-col md:flex-row md:items-center gap-4">
            <div class="flex-1">
                <input 
                    type="text" 
                    v-model="searchQuery" 
                    placeholder="Cari kode, nama, email, atau penanggung jawab..." 
                    @keyup.enter="applyFilters"
                    class="w-full rounded-xl border-border-warm dark:border-border-warm bg-surface-warm dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200/50 shadow-sm"
                />
            </div>
            <div class="w-full md:w-48">
                <select 
                    v-model="statusFilter" 
                    @change="applyFilters"
                    class="w-full rounded-xl border-border-warm dark:border-border-warm bg-surface-warm dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200/50 shadow-sm"
                >
                    <option value="">Semua Status</option>
                    <option value="active">Aktif</option>
                    <option value="inactive">Nonaktif</option>
                </select>
            </div>
            <div class="flex gap-2">
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

        <!-- Suppliers Table Card -->
        <div class="bg-surface-warm dark:bg-surface-warm rounded-2xl border border-border-warm dark:border-border-warm shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/50 dark:bg-surface-warm/50 text-slate-400 dark:text-slate-500 text-xs font-semibold uppercase">
                            <th class="p-4 w-16">No</th>
                            <th class="p-4 w-32">Kode</th>
                            <th class="p-4">Nama Supplier</th>
                            <th class="p-4">Kontak Person</th>
                            <th class="p-4">Telepon / Email</th>
                            <th class="p-4 w-32">Status</th>
                            <th class="p-4 w-32 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-150 dark:divide-slate-800 text-sm">
                        <tr v-for="(supplier, idx) in suppliers.data" :key="supplier.id" class="text-slate-700 dark:text-slate-300 hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors">
                            <td class="p-4 text-slate-400">
                                {{ (suppliers.current_page - 1) * 10 + idx + 1 }}
                            </td>
                            <td class="p-4 font-mono font-semibold text-slate-500 dark:text-slate-400">
                                {{ supplier.code }}
                            </td>
                            <td class="p-4 font-semibold text-slate-800 dark:text-slate-100">
                                <div>{{ supplier.name }}</div>
                                <div class="text-xs text-slate-450 mt-0.5 line-clamp-1 max-w-xs">{{ supplier.address || '-' }}</div>
                            </td>
                            <td class="p-4">
                                {{ supplier.contact_person || '-' }}
                            </td>
                            <td class="p-4">
                                <div>{{ supplier.phone || '-' }}</div>
                                <div class="text-xs text-slate-450 mt-0.5">{{ supplier.email || '-' }}</div>
                            </td>
                            <td class="p-4">
                                <span 
                                    :class="[
                                        supplier.status === 'active' 
                                            ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-450' 
                                            : 'bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-450',
                                        'px-2 py-0.5 rounded-full text-xs font-semibold tracking-wide border border-transparent'
                                    ]"
                                >
                                    {{ supplier.status === 'active' ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="p-4 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button 
                                        @click="openEditModal(supplier)"
                                        class="p-1.5 text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-lg transition-colors"
                                        title="Ubah"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>
                                    <button 
                                        @click="deleteSupplier(supplier.id)"
                                        class="p-1.5 text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-900/30 rounded-lg transition-colors"
                                        title="Hapus"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="suppliers.data.length === 0">
                            <td colspan="7" class="p-8 text-center text-slate-450 dark:text-slate-500">
                                Data Supplier tidak ditemukan.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Inline Pagination -->
            <div v-if="suppliers.last_page > 1" class="p-4 border-t border-border-warm dark:border-border-warm flex items-center justify-between">
                <span class="text-xs text-slate-500 dark:text-slate-400">
                    Menampilkan {{ suppliers.data.length }} dari {{ suppliers.total }} data
                </span>
                <div class="flex space-x-1">
                    <Link
                        v-for="link in suppliers.links"
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
            
            <div class="bg-surface-warm dark:bg-surface-warm border border-border-warm dark:border-border-warm rounded-2xl max-w-lg w-full shadow-2xl overflow-hidden relative z-10">
                <div class="p-6 border-b border-border-warm dark:border-border-warm flex items-center justify-between">
                    <h3 class="font-bold text-lg text-slate-800 dark:text-slate-200">
                        {{ modalMode === 'create' ? 'Tambah Supplier' : 'Edit Supplier' }}
                    </h3>
                    <button @click="closeModal" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="submitForm" class="p-6 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <!-- Code -->
                        <div class="space-y-1">
                            <label for="modal-code" class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">
                                Kode Supplier
                            </label>
                            <input 
                                id="modal-code"
                                type="text" 
                                v-model="form.code" 
                                required
                                class="w-full rounded-xl border-border-warm dark:border-border-warm bg-surface-warm dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200/50"
                            />
                            <span v-if="form.errors.code" class="text-xs text-rose-500 mt-1 block">
                                {{ form.errors.code }}
                            </span>
                        </div>

                        <!-- Contact Person -->
                        <div class="space-y-1">
                            <label for="modal-cp" class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">
                                Contact Person
                            </label>
                            <input 
                                id="modal-cp"
                                type="text" 
                                v-model="form.contact_person" 
                                placeholder="Nama CP"
                                class="w-full rounded-xl border-border-warm dark:border-border-warm bg-surface-warm dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200/50"
                            />
                            <span v-if="form.errors.contact_person" class="text-xs text-rose-500 mt-1 block">
                                {{ form.errors.contact_person }}
                            </span>
                        </div>
                    </div>

                    <!-- Name -->
                    <div class="space-y-1">
                        <label for="modal-name" class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">
                            Nama Supplier / Perusahaan
                        </label>
                        <input 
                            id="modal-name"
                            type="text" 
                            v-model="form.name" 
                            required
                            placeholder="Contoh: PT. Supplier Abadi"
                            class="w-full rounded-xl border-border-warm dark:border-border-warm bg-surface-warm dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200/50"
                        />
                        <span v-if="form.errors.name" class="text-xs text-rose-500 mt-1 block">
                            {{ form.errors.name }}
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <!-- Phone -->
                        <div class="space-y-1">
                            <label for="modal-phone" class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">
                                Nomor Telepon
                            </label>
                            <input 
                                id="modal-phone"
                                type="text" 
                                v-model="form.phone" 
                                placeholder="Contoh: 021-xxxxxxxx"
                                class="w-full rounded-xl border-border-warm dark:border-border-warm bg-surface-warm dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200/50"
                            />
                            <span v-if="form.errors.phone" class="text-xs text-rose-500 mt-1 block">
                                {{ form.errors.phone }}
                            </span>
                        </div>

                        <!-- Email -->
                        <div class="space-y-1">
                            <label for="modal-email" class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">
                                Email
                            </label>
                            <input 
                                id="modal-email"
                                type="email" 
                                v-model="form.email" 
                                placeholder="email@perusahaan.com"
                                class="w-full rounded-xl border-border-warm dark:border-border-warm bg-surface-warm dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200/50"
                            />
                            <span v-if="form.errors.email" class="text-xs text-rose-500 mt-1 block">
                                {{ form.errors.email }}
                            </span>
                        </div>
                    </div>

                    <!-- Address -->
                    <div class="space-y-1">
                        <label for="modal-address" class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">
                            Alamat Kantor
                        </label>
                        <textarea 
                            id="modal-address"
                            v-model="form.address" 
                            rows="2"
                            class="w-full rounded-xl border-border-warm dark:border-border-warm bg-surface-warm dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200/50"
                        ></textarea>
                        <span v-if="form.errors.address" class="text-xs text-rose-500 mt-1 block">
                            {{ form.errors.address }}
                        </span>
                    </div>

                    <!-- Status -->
                    <div class="space-y-1">
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
                        <span v-if="form.errors.status" class="text-xs text-rose-500 mt-1 block">
                            {{ form.errors.status }}
                        </span>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex justify-end space-x-2 pt-4 border-t border-border-warm dark:border-border-warm">
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
    </AuthenticatedLayout>
</template>
