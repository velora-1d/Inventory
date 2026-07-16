<script setup lang="ts">
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps<{
    canResetPassword?: boolean;
    status?: string;
}>();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const showPassword = ref(false);

const submit = () => {
    form.post(route('login'), {
        onFinish: () => {
            form.reset('password');
        },
    });
};
</script>

<template>
    <Head title="Masuk ke Sistem" />

    <div class="min-h-screen flex items-center justify-center bg-gradient-to-tr from-indigo-950 via-slate-900 to-indigo-900 px-4 py-12 sm:px-6 lg:px-8 relative overflow-hidden">
        <!-- Background Decorative Orbs -->
        <div class="absolute -top-40 -left-40 w-96 h-96 rounded-full bg-indigo-500/10 blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-40 -right-40 w-96 h-96 rounded-full bg-emerald-500/10 blur-3xl pointer-events-none"></div>

        <div class="max-w-md w-full space-y-8 bg-surface-warm/5 dark:bg-surface-warm/40 backdrop-blur-xl border border-white/10 dark:border-border-warm p-8 rounded-3xl shadow-2xl relative z-10">
            <!-- Header/Logo -->
            <div class="text-center">
                <div class="mx-auto h-12 w-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-emerald-600 flex items-center justify-center shadow-lg shadow-indigo-500/30">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
                <h2 class="mt-4 text-3xl font-extrabold text-white tracking-tight">Inventory</h2>
                <p class="mt-1.5 text-sm text-slate-400">Sistem Manajemen Inventaris & Gudang</p>
            </div>

            <div v-if="status" class="bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 p-3 rounded-xl text-sm font-medium text-center">
                {{ status }}
            </div>

            <!-- Form -->
            <form class="mt-8 space-y-5" @submit.prevent="submit">
                <!-- Email -->
                <div>
                    <label for="email" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Alamat Email</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/></svg>
                        </div>
                        <input id="email" type="email" required v-model="form.email" autofocus autocomplete="username"
                            placeholder="nama@perusahaan.com"
                            class="pl-10 w-full px-4 py-3 bg-surface-warm/5 border border-white/10 dark:border-border-warm text-white rounded-xl placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all text-sm"/>
                    </div>
                    <InputError class="mt-1.5 text-xs" :message="form.errors.email" />
                </div>

                <!-- Password -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="password" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider">Kata Sandi</label>
                        <Link v-if="canResetPassword" :href="route('password.request')" class="text-xs text-indigo-400 hover:text-indigo-300 transition-colors">
                            Lupa sandi?
                        </Link>
                    </div>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        </div>
                        <input id="password" :type="showPassword ? 'text' : 'password'" required v-model="form.password" autocomplete="current-password"
                            placeholder="••••••••"
                            class="pl-10 pr-10 w-full px-4 py-3 bg-surface-warm/5 border border-white/10 dark:border-border-warm text-white rounded-xl placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all text-sm"/>
                        <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-white transition-colors">
                            <svg v-if="showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                            <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </button>
                    </div>
                    <InputError class="mt-1.5 text-xs" :message="form.errors.password" />
                </div>

                <!-- Remember Me -->
                <div class="flex items-center">
                    <label class="flex items-center cursor-pointer group">
                        <Checkbox name="remember" v-model:checked="form.remember" class="rounded border-white/10 dark:border-border-warm bg-surface-warm/5 text-indigo-600 focus:ring-indigo-500/30 cursor-pointer w-4.5 h-4.5"/>
                        <span class="ms-2 text-xs font-semibold text-slate-400 group-hover:text-slate-300 transition-colors uppercase tracking-wider">Ingat Saya</span>
                    </label>
                </div>

                <!-- Action Button -->
                <div>
                    <button type="submit" :disabled="form.processing"
                        class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-slate-900 transition-all shadow-lg shadow-indigo-600/30 hover:shadow-indigo-600/50 disabled:opacity-50 disabled:cursor-not-allowed">
                        <span v-if="form.processing" class="flex items-center gap-2">
                            <svg class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                            Memproses...
                        </span>
                        <span v-else>Masuk</span>
                    </button>
                </div>
            </form>

            <div class="text-center pt-2">
                <p class="text-xs text-slate-500">
                    Belum punya akun?
                    <Link :href="route('register')" class="font-semibold text-indigo-400 hover:text-indigo-300 transition-colors ml-1">
                        Daftar disini
                    </Link>
                </p>
            </div>
        </div>
    </div>
</template>
