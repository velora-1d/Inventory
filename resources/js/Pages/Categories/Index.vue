<script setup lang="ts">
import { showConfirm } from '@/confirm';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

defineOptions({ layout: AuthenticatedLayout });


interface Category {
    id: number;
    name: string;
    description: string | null;
    status: 'active' | 'inactive';
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface PaginatedCategories {
    data: Category[];
    links: PaginationLink[];
    current_page: number;
    last_page: number;
    total: number;
}

const props = defineProps<{
    categories: PaginatedCategories;
    filters: {
        search?: string;
        status?: string;
    };
}>();

// Search & Filter
const searchQuery = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || '');

const applyFilters = () => {
    router.get(route('categories.index'), {
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
const selectedCategoryId = ref<number | null>(null);

const form = useForm({
    name: '',
    description: '',
    status: 'active' as 'active' | 'inactive',
});

const openCreateModal = () => {
    modalMode.value = 'create';
    form.reset();
    form.clearErrors();
    isModalOpen.value = true;
};

const openEditModal = (category: Category) => {
    modalMode.value = 'edit';
    selectedCategoryId.value = category.id;
    form.name = category.name;
    form.description = category.description || '';
    form.status = category.status;
    form.clearErrors();
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    selectedCategoryId.value = null;
    form.reset();
};

const submitForm = () => {
    if (modalMode.value === 'create') {
        form.post(route('categories.store'), {
            onSuccess: () => closeModal(),
        });
    } else if (modalMode.value === 'edit' && selectedCategoryId.value) {
        form.put(route('categories.update', selectedCategoryId.value), {
            onSuccess: () => closeModal(),
        });
    }
};

const deleteCategory = async (id: number) => {
    if (await showConfirm('Apakah Anda yakin ingin menghapus kategori ini?')) {
        router.delete(route('categories.destroy', id), {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <Head title="Kategori Barang" />
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-4 sm:space-y-0">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-slate-800 dark:text-slate-100">
                        Kategori Barang
                    </h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                        Kelola klasifikasi atau pengelompokan barang inventory.
                    </p>
                </div>

                <button 
                    @click="openCreateModal"
                    class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm shadow-sm transition-colors duration-200"
                >
                    <svg class="h-5 w-5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Tambah Kategori
                </button>
            </div>

        <!-- Filters Section -->
        <div class="bg-surface-warm dark:bg-surface-warm p-4 rounded-2xl border border-border-warm dark:border-border-warm shadow-sm mb-6 flex flex-col md:flex-row md:items-center gap-4">
            <div class="flex-1">
                <input 
                    type="text" 
                    v-model="searchQuery" 
                    placeholder="Cari kategori..." 
                    @keyup.enter="applyFilters"
                    class="w-full rounded-xl border-border-warm dark:border-border-warm bg-surface-warm dark:bg-slate-950 text-slate-700 dark:text-slate-300 text-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200/50 shadow-sm"
                />
            </div>
            <div class="w-full md:w-48">
                <select 
                    v-model="statusFilter" 
                    @change="applyFilters"
                    class="w-full rounded-xl border-border-warm dark:border-border-warm bg-surface-warm dark:bg-slate-950 text-slate-700 dark:text-slate-300 text-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200/50 shadow-sm"
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
                    class="px-4 py-2 rounded-xl border border-border-warm dark:border-border-warm hover:bg-slate-50 dark:hover:bg-slate-850 text-slate-500 dark:text-slate-450 font-medium text-sm transition-colors"
                >
                    Reset
                </button>
            </div>
        </div>

        <!-- Categories Table Card -->
        <div class="bg-surface-warm dark:bg-surface-warm rounded-2xl border border-border-warm dark:border-border-warm shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/50 dark:bg-surface-warm/50 text-slate-400 dark:text-slate-500 text-xs font-semibold uppercase">
                            <th class="p-4 w-16">No</th>
                            <th class="p-4">Nama Kategori</th>
                            <th class="p-4">Deskripsi</th>
                            <th class="p-4 w-32">Status</th>
                            <th class="p-4 w-32 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-150 dark:divide-slate-800 text-sm">
                        <tr v-for="(category, idx) in categories.data" :key="category.id" class="text-slate-700 dark:text-slate-300 hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors">
                            <td class="p-4 text-slate-400">
                                {{ (categories.current_page - 1) * 10 + idx + 1 }}
                            </td>
                            <td class="p-4 font-semibold text-slate-800 dark:text-slate-100">
                                {{ category.name }}
                            </td>
                            <td class="p-4">
                                {{ category.description || '-' }}
                            </td>
                            <td class="p-4">
                                <span 
                                    :class="[
                                        category.status === 'active' 
                                            ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-450' 
                                            : 'bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-450',
                                        'px-2 py-0.5 rounded-full text-xs font-semibold tracking-wide border border-transparent'
                                    ]"
                                >
                                    {{ category.status === 'active' ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="p-4 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button 
                                        @click="openEditModal(category)"
                                        class="p-1.5 text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-lg transition-colors"
                                        title="Ubah"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>
                                    <button 
                                        @click="deleteCategory(category.id)"
                                        class="p-1.5 text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-900/30 rounded-lg transition-colors"
                                        title="Hapus"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="categories.data.length === 0">
                            <td colspan="5" class="p-8 text-center text-slate-450 dark:text-slate-500">
                                Data Kategori tidak ditemukan.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Inline Pagination -->
            <div v-if="categories.last_page > 1" class="p-4 border-t border-border-warm dark:border-border-warm flex items-center justify-between">
                <span class="text-xs text-slate-500 dark:text-slate-400">
                    Menampilkan {{ categories.data.length }} dari {{ categories.total }} data
                </span>
                <div class="flex space-x-1">
                    <Link
                        v-for="link in categories.links"
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
            
            <div class="bg-surface-warm dark:bg-surface-warm border border-border-warm dark:border-border-warm rounded-2xl max-w-md w-full shadow-2xl overflow-hidden relative z-10">
                <div class="p-6 border-b border-border-warm dark:border-border-warm flex items-center justify-between">
                    <h3 class="font-bold text-lg text-slate-800 dark:text-slate-200">
                        {{ modalMode === 'create' ? 'Tambah Kategori' : 'Edit Kategori' }}
                    </h3>
                    <button @click="closeModal" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="submitForm" class="p-6 space-y-4">
                    <!-- Name -->
                    <div class="space-y-1">
                        <label for="modal-name" class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">
                            Nama Kategori
                        </label>
                        <input 
                            id="modal-name"
                            type="text" 
                            v-model="form.name" 
                            required
                            class="w-full rounded-xl border-border-warm dark:border-border-warm bg-surface-warm dark:bg-slate-950 text-slate-700 dark:text-slate-300 text-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200/50"
                        />
                        <span v-if="form.errors.name" class="text-xs text-rose-500 mt-1 block">
                            {{ form.errors.name }}
                        </span>
                    </div>

                    <!-- Description -->
                    <div class="space-y-1">
                        <label for="modal-desc" class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">
                            Deskripsi
                        </label>
                        <textarea 
                            id="modal-desc"
                            v-model="form.description" 
                            rows="3"
                            class="w-full rounded-xl border-border-warm dark:border-border-warm bg-surface-warm dark:bg-slate-950 text-slate-700 dark:text-slate-300 text-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200/50"
                        ></textarea>
                        <span v-if="form.errors.description" class="text-xs text-rose-500 mt-1 block">
                            {{ form.errors.description }}
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
                            class="w-full rounded-xl border-border-warm dark:border-border-warm bg-surface-warm dark:bg-slate-950 text-slate-700 dark:text-slate-300 text-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200/50"
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
</template>
