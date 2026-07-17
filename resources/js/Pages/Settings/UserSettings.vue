<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PasswordInput from '@/Components/PasswordInput.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineOptions({ layout: AuthenticatedLayout });


const props = defineProps<{
    user: { id: number; name: string; email: string; roles: { name: string }[] };
    isAdmin: boolean;
    hasPin: boolean;
    switchedRole?: string;
}>();

// Profile form
const profileForm = useForm({ name: props.user.name, email: props.user.email });
const submitProfile = () => profileForm.patch(route('settings.user.profile'));

// Password form
const passwordForm = useForm({ current_password: '', password: '', password_confirmation: '' });
const submitPassword = () => passwordForm.patch(route('settings.user.password'), {
    onSuccess: () => passwordForm.reset(),
});

// PIN form (admin only)
const pinForm = useForm({ password: '', pin: '', pin_confirmation: '' });
const pinChars = ref<string[]>(['', '', '', '', '', '']);
const pinRefs = ref<HTMLInputElement[]>([]);
const pinConfirmChars = ref<string[]>(['', '', '', '', '', '']);
const pinConfirmRefs = ref<HTMLInputElement[]>([]);

const handlePinInput = (idx: number, type: 'pin' | 'confirm') => {
    const chars = type === 'pin' ? pinChars : pinConfirmChars;
    const refs = type === 'pin' ? pinRefs : pinConfirmRefs;
    const v = chars.value[idx];
    if (/^[0-9]$/.test(v) && idx < 5) refs.value[idx + 1]?.focus();
    if (type === 'pin') pinForm.pin = pinChars.value.join('');
    else pinForm.pin_confirmation = pinConfirmChars.value.join('');
};
const handlePinKeydown = (e: KeyboardEvent, idx: number, type: 'pin' | 'confirm') => {
    const chars = type === 'pin' ? pinChars : pinConfirmChars;
    const refs = type === 'pin' ? pinRefs : pinConfirmRefs;
    if (e.key === 'Backspace' && !chars.value[idx] && idx > 0) refs.value[idx - 1]?.focus();
};
const submitPin = () => pinForm.patch(route('settings.user.pin'), {
    onSuccess: () => { pinForm.reset(); pinChars.value = ['','','','','','']; pinConfirmChars.value = ['','','','','','']; },
});

// Role switch form
const switchForm = useForm({ role: '', pin: '' });
const restoreForm = useForm({ pin: '' });
const switchPinChars = ref<string[]>(['', '', '', '', '', '']);
const switchPinRefs = ref<HTMLInputElement[]>([]);
const restorePinChars = ref<string[]>(['', '', '', '', '', '']);
const restorePinRefs = ref<HTMLInputElement[]>([]);
const handleSwitchPinInput = (idx: number, type: 'switch' | 'restore') => {
    const chars = type === 'switch' ? switchPinChars : restorePinChars;
    const refs = type === 'switch' ? switchPinRefs : restorePinRefs;
    if (/^[0-9]$/.test(chars.value[idx]) && idx < 5) refs.value[idx + 1]?.focus();
    if (type === 'switch') switchForm.pin = switchPinChars.value.join('');
    else restoreForm.pin = restorePinChars.value.join('');
};
const handleSwitchPinKeydown = (e: KeyboardEvent, idx: number, type: 'switch' | 'restore') => {
    const chars = type === 'switch' ? switchPinChars : restorePinChars;
    const refs = type === 'switch' ? switchPinRefs : restorePinRefs;
    if (e.key === 'Backspace' && !chars.value[idx] && idx > 0) refs.value[idx - 1]?.focus();
};
const submitSwitch = () => switchForm.post(route('role-switch.switch'), {
    onSuccess: () => { switchForm.reset(); switchPinChars.value = ['','','','','','']; },
});
const submitRestore = () => restoreForm.post(route('role-switch.restore'), {
    onSuccess: () => { restoreForm.reset(); restorePinChars.value = ['','','','','','']; },
});

import axios from 'axios';

