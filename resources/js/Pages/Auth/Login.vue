<script setup lang="ts">
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';

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
const isDark = ref(false);

const initTheme = () => {
    if (typeof window !== 'undefined') {
        const saved = localStorage.getItem('theme');
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        isDark.value = saved === 'dark' || (!saved && prefersDark);
        document.documentElement.classList.toggle('dark', isDark.value);
    }
};

const toggleDark = () => {
    isDark.value = !isDark.value;
    document.documentElement.classList.toggle('dark', isDark.value);
    localStorage.setItem('theme', isDark.value ? 'dark' : 'light');
};

initTheme();

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Masuk ke Sistem" />

    <!-- Full-screen two-column layout -->
    <div class="min-h-screen flex bg-bg-warm transition-colors duration-500">

        <!-- ═══ LEFT PANEL — Branding ═══════════════════════════════════════ -->
        <div class="hidden lg:flex lg:w-[55%] relative overflow-hidden flex-col">
            <!-- Gradient background -->
            <div class="absolute inset-0 bg-gradient-to-br from-[#1a1625] via-[#2d1f3d] to-[#1a2640]"></div>

            <!-- Animated orbs -->
            <div class="absolute top-[-10%] left-[-5%] w-[45rem] h-[45rem] rounded-full bg-violet-600/20 blur-[120px] animate-pulse"></div>
            <div class="absolute bottom-[-10%] right-[-5%] w-[35rem] h-[35rem] rounded-full bg-indigo-500/15 blur-[100px] animate-pulse" style="animation-delay: 1.5s"></div>
            <div class="absolute top-[40%] left-[40%] w-[20rem] h-[20rem] rounded-full bg-emerald-500/10 blur-[80px] animate-pulse" style="animation-delay: 3s"></div>

            <!-- Grid overlay -->
            <div class="absolute inset-0 opacity-[0.04]" style="background-image: linear-gradient(#fff 1px, transparent 1px), linear-gradient(90deg, #fff 1px, transparent 1px); background-size: 48px 48px;"></div>

            <!-- Content -->
            <div class="relative z-10 flex flex-col h-full px-14 py-12">
                <!-- Logo -->
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-xl bg-gradient-to-br from-violet-500 to-indigo-600 flex items-center justify-center shadow-lg shadow-violet-500/40">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                    <span class="text-white font-bold text-xl tracking-tight">INVENTORY</span>
                </div>

                <!-- Center hero text -->
                <div class="flex-1 flex flex-col justify-center max-w-md">
                    <div class="inline-flex items-center gap-2 bg-white/10 border border-white/15 rounded-full px-3.5 py-1.5 mb-6 w-fit">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span class="text-xs font-semibold text-white/80 tracking-wide">Sistem Inventaris Real-time</span>
                    </div>

                    <h1 class="text-5xl font-extrabold text-white leading-tight tracking-tight mb-5">
                        Kelola Stok<br/>
                        <span class="bg-gradient-to-r from-violet-400 to-indigo-300 bg-clip-text text-transparent">Lebih Cerdas</span>
                    </h1>
                    <p class="text-white/55 text-base leading-relaxed mb-10">
                        Platform manajemen inventaris dan gudang yang terintegrasi. Pantau stok, lacak mutasi, dan generate laporan dalam satu sistem.
                    </p>

                    <!-- Feature list -->
                    <div class="space-y-3.5">
                        <div v-for="(feat, i) in [
                            { icon: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z', text: 'Dashboard analitik real-time' },
                            { icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4', text: 'Manajemen stok multi-gudang' },
                            { icon: 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z', text: 'Notifikasi stok menipis otomatis' },
                        ]" :key="i" class="flex items-center gap-3">
                            <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-white/10 border border-white/15 flex items-center justify-center">
                                <svg class="w-4 h-4 text-violet-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="feat.icon"/>
                                </svg>
                            </div>
                            <span class="text-white/65 text-sm">{{ feat.text }}</span>
                        </div>
                    </div>
                </div>

                <!-- Bottom copyright -->
                <div class="text-white/25 text-xs">
                    © 2025 Inventory System. Hak cipta dilindungi.
                </div>
            </div>
        </div>

        <!-- ═══ RIGHT PANEL — Login Form ════════════════════════════════════ -->
        <div class="flex-1 flex flex-col items-center justify-center px-6 sm:px-10 lg:px-16 py-12 relative">

            <!-- Dark mode toggle (top-right) -->
            <button
                @click="toggleDark"
                class="absolute top-5 right-5 p-2.5 rounded-xl border border-border-warm bg-surface-warm hover:bg-surface-raised transition-all shadow-sm"
                :title="isDark ? 'Mode Terang' : 'Mode Gelap'"
            >
                <!-- Sun icon (shown in dark mode) -->
                <svg v-if="isDark" class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707M17.657 17.657l-.707-.707M6.343 6.343l-.707-.707M12 8a4 4 0 100 8 4 4 0 000-8z"/>
                </svg>
                <!-- Moon icon (shown in light mode) -->
                <svg v-else class="w-5 h-5 text-text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                </svg>
            </button>

            <!-- Mobile logo (only shown on small screens) -->
            <div class="flex lg:hidden items-center gap-2 mb-8">
                <div class="h-9 w-9 rounded-xl bg-gradient-to-br from-violet-500 to-indigo-600 flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
                <span class="font-bold text-xl text-text-primary">INVENTORY</span>
            </div>

            <!-- Form card -->
            <div class="w-full max-w-[400px]">

                <!-- Heading -->
                <div class="mb-8">
                    <h2 class="text-2xl font-bold text-text-primary mb-1.5 tracking-tight">Selamat datang 👋</h2>
                    <p class="text-sm text-text-secondary">Masuk ke akun Anda untuk melanjutkan</p>
                </div>

                <!-- Status flash -->
                <div v-if="status" class="mb-5 bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-400 p-3.5 rounded-xl text-sm font-medium">
                    {{ status }}
                </div>

                <form @submit.prevent="submit" class="space-y-5">

                    <!-- Email field -->
                    <div>
                        <label for="email" class="block text-xs font-semibold text-text-secondary uppercase tracking-wider mb-1.5">Alamat Email</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="w-4 h-4 text-text-secondary opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <input
                                id="email"
                                type="email"
                                required
                                v-model="form.email"
                                autofocus
                                autocomplete="username"
                                placeholder="nama@perusahaan.com"
                                class="pl-9 w-full"
                            />
                        </div>
                        <InputError class="mt-1.5 text-xs" :message="form.errors.email" />
                    </div>

                    <!-- Password field -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="password" class="block text-xs font-semibold text-text-secondary uppercase tracking-wider">Kata Sandi</label>
                            <Link v-if="canResetPassword" :href="route('password.request')"
                                class="text-xs font-semibold text-violet-600 dark:text-violet-400 hover:underline">
                                Lupa sandi?
                            </Link>
                        </div>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="w-4 h-4 text-text-secondary opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </div>
                            <input
                                id="password"
                                :type="showPassword ? 'text' : 'password'"
                                required
                                v-model="form.password"
                                autocomplete="current-password"
                                placeholder="••••••••"
                                class="pl-9 pr-10 w-full"
                            />
                            <button
                                type="button"
                                @click="showPassword = !showPassword"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-text-secondary opacity-60 hover:opacity-100 transition-opacity"
                            >
                                <svg v-if="showPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                </svg>
                                <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </button>
                        </div>
                        <InputError class="mt-1.5 text-xs" :message="form.errors.password" />
                    </div>

                    <!-- Remember me -->
                    <div class="flex items-center">
                        <label class="flex items-center gap-2.5 cursor-pointer" style="text-transform: none; font-size: 0.8125rem; font-weight: 500; letter-spacing: 0; margin-bottom: 0;">
                            <Checkbox name="remember" v-model:checked="form.remember" class="rounded"/>
                            <span class="text-text-secondary">Ingat saya</span>
                        </label>
                    </div>

                    <!-- Submit button -->
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full flex items-center justify-center gap-2 py-2.5 px-4 rounded-xl text-sm font-semibold text-white bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-500 hover:to-indigo-500 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:ring-offset-2 transition-all shadow-md shadow-violet-600/25 hover:shadow-violet-600/40 disabled:opacity-60 disabled:cursor-not-allowed"
                        style="height: auto; border: none; font-size: 0.875rem;"
                    >
                        <svg v-if="form.processing" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        {{ form.processing ? 'Memproses...' : 'Masuk ke Sistem' }}
                    </button>
                </form>

                <!-- Divider + register link -->
                <div class="mt-6 pt-5 border-t border-border-warm text-center">
                    <p class="text-sm text-text-secondary">
                        Belum punya akun?
                        <Link :href="route('register')" class="font-semibold text-violet-600 dark:text-violet-400 hover:underline ml-1">
                            Daftar disini
                        </Link>
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
