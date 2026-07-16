<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

interface Permission { id: number; name: string; }
interface Role { id: number; name: string; users_count: number; permissions: Permission[]; }

const props = defineProps<{
    roles: Role[];
    permissions: Record<string, Permission[]>;
}>();

const showModal  = ref(false);
const showDelete = ref(false);
const editing    = ref<Role | null>(null);
const toDelete   = ref<Role | null>(null);

const builtIn = ['super-admin', 'admin', 'staff'];

const allPermissions = computed(() => Object.values(props.permissions).flat());

const form = useForm({ name: '', permissions: [] as string[] });

const openCreate = () => {
    editing.value = null;
    form.reset();
    showModal.value = true;
};
const openEdit = (r: Role) => {
    editing.value = r;
    form.name = r.name;
    form.permissions = r.permissions.map(p => p.name);
    showModal.value = true;
};
const confirmDelete = (r: Role) => { toDelete.value = r; showDelete.value = true; };
const deleteRole = () => {
    if (!toDelete.value) return;
    router.delete(route('roles.destroy', toDelete.value.id), { onSuccess: () => { showDelete.value = false; toDelete.value = null; } });
};

const submitForm = () => {
    if (editing.value) {
        form.put(route('roles.update', editing.value.id), { onSuccess: () => { showModal.value = false; form.reset(); } });
    } else {
        form.post(route('roles.store'), { onSuccess: () => { showModal.value = false; form.reset(); } });
    }
};

const togglePermission = (name: string) => {
    const idx = form.permissions.indexOf(name);
    if (idx >= 0) form.permissions.splice(idx, 1);
    else form.permissions.push(name);
};

const moduleLabels: Record<string, string> = {
    dashboard: 'Dashboard',
    barang: 'Data Barang',
    kategori: 'Kategori Barang',
    satuan: 'Satuan Barang',
    supplier: 'Supplier',
    gudang: 'Gudang / Lokasi',
    barang_masuk: 'Barang Masuk',
    barang_keluar: 'Barang Keluar',
    transfer_stok: 'Transfer Stok',
    stock_opname: 'Stock Opname',
    retur_barang: 'Retur Barang',
    laporan: 'Laporan',
    user: 'Manajemen User',
    role: 'Manajemen Role',
    notifikasi: 'Pengaturan Notifikasi',
};

const matrixActions = [
    { key: 'view',   label: 'Lihat' },
    { key: 'create', label: 'Tambah' },
    { key: 'update', label: 'Ubah' },
    { key: 'delete', label: 'Hapus' },
    { key: 'input',  label: 'Input/Konfirm' }
];

const getPermByAction = (perms: Permission[], actionKey: string) => {
    return perms.find(p => p.name.endsWith('.' + actionKey));
};

const getGroupedPermissions = (rolePermissions: Permission[]) => {
    const groups: Record<string, string[]> = {};
    rolePermissions.forEach(p => {
        const parts = p.name.split('.');
        const mod = parts[0];
        const act = parts[1] || '';
        
        let actLabel = act;
        if (act === 'view') actLabel = 'Lihat';
        else if (act === 'create') actLabel = 'Tambah';
        else if (act === 'update') actLabel = 'Ubah';
        else if (act === 'delete') actLabel = 'Hapus';
        else if (act === 'input') actLabel = 'Input';

        if (!groups[mod]) groups[mod] = [];
        groups[mod].push(actLabel);
    });
    return groups;
};

const moduleIcon: Record<string, string> = {
    dashboard: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 002 2h2a2 2 0 002-2',
    products: 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
    barang: 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
    categories: 'M3 7v10a2 2 0 002 2h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2zm5-3h8v3H8V4z',
    kategori: 'M3 7v10a2 2 0 002 2h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2zm5-3h8v3H8V4z',
    warehouses: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
    gudang: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
    units: 'M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z',
    satuan: 'M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z',
    'stock-in': 'M19 13l-7 7-7-7m14-6l-7 7-7-7',
    barang_masuk: 'M19 13l-7 7-7-7m14-6l-7 7-7-7',
    'stock-out': 'M5 11l7-7 7 7m-14 6l7-7 7 7',
    barang_keluar: 'M5 11l7-7 7 7m-14 6l7-7 7 7',
    'stock-transfers': 'M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4',
    transfer_stok: 'M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4',
    'stock-opnames': 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4',
    stock_opname: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4',
    'stock-returns': 'M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 8H17',
    retur_barang: 'M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 8H17',
    reports: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 002 2h2a2 2 0 002-2',
    laporan: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 002 2h2a2 2 0 002-2',
    users: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z',
    user: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z',
    roles: 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
    role: 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
    supplier: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z',
    notifikasi: 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9',
};
</script>

