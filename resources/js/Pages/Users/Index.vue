<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PasswordInput from '@/Components/PasswordInput.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineOptions({ layout: AuthenticatedLayout });


interface Warehouse { id: number; name: string; }
interface Role      { id: number; name: string; }
interface UserItem  { id: number; name: string; email: string; warehouse_id: number | null; warehouse: Warehouse | null; roles: Role[]; created_at: string; email_verified_at: string | null; }
interface Pagination<T> { data: T[]; current_page: number; last_page: number; per_page: number; total: number; links: { url: string | null; label: string; active: boolean }[]; }

const props = defineProps<{
    users: Pagination<UserItem>;
    warehouses: Warehouse[];
    allRoles: Role[];
    filters: { search?: string; role?: string };
}>();

// Search
const search   = ref(props.filters.search ?? '');
const roleFilter = ref(props.filters.role ?? '');
const doSearch = () => router.get(route('users.index'), { search: search.value || undefined, role: roleFilter.value || undefined }, { preserveState: true, replace: true });

// Modal state
const showModal  = ref(false);
const showDelete = ref(false);
const editing    = ref<UserItem | null>(null);
const toDelete   = ref<UserItem | null>(null);

const openCreate = () => {
    editing.value = null;
    form.reset();
    showModal.value = true;
};
const openEdit = (u: UserItem) => {
    editing.value = u;
    form.name         = u.name;
    form.email        = u.email;
    form.warehouse_id = u.warehouse_id?.toString() ?? '';
    form.role         = u.roles[0]?.name ?? '';
    form.password     = '';
    form.password_confirmation = '';
    showModal.value = true;
};
const confirmDelete = (u: UserItem) => { toDelete.value = u; showDelete.value = true; };
const deleteUser    = () => { if (!toDelete.value) return; router.delete(route('users.destroy', toDelete.value.id), { onSuccess: () => { showDelete.value = false; toDelete.value = null; } }); };

const form = useForm({
    name: '', email: '', password: '', password_confirmation: '',
    warehouse_id: '', role: '',
});

const submitForm = () => {
    if (editing.value) {
        form.put(route('users.update', editing.value.id), { onSuccess: () => { showModal.value = false; form.reset(); } });
    } else {
        form.post(route('users.store'), { onSuccess: () => { showModal.value = false; form.reset(); } });
    }
};

const roleColor: Record<string, string> = {
    'super-admin': 'bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-350 border border-rose-200 dark:border-rose-900',
    'admin':       'bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-350 border border-blue-200 dark:border-blue-900',
    'staff':       'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-350 border border-emerald-200 dark:border-emerald-900',
};
const getRoleColor = (name: string) => roleColor[name] ?? 'bg-gray-50 text-gray-700 dark:bg-gray-800 dark:text-gray-300 border border-gray-200 dark:border-gray-700';

const avatarColors = ['bg-orange-600', 'bg-emerald-600', 'bg-blue-600', 'bg-cyan-600', 'bg-amber-600'];
const getAvatar = (name: string) => avatarColors[name.charCodeAt(0) % avatarColors.length];
const initials  = (name: string) => name.split(' ').slice(0, 2).map(w => w[0]).join('').toUpperCase();
</script>