// Connection test state
const testingDb = ref(false);
const testingS3 = ref(false);
const testResult = ref<{ type: 'success' | 'error'; message: string } | null>(null);

const testDbConnection = async () => {
    testingDb.value = true;
    testResult.value = null;
    try {
        const response = await axios.post(route('settings.user.test-db'));
        testResult.value = { type: 'success', message: response.data.message };
    } catch (err: any) {
        testResult.value = { type: 'error', message: err.response?.data?.message || 'Gagal mengetes koneksi database.' };
    } finally {
        testingDb.value = false;
    }
};

const testS3Connection = async () => {
    testingS3.value = true;
    testResult.value = null;
    try {
        const response = await axios.post(route('settings.user.test-s3'));
        testResult.value = { type: 'success', message: response.data.message };
    } catch (err: any) {
        testResult.value = { type: 'error', message: err.response?.data?.message || 'Gagal mengetes koneksi S3/RustFS.' };
    } finally {
        testingS3.value = false;
    }
};

const roles = ['Admin', 'Staff Gudang', 'Kasir', 'Manager'];
</script>

<template>
    <Head title="Pengaturan Akun" />
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">Pengaturan Akun</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Kelola profil, password, dan keamanan akun Anda</p>
            </div>

        <div class="py-6 space-y-6">

            <!-- Role Switch Banner (if currently switched) -->
            <div v-if="switchedRole" class="mx-4 sm:mx-6 lg:mx-8 bg-amber-50 dark:bg-amber-950/20 border border-amber-300 dark:border-amber-700 rounded-2xl p-4 flex items-center gap-3">
                <svg class="w-5 h-5 text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <div class="flex-1">
                    <p class="font-semibold text-amber-800 dark:text-amber-300 text-sm">Mode Tampilan Role Aktif</p>
                    <p class="text-xs text-amber-600 dark:text-amber-400">Anda sedang melihat tampilan sebagai <strong>{{ switchedRole }}</strong>. Kembali ke Admin untuk keluar.</p>
                </div>
            </div>

            <!-- Connection Testing Panel (Database & Cloud Storage) -->
            <div class="mx-4 sm:mx-6 lg:mx-8 bg-surface-warm dark:bg-gray-800 rounded-2xl border border-border-warm shadow-sm p-6">
                <h3 class="font-bold text-text-primary text-base mb-2 pb-3 border-b border-border-warm flex items-center gap-2">
                    <svg class="w-4 h-4 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    Utilitas Tes Koneksi Sistem
                </h3>
                <p class="text-xs text-gray-400 mb-4">Uji koneksi secara langsung dari server aplikasi ke database remote TiDB Cloud dan Cloud Storage S3 / RustFS.</p>

                <!-- Connection Results Toast Notification -->
                <Transition enter-active-class="transition duration-300" enter-from-class="opacity-0 -translate-y-2" enter-to-class="opacity-100 translate-y-0"
                    leave-active-class="transition duration-200" leave-from-class="opacity-100" leave-to-class="opacity-0">
                    <div v-if="testResult" :class="[
                        'flex items-start gap-3 rounded-xl px-4 py-3 text-sm font-medium mb-4 border',
                        testResult.type === 'success' 
                            ? 'bg-emerald-50 dark:bg-emerald-950/20 border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-300' 
                            : 'bg-red-50 dark:bg-red-950/20 border-red-200 dark:border-red-800 text-red-700 dark:text-red-300'
                    ]">
                        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path v-if="testResult.type === 'success'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div class="flex-1 break-all">
                            {{ testResult.message }}
                        </div>
                        <button @click="testResult = null" class="text-xs opacity-60 hover:opacity-100 font-bold ml-1">Tutup</button>
                    </div>
                </Transition>

                <div class="flex flex-col sm:flex-row gap-3">
                    <button @click="testDbConnection" :disabled="testingDb || testingS3"
                        class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-3 text-sm font-semibold rounded-xl text-white bg-gray-900 hover:bg-gray-800 dark:bg-gray-100 dark:text-gray-900 dark:hover:bg-gray-200 transition-all active:scale-95 disabled:opacity-50">
                        <svg v-if="testingDb" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/></svg>
                        {{ testingDb ? 'Mengetes TiDB...' : 'Tes Koneksi Database TiDB' }}
                    </button>

                    <button @click="testS3Connection" :disabled="testingDb || testingS3"
                        class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-3 text-sm font-semibold rounded-xl text-white bg-orange-600 hover:bg-orange-700 transition-all active:scale-95 disabled:opacity-50">
                        <svg v-if="testingS3" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"/></svg>
                        {{ testingS3 ? 'Mengetes RustFS...' : 'Tes Koneksi Cloud Storage S3' }}
                    </button>
                </div>
            </div>

            <!-- Profile Information -->
            <div class="mx-4 sm:mx-6 lg:mx-8 bg-surface-warm dark:bg-gray-800 rounded-2xl border border-border-warm shadow-sm p-6">
                <h3 class="font-bold text-text-primary text-base mb-4 pb-3 border-b border-border-warm flex items-center gap-2">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    Informasi Profil
                </h3>
                <form @submit.prevent="submitProfile" class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-text-primary mb-1.5">Nama Lengkap</label>
                        <input v-model="profileForm.name" type="text" class="w-full px-3.5 py-2.5 rounded-xl border border-border-warm bg-gray-50 dark:bg-gray-700 text-text-primary focus:outline-none focus:ring-2 focus:ring-orange-400 text-sm" />
                        <p v-if="profileForm.errors.name" class="text-xs text-red-500 mt-1">{{ profileForm.errors.name }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-text-primary mb-1.5">Alamat Email</label>
                        <input v-model="profileForm.email" type="email" class="w-full px-3.5 py-2.5 rounded-xl border border-border-warm bg-gray-50 dark:bg-gray-700 text-text-primary focus:outline-none focus:ring-2 focus:ring-orange-400 text-sm" />
                        <p v-if="profileForm.errors.email" class="text-xs text-red-500 mt-1">{{ profileForm.errors.email }}</p>
                    </div>
                    <div class="flex justify-end pt-2">
                        <button type="submit" :disabled="profileForm.processing" class="px-5 py-2 bg-gray-900 dark:bg-gray-100 text-white dark:text-gray-900 font-semibold rounded-xl text-sm hover:opacity-90 transition-all disabled:opacity-50 active:scale-95">
                            Simpan Profil
                        </button>
                    </div>
                </form>
            </div>

            <!-- Update Password -->
            <div class="mx-4 sm:mx-6 lg:mx-8 bg-surface-warm dark:bg-gray-800 rounded-2xl border border-border-warm shadow-sm p-6">
                <h3 class="font-bold text-text-primary text-base mb-4 pb-3 border-b border-border-warm flex items-center gap-2">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    Ganti Password
                </h3>
                <form @submit.prevent="submitPassword" class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-text-primary mb-1.5">Password Saat Ini</label>
                        <PasswordInput v-model="passwordForm.current_password"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-border-warm bg-gray-50 dark:bg-gray-700 text-text-primary focus:outline-none focus:ring-2 focus:ring-orange-400 text-sm" />
                        <p v-if="passwordForm.errors.current_password" class="text-xs text-red-500 mt-1">{{ passwordForm.errors.current_password }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-text-primary mb-1.5">Password Baru</label>
                        <PasswordInput v-model="passwordForm.password"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-border-warm bg-gray-50 dark:bg-gray-700 text-text-primary focus:outline-none focus:ring-2 focus:ring-orange-400 text-sm" />
                        <p v-if="passwordForm.errors.password" class="text-xs text-red-500 mt-1">{{ passwordForm.errors.password }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-text-primary mb-1.5">Konfirmasi Password Baru</label>
                        <PasswordInput v-model="passwordForm.password_confirmation"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-border-warm bg-gray-50 dark:bg-gray-700 text-text-primary focus:outline-none focus:ring-2 focus:ring-orange-400 text-sm" />
                    </div>
                    <div class="flex justify-end pt-2">
                        <button type="submit" :disabled="passwordForm.processing" class="px-5 py-2 bg-gray-900 dark:bg-gray-100 text-white dark:text-gray-900 font-semibold rounded-xl text-sm hover:opacity-90 transition-all disabled:opacity-50 active:scale-95">
                            Ganti Password
                        </button>
                    </div>
                </form>
            </div>

            <!-- PIN Management (Admin only) -->
            <div v-if="isAdmin" class="mx-4 sm:mx-6 lg:mx-8 bg-surface-warm dark:bg-gray-800 rounded-2xl border border-border-warm shadow-sm p-6">
                <h3 class="font-bold text-text-primary text-base mb-1 flex items-center gap-2">
                    <svg class="w-4 h-4 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    {{ hasPin ? 'Reset PIN Admin' : 'Buat PIN Admin' }}
                </h3>
                <p class="text-xs text-gray-400 mb-4 pb-3 border-b border-border-warm">PIN 6 digit digunakan untuk konfirmasi saat melakukan Role Switch.</p>
                <form @submit.prevent="submitPin" class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-text-primary mb-1.5">Password Anda (Konfirmasi Identitas)</label>
                        <PasswordInput v-model="pinForm.password" placeholder="Masukkan password akun"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-border-warm bg-gray-50 dark:bg-gray-700 text-text-primary focus:outline-none focus:ring-2 focus:ring-orange-400 text-sm" />
                        <p v-if="pinForm.errors.password" class="text-xs text-red-500 mt-1">{{ pinForm.errors.password }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-text-primary mb-2">PIN Baru (6 Digit)</label>
                        <div class="flex gap-2">
                            <input v-for="(_, i) in pinChars" :key="i" v-model="pinChars[i]" type="password" maxlength="1" inputmode="numeric"
                                :ref="el => { if (el) pinRefs[i] = el as HTMLInputElement }"
                                @input="handlePinInput(i, 'pin')" @keydown="handlePinKeydown($event, i, 'pin')"
                                class="w-10 h-11 text-center text-lg font-bold rounded-xl border-2 border-border-warm bg-gray-50 dark:bg-gray-700 text-text-primary focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-400/30 transition-all" />
                        </div>
                        <p v-if="pinForm.errors.pin" class="text-xs text-red-500 mt-1">{{ pinForm.errors.pin }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-text-primary mb-2">Konfirmasi PIN Baru</label>
                        <div class="flex gap-2">
                            <input v-for="(_, i) in pinConfirmChars" :key="i" v-model="pinConfirmChars[i]" type="password" maxlength="1" inputmode="numeric"
                                :ref="el => { if (el) pinConfirmRefs[i] = el as HTMLInputElement }"
                                @input="handlePinInput(i, 'confirm')" @keydown="handlePinKeydown($event, i, 'confirm')"
                                class="w-10 h-11 text-center text-lg font-bold rounded-xl border-2 border-border-warm bg-gray-50 dark:bg-gray-700 text-text-primary focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-400/30 transition-all" />
                        </div>
                    </div>
                    <div class="flex justify-end pt-2">
                        <button type="submit" :disabled="pinForm.processing" class="px-5 py-2 bg-orange-600 hover:bg-orange-700 text-white font-semibold rounded-xl text-sm transition-all disabled:opacity-50 active:scale-95">
                            {{ hasPin ? 'Reset PIN' : 'Buat PIN' }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Role Switch (Admin only) -->
            <div v-if="isAdmin" class="mx-4 sm:mx-6 lg:mx-8 bg-surface-warm dark:bg-gray-800 rounded-2xl border border-border-warm shadow-sm p-6">
                <h3 class="font-bold text-text-primary text-base mb-1 flex items-center gap-2">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                    Role Switch
                </h3>
                <p class="text-xs text-gray-400 mb-4 pb-3 border-b border-border-warm">Lihat tampilan menu berdasarkan role lain. Perlu PIN 6 digit untuk konfirmasi.</p>

                <!-- Restore if currently switched -->
                <div v-if="switchedRole" class="space-y-4">
                    <p class="text-sm text-text-primary font-medium">Anda sedang dalam mode <span class="font-bold text-orange-600">{{ switchedRole }}</span>. Kembali ke Admin:</p>
                    <form @submit.prevent="submitRestore" class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-text-primary mb-2">Masukkan PIN Admin</label>
                            <div class="flex gap-2">
                                <input v-for="(_, i) in restorePinChars" :key="i" v-model="restorePinChars[i]" type="password" maxlength="1" inputmode="numeric"
                                    :ref="el => { if (el) restorePinRefs[i] = el as HTMLInputElement }"
                                    @input="handleSwitchPinInput(i, 'restore')" @keydown="handleSwitchPinKeydown($event, i, 'restore')"
                                    class="w-10 h-11 text-center text-lg font-bold rounded-xl border-2 border-border-warm bg-gray-50 dark:bg-gray-700 text-text-primary focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-400/30 transition-all" />
                            </div>
                            <p v-if="restoreForm.errors.pin" class="text-xs text-red-500 mt-1">{{ restoreForm.errors.pin }}</p>
                        </div>
                        <button type="submit" :disabled="restoreForm.processing" class="px-5 py-2 bg-gray-900 dark:bg-gray-100 text-white dark:text-gray-900 font-semibold rounded-xl text-sm hover:opacity-90 transition-all disabled:opacity-50 active:scale-95">
                            Kembali ke Admin
                        </button>
                    </form>
                </div>

                <!-- Switch form if not yet switched -->
                <form v-else @submit.prevent="submitSwitch" class="space-y-4">
                    <div v-if="!hasPin" class="p-3 bg-amber-50 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-700 rounded-xl text-xs text-amber-700 dark:text-amber-400">
                        ⚠️ Anda belum mengatur PIN. Buat PIN terlebih dahulu di bagian atas.
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-text-primary mb-1.5">Pilih Role yang Ingin Dilihat</label>
                        <select v-model="switchForm.role" class="w-full px-3.5 py-2.5 rounded-xl border border-border-warm bg-gray-50 dark:bg-gray-700 text-text-primary focus:outline-none focus:ring-2 focus:ring-orange-400 text-sm">
                            <option value="" disabled>Pilih role...</option>
                            <option v-for="r in roles" :key="r" :value="r" :disabled="user.roles[0]?.name === r">{{ r }}</option>
                        </select>
                        <p v-if="switchForm.errors.role" class="text-xs text-red-500 mt-1">{{ switchForm.errors.role }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-text-primary mb-2">Konfirmasi PIN (6 Digit)</label>
                        <div class="flex gap-2">
                            <input v-for="(_, i) in switchPinChars" :key="i" v-model="switchPinChars[i]" type="password" maxlength="1" inputmode="numeric"
                                :ref="el => { if (el) switchPinRefs[i] = el as HTMLInputElement }"
                                @input="handleSwitchPinInput(i, 'switch')" @keydown="handleSwitchPinKeydown($event, i, 'switch')"
                                class="w-10 h-11 text-center text-lg font-bold rounded-xl border-2 border-border-warm bg-gray-50 dark:bg-gray-700 text-text-primary focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-400/30 transition-all" />
                        </div>
                        <p v-if="switchForm.errors.pin" class="text-xs text-red-500 mt-1">{{ switchForm.errors.pin }}</p>
                    </div>
                    <div class="flex justify-end pt-2">
                        <button type="submit" :disabled="switchForm.processing || !hasPin" class="px-5 py-2 bg-orange-600 hover:bg-orange-700 text-white font-semibold rounded-xl text-sm transition-all disabled:opacity-50 active:scale-95 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                            Beralih Role
                        </button>
                    </div>
                </form>
            </div>

        </div>
</template>