<template>
    <Head title="Manajemen Role" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between w-full">
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Manajemen Role & Izin</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Atur hak akses setiap peran dalam sistem</p>
                </div>
                <button @click="openCreate"
                    class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-orange-600 hover:bg-orange-700 rounded-xl transition-colors shadow-sm active:scale-95 duration-100">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Tambah Role
                </button>
            </div>
        </template>

        <div class="py-6 px-4 sm:px-6 lg:px-8 space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <div v-for="r in roles" :key="r.id"
                    class="bg-surface-warm dark:bg-gray-800 rounded-2xl border border-border-warm p-5 hover:shadow-md transition-all duration-200">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                                <h3 class="font-bold text-gray-850 dark:text-gray-150">{{ r.name }}</h3>
                                <span v-if="builtIn.includes(r.name)" class="text-[10px] uppercase font-bold text-gray-400 bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded-lg tracking-wider">Sistem</span>
                            </div>
                            <p class="text-xs text-gray-404 mt-1.5">{{ r.users_count }} user menggunakan role ini</p>
                        </div>
                        <div class="flex gap-1">
                            <button @click="openEdit(r)" class="p-1.5 text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-lg transition-colors" title="Ubah Izin">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </button>
                            <button @click="confirmDelete(r)" :disabled="builtIn.includes(r.name)" class="p-1.5 text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-900/30 rounded-lg transition-colors disabled:opacity-30 disabled:cursor-not-allowed" title="Hapus Role">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Grouped Permission List in Grid -->
                    <div class="grid grid-cols-2 gap-2 mt-4 pt-3.5 border-t border-gray-150/40 dark:border-gray-700/50">
                        <div v-for="(acts, mod) in getGroupedPermissions(r.permissions)" :key="mod" 
                            class="flex flex-col gap-1 border border-gray-150/40 dark:border-gray-700/30 p-2.5 rounded-xl bg-gray-50/50 dark:bg-gray-900/30">
                            <span class="font-bold text-gray-900 dark:text-gray-100 text-xs capitalize truncate">
                                {{ moduleLabels[mod] ?? mod.replace('_', ' ') }}
                            </span>
                            <div class="flex flex-wrap gap-1">
                                <span v-for="act in acts" :key="act" 
                                    class="px-1.5 py-0.5 rounded bg-orange-50 dark:bg-orange-950/20 text-orange-700 dark:text-orange-400 font-bold text-[9px] uppercase tracking-wider">
                                    {{ act }}
                                </span>
                            </div>
                        </div>
                        <div v-if="r.permissions.length === 0" class="col-span-2 text-xs text-gray-450 italic text-center py-2">Tidak ada izin akses</div>
                    </div>
                </div>

                <!-- Empty -->
                <div v-if="roles.length === 0" class="col-span-3 text-center py-16 text-gray-400 dark:text-gray-500">
                    <p class="font-medium">Belum ada role terdaftar</p>
                </div>
            </div>
        </div>

        <!-- Create/Edit Modal -->
        <Teleport to="body">
            <Transition enter-active-class="transition duration-200" enter-from-class="opacity-0" enter-to-class="opacity-100"
                leave-active-class="transition duration-150" leave-from-class="opacity-100" leave-to-class="opacity-0">
                <div v-if="showModal" class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
                    <div class="bg-surface-warm dark:bg-gray-900 rounded-2xl shadow-2xl w-full max-w-4xl border border-border-warm flex flex-col max-h-[90vh]">
                        <div class="flex items-center justify-between p-6 border-b border-border-warm flex-shrink-0">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ editing ? 'Edit Peran & Hak Akses' : 'Tambah Role Baru' }}</h3>
                            <button @click="showModal = false; form.reset()" class="text-gray-400 hover:text-gray-600 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                        <div class="flex-1 overflow-y-auto p-6 space-y-5">
                            <div>
                                <label class="block text-sm font-semibold text-gray-750 dark:text-gray-250 mb-1">Nama Role <span class="text-red-500">*</span></label>
                                <input v-model="form.name" type="text" required :readonly="!!(editing && builtIn.includes(editing.name))"
                                    class="w-full px-3 py-2 text-sm rounded-xl border border-gray-250 dark:border-gray-650 bg-gray-50 dark:bg-gray-800 text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-orange-500 read-only:bg-gray-100 dark:read-only:bg-gray-800 read-only:text-gray-400"/>
                                <p v-if="form.errors.name" class="text-xs text-red-500 mt-1">{{ form.errors.name }}</p>
                            </div>
                            
                            <!-- Permissions grouped in a gorgeous table matrix -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-750 dark:text-gray-250 mb-3">Daftar Izin Akses (Matrix)</label>
                                <div class="overflow-x-auto border border-border-warm rounded-2xl bg-white dark:bg-gray-800 shadow-sm">
                                    <table class="w-full text-left border-collapse">
                                        <thead>
                                            <tr class="bg-gray-50 dark:bg-gray-900 border-b border-border-warm">
                                                <th class="p-3.5 text-xs font-bold text-gray-550 dark:text-gray-400 uppercase tracking-wider">Modul</th>
                                                <th v-for="act in matrixActions" :key="act.key" class="p-3.5 text-xs font-bold text-center text-gray-550 dark:text-gray-400 uppercase tracking-wider">{{ act.label }}</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-150/40 dark:divide-gray-750/30">
                                            <tr v-for="(perms, module) in permissions" :key="module" class="hover:bg-gray-50/40 dark:hover:bg-gray-750/10 transition-colors">
                                                <td class="p-3.5 flex items-center gap-2">
                                                    <svg class="w-4.5 h-4.5 text-orange-600 dark:text-orange-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="moduleIcon[module] ?? 'M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4'" />
                                                    </svg>
                                                    <span class="text-xs font-bold text-gray-750 dark:text-gray-250 capitalize">{{ moduleLabels[module] ?? module.replace('_', ' ') }}</span>
                                                </td>
                                                <td v-for="act in matrixActions" :key="act.key" class="p-3.5 text-center">
                                                    <template v-if="getPermByAction(perms, act.key)">
                                                        <label class="inline-flex items-center justify-center cursor-pointer">
                                                            <input type="checkbox" :checked="form.permissions.includes(getPermByAction(perms, act.key).name)" @change="togglePermission(getPermByAction(perms, act.key).name)"
                                                                class="w-4.5 h-4.5 rounded text-orange-600 border-gray-300 dark:border-gray-600 focus:ring-orange-500 cursor-pointer transition-all duration-100"/>
                                                        </label>
                                                    </template>
                                                    <template v-else>
                                                        <span class="text-gray-300 dark:text-gray-600 text-xs font-semibold">—</span>
                                                    </template>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="flex gap-3 p-6 border-t border-border-warm flex-shrink-0">
                            <button type="button" @click="showModal = false; form.reset()" class="flex-1 py-2 text-sm font-medium text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 rounded-xl transition-colors">Batal</button>
                            <button @click="submitForm" :disabled="form.processing" class="flex-1 py-2 text-sm font-semibold text-white bg-orange-600 hover:bg-orange-700 disabled:opacity-60 rounded-xl transition-colors shadow active:scale-95 duration-100">
                                {{ form.processing ? 'Menyimpan...' : (editing ? 'Simpan Perubahan' : 'Buat Role') }}
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- Delete Confirm -->
        <Teleport to="body">
            <Transition enter-active-class="transition duration-200" enter-from-class="opacity-0" enter-to-class="opacity-100">
                <div v-if="showDelete" class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
                    <div class="bg-surface-warm dark:bg-gray-900 rounded-2xl shadow-2xl w-full max-w-sm border border-border-warm p-6 text-center">
                        <div class="w-14 h-14 mx-auto mb-4 bg-rose-50 dark:bg-rose-950/20 rounded-2xl flex items-center justify-center">
                            <svg class="w-7 h-7 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.962-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                        </div>
                        <p class="font-bold text-gray-900 dark:text-white text-lg">Hapus Role?</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Role <strong>{{ toDelete?.name }}</strong> akan dihapus secara permanen.</p>
                        <div class="flex gap-3 mt-5">
                            <button @click="showDelete = false" class="flex-1 py-2 text-sm font-medium text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 rounded-xl transition-colors">Batal</button>
                            <button @click="deleteRole" class="flex-1 py-2 text-sm font-semibold text-white bg-rose-600 hover:bg-rose-700 rounded-xl transition-colors">Ya, Hapus</button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </AuthenticatedLayout>
</template>