<template>
    <Head title="Manajemen User" />
            <div class="flex items-center justify-between w-full">
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Manajemen User</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Kelola akun pengguna dan hak akses operasional</p>
                </div>
                <button @click="openCreate"
                    class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-orange-600 hover:bg-orange-700 rounded-xl transition-colors shadow-sm active:scale-95 duration-100">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Tambah User
                </button>
            </div>

        <div class="py-6 px-4 sm:px-6 lg:px-8 space-y-5">
            <!-- Filter bar -->
            <div class="flex flex-col sm:flex-row gap-3 bg-surface-warm dark:bg-gray-800 p-4 rounded-2xl border border-border-warm shadow-sm">
                <input v-model="search" @keyup.enter="doSearch" type="text" placeholder="Cari nama / email..."
                    class="flex-1 px-4 py-2.5 text-sm rounded-xl border border-gray-250 dark:border-gray-655 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-orange-500 placeholder-gray-400"/>
                
                <select v-model="roleFilter" @change="doSearch"
                    class="sm:w-60 px-3 py-2.5 text-sm rounded-xl border border-gray-250 dark:border-gray-655 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-orange-500">
                    <option value="">Semua Role</option>
                    <option v-for="r in allRoles" :key="r.id" :value="r.name">{{ r.name }}</option>
                </select>

                <button @click="doSearch" class="px-6 py-2.5 text-sm font-semibold text-white bg-orange-600 hover:bg-orange-700 rounded-xl active:scale-95 transition-all duration-100 shrink-0">Cari</button>
            </div>

            <!-- Stats -->
            <div class="text-xs font-semibold text-gray-550 dark:text-gray-400 uppercase tracking-wider">
                Total: <span class="text-orange-600 dark:text-orange-400 font-bold">{{ users.total }}</span> Pengguna
            </div>

            <!-- User cards grid -->
            <div v-if="users.data.length === 0" class="text-center py-20 text-gray-400 dark:text-gray-500 bg-surface-warm dark:bg-gray-850 rounded-2xl border border-border-warm">
                <svg class="w-12 h-12 mx-auto mb-3 opacity-40 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <p class="font-bold text-sm">Belum ada user terdaftar</p>
            </div>
            
            <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <div v-for="u in users.data" :key="u.id"
                    class="bg-surface-warm dark:bg-gray-800 rounded-2xl border border-border-warm p-5 hover:shadow-md hover:border-orange-300 dark:hover:border-orange-900 transition-all duration-200">
                    <div class="flex items-start gap-3.5">
                        <!-- Avatar -->
                        <div :class="['w-12 h-12 rounded-xl flex items-center justify-center text-white font-bold text-sm flex-shrink-0 shadow-inner', getAvatar(u.name)]">
                            {{ initials(u.name) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-bold text-gray-850 dark:text-gray-150 truncate text-sm">{{ u.name }}</p>
                            <p class="text-xs text-gray-500 truncate mt-0.5">{{ u.email }}</p>
                            <div class="flex flex-wrap gap-1.5 mt-2.5">
                                <span v-for="r in u.roles" :key="r.id"
                                    :class="['px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider', getRoleColor(r.name)]">{{ r.name }}</span>
                                <span v-if="!u.roles.length" class="text-xs text-gray-400 italic">Tanpa role</span>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 pt-3.5 border-t border-gray-150/40 dark:border-gray-700/50 space-y-1.5">
                        <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                            <svg class="w-4 h-4 text-gray-400 dark:text-gray-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            <span class="font-medium text-gray-700 dark:text-gray-300">{{ u.warehouse?.name ?? 'Semua Gudang' }}</span>
                        </div>
                        <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                            <svg class="w-4 h-4 text-gray-400 dark:text-gray-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            <span class="font-medium text-gray-700 dark:text-gray-300">{{ u.email_verified_at ? 'Email Terverifikasi' : 'Belum Verifikasi Email' }}</span>
                        </div>
                    </div>
                    <div class="flex gap-2 mt-4">
                        <button @click="openEdit(u)"
                            class="flex-1 py-1.5 text-xs font-semibold text-orange-700 dark:text-orange-300 bg-orange-50 dark:bg-orange-950/20 hover:bg-orange-100 dark:hover:bg-orange-900/30 rounded-xl transition-all duration-100">Ubah</button>
                        <button @click="confirmDelete(u)" :disabled="u.id === $page.props.auth.user.id"
                            class="flex-1 py-1.5 text-xs font-semibold text-rose-600 dark:text-rose-450 bg-rose-50 dark:bg-rose-950/20 hover:bg-rose-100 dark:hover:bg-rose-900/30 rounded-xl transition-all duration-100 disabled:opacity-40 disabled:cursor-not-allowed">Hapus</button>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="users.last_page > 1" class="flex justify-center gap-1 flex-wrap pt-4">
                <button v-for="link in users.links" :key="link.label" :disabled="!link.url"
                    @click="link.url && router.get(link.url)" v-html="link.label"
                    :class="['px-3 py-1.5 text-xs rounded-lg border transition-colors', link.active ? 'bg-orange-600 border-orange-600 text-white' : 'border-gray-250 dark:border-gray-650 text-gray-650 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-40']"/>
            </div>
        </div>

        <!-- Create/Edit Modal -->
        <Teleport to="body">
            <Transition enter-active-class="transition duration-200" enter-from-class="opacity-0" enter-to-class="opacity-100"
                leave-active-class="transition duration-150" leave-from-class="opacity-100" leave-to-class="opacity-0">
                <div v-if="showModal" class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
                    <div class="bg-surface-warm dark:bg-gray-900 rounded-2xl shadow-2xl w-full max-w-lg border border-border-warm">
                        <div class="flex items-center justify-between p-6 border-b border-border-warm">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ editing ? 'Edit Data Pengguna' : 'Tambah User Baru' }}</h3>
                            <button @click="showModal = false; form.reset()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                        <form @submit.prevent="submitForm" class="p-6 space-y-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-750 dark:text-gray-250 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                                <input v-model="form.name" type="text" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-250 dark:border-gray-650 bg-gray-50 dark:bg-gray-800 text-gray-850 dark:text-gray-150 focus:outline-none focus:ring-2 focus:ring-orange-500"/>
                                <p v-if="form.errors.name" class="text-xs text-red-500 mt-1">{{ form.errors.name }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-750 dark:text-gray-250 mb-1">Email <span class="text-red-500">*</span></label>
                                <input v-model="form.email" type="email" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-250 dark:border-gray-650 bg-gray-50 dark:bg-gray-800 text-gray-855 dark:text-gray-145 focus:outline-none focus:ring-2 focus:ring-orange-500"/>
                                <p v-if="form.errors.email" class="text-xs text-red-500 mt-1">{{ form.errors.email }}</p>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-sm font-semibold text-text-primary mb-1">Password {{ editing ? '(kosongkan jika tidak diubah)' : '*' }}</label>
                                    <PasswordInput v-model="form.password" :required="!editing"
                                        class="w-full px-3 py-2 text-sm rounded-xl border border-gray-250 dark:border-gray-650 bg-gray-50 dark:bg-gray-800 text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-orange-500"/>
                                    <p v-if="form.errors.password" class="text-xs text-red-500 mt-1">{{ form.errors.password }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-text-primary mb-1">Konfirmasi Password</label>
                                    <PasswordInput v-model="form.password_confirmation"
                                        class="w-full px-3 py-2 text-sm rounded-xl border border-gray-250 dark:border-gray-650 bg-gray-50 dark:bg-gray-800 text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-orange-500"/>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-750 dark:text-gray-250 mb-1">Role / Peran <span class="text-red-500">*</span></label>
                                    <select v-model="form.role" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-250 dark:border-gray-650 bg-gray-50 dark:bg-gray-800 text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-orange-500">
                                        <option value="">Pilih role</option>
                                        <option v-for="r in allRoles" :key="r.id" :value="r.name">{{ r.name }}</option>
                                    </select>
                                    <p v-if="form.errors.role" class="text-xs text-red-500 mt-1">{{ form.errors.role }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-750 dark:text-gray-250 mb-1">Penugasan Gudang</label>
                                    <select v-model="form.warehouse_id" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-250 dark:border-gray-650 bg-gray-50 dark:bg-gray-800 text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-orange-500">
                                        <option value="">Semua Gudang</option>
                                        <option v-for="w in warehouses" :key="w.id" :value="w.id">{{ w.name }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="flex gap-3 pt-3 border-t border-gray-150/40 dark:border-gray-700/50">
                                <button type="button" @click="showModal = false; form.reset()" class="flex-1 py-2 text-sm font-medium text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-xl transition-colors">Batal</button>
                                <button type="submit" :disabled="form.processing" class="flex-1 py-2 text-sm font-semibold text-white bg-orange-600 hover:bg-orange-700 disabled:opacity-60 rounded-xl transition-colors shadow active:scale-95 duration-100">
                                    {{ form.processing ? 'Menyimpan...' : (editing ? 'Simpan Perubahan' : 'Tambah User') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- Delete Confirm -->
        <Teleport to="body">
            <Transition enter-active-class="transition duration-200" enter-from-class="opacity-0" enter-to-class="opacity-100"
                leave-active-class="transition duration-150" leave-from-class="opacity-100" leave-to-class="opacity-0">
                <div v-if="showDelete" class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
                    <div class="bg-surface-warm dark:bg-gray-900 rounded-2xl shadow-2xl w-full max-w-sm border border-border-warm p-6 text-center">
                        <div class="w-14 h-14 mx-auto mb-4 bg-rose-50 dark:bg-rose-950/20 rounded-2xl flex items-center justify-center">
                            <svg class="w-7 h-7 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </div>
                        <p class="font-bold text-gray-900 dark:text-white text-lg">Hapus User?</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Akun <strong>{{ toDelete?.name }}</strong> akan dihapus permanen.</p>
                        <div class="flex gap-3 mt-5">
                            <button @click="showDelete = false" class="flex-1 py-2 text-sm font-medium text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 rounded-xl transition-colors">Batal</button>
                            <button @click="deleteUser" class="flex-1 py-2 text-sm font-semibold text-white bg-rose-600 hover:bg-rose-700 rounded-xl transition-colors">Ya, Hapus</button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
</template>
