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

const currentYear = new Date().getFullYear();

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Masuk ke Sistem" />

    <div class="min-h-screen flex bg-bg-warm transition-colors duration-500">

        <!-- ═══ LEFT PANEL — Branding ═══════════════════════════════════════ -->
        <div class="hidden lg:flex lg:w-[52%] relative overflow-hidden flex-col">
            <!-- Dark rich background — adapts to mode -->
            <div class="absolute inset-0 bg-[#1a1814] dark:bg-[#0e0d0b]"></div>

            <!-- Warm radial glow -->
            <div class="absolute top-[-8%] left-[-8%] w-[40rem] h-[40rem] rounded-full opacity-30"
                style="background: radial-gradient(circle, #c8a96e 0%, transparent 70%); filter: blur(80px);"></div>
            <div class="absolute bottom-[-10%] right-[-5%] w-[32rem] h-[32rem] rounded-full opacity-20"
                style="background: radial-gradient(circle, #a07850 0%, transparent 70%); filter: blur(100px);"></div>

            <!-- Subtle dot grid -->
            <div class="absolute inset-0 opacity-[0.06]"
                style="background-image: radial-gradient(circle, #fff 1px, transparent 1px); background-size: 32px 32px;"></div>

            <!-- Content -->
            <div class="relative z-10 flex flex-col h-full pl-20 pr-8 py-12">
                <!-- Logo -->
                <div class="flex items-center gap-4">
                    <div class="h-[4.5rem] w-[4.5rem] rounded-2xl bg-[#c8a96e] flex items-center justify-center shadow-xl">
                        <svg class="w-9 h-9 text-[#1a1814]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                    <span class="text-white font-bold text-3xl tracking-tight">INVENTORY</span>
                </div>

                <!-- Hero -->
                <div class="flex-1 flex flex-col justify-center max-w-md">
                    <!-- Pill badge -->
                    <div class="inline-flex items-center gap-2.5 border border-white/10 bg-white/5 rounded-full px-5 py-2.5 mb-10 w-fit">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span class="text-base font-medium text-white/60 tracking-wide">Sistem Real-time Aktif</span>
                    </div>

                    <h1 class="text-6xl font-extrabold text-white leading-tight tracking-tight mb-6">
                        Kelola Stok<br/>
                        <span style="color: #c8a96e;">Lebih Cerdas</span>
                    </h1>

                    <p class="text-white/45 text-lg leading-relaxed mb-14">
                        Platform manajemen inventaris dan gudang yang terintegrasi. Pantau stok, lacak mutasi, dan generate laporan dalam satu sistem.
                    </p>

                    <!-- Feature pills -->
                    <div class="space-y-5">
                        <div v-for="(feat, i) in [
                            { icon: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z', label: 'Dashboard analitik real-time' },
                            { icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4', label: 'Manajemen stok multi-gudang' },
                            { icon: 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9', label: 'Notifikasi stok menipis otomatis' },
                        ]" :key="i" class="flex items-center gap-5">
                            <div class="flex-shrink-0 w-14 h-14 rounded-2xl bg-white/8 border border-white/10 flex items-center justify-center">
                                <svg class="w-6 h-6" style="color: #c8a96e;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="feat.icon"/>
                                </svg>
                            </div>
                            <span class="text-white/60 text-lg">{{ feat.label }}</span>
                        </div>
                    </div>
                </div>

                <p class="text-white/20 text-xs">© {{ currentYear }} Inventory System. All rights reserved.</p>
            </div>
        </div>

        <!-- ═══ RIGHT PANEL — Form ══════════════════════════════════════════ -->
        <div class="flex-1 flex flex-col items-center justify-center px-6 sm:px-10 lg:px-14 py-12 relative">

            <!-- Dark mode toggle -->
            <button @click="toggleDark"
                class="absolute top-5 right-5 p-2 rounded-lg border border-border-warm bg-surface-warm hover:bg-surface-raised transition-all"
                :title="isDark ? 'Mode Terang' : 'Mode Gelap'"
            >
                <svg v-if="isDark" class="w-4.5 h-4.5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707M17.657 17.657l-.707-.707M6.343 6.343l-.707-.707M12 8a4 4 0 100 8 4 4 0 000-8z"/>
                </svg>
                <svg v-else class="w-4.5 h-4.5 text-text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                </svg>
            </button>

            <!-- Mobile logo -->
            <div class="flex lg:hidden items-center gap-2 mb-8">
                <div class="h-9 w-9 rounded-xl bg-[#c8a96e] flex items-center justify-center">
                    <svg class="w-5 h-5 text-[#1a1814]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
                <span class="font-bold text-xl text-text-primary">INVENTORY</span>
            </div>

            <!-- Card wrapper -->
            <div class="w-full max-w-[380px]">

                <!-- Heading -->
                <div class="mb-7">
                    <h2 class="text-2xl font-bold text-text-primary mb-1 tracking-tight">Selamat datang 👋</h2>
                    <p class="text-sm" style="color: var(--color-text-secondary); opacity: 0.8;">Masuk ke akun Anda untuk melanjutkan</p>
                </div>

                <!-- Status -->
                <div v-if="status"
                    class="mb-5 bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-400 p-3.5 rounded-xl text-sm font-medium">
                    {{ status }}
                </div>

                <form @submit.prevent="submit" class="space-y-5">

                    <!-- Email -->
                    <div class="space-y-1.5">
                        <label for="email" style="margin-bottom: 0 !important;">Alamat Email</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none">
                                <svg class="w-4 h-4 text-text-secondary opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            </span>
                            <input
                                id="email"
                                type="email"
                                v-model="form.email"
                                autofocus
                                autocomplete="username"
                                placeholder="nama@perusahaan.com"
                                style="padding-left: 2.25rem !important;"
                                class="w-full"
                            />
                        </div>
                        <InputError :message="form.errors.email" />
                    </div>

                    <!-- Password -->
                    <div class="space-y-1.5">
                        <label for="password" style="margin-bottom: 0 !important;">Kata Sandi</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none">
                                <svg class="w-4 h-4 text-text-secondary opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </span>
                            <input
                                id="password"
                                :type="showPassword ? 'text' : 'password'"
                                v-model="form.password"
                                autocomplete="current-password"
                                placeholder="••••••••"
                                style="padding-left: 2.25rem !important; padding-right: 2.5rem !important;"
                                class="w-full"
                            />
                            <button
                                type="button"
                                @click="showPassword = !showPassword"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-text-secondary opacity-50 hover:opacity-100 transition-opacity"
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
                        <InputError :message="form.errors.password" />
                    </div>

                    <!-- Remember me -->
                    <div class="flex items-center gap-2.5">
                        <Checkbox id="remember" name="remember" v-model:checked="form.remember"/>
                        <label for="remember"
                            style="text-transform: none !important; font-size: 0.8125rem !important; font-weight: 500 !important; letter-spacing: 0 !important; margin-bottom: 0 !important; display: inline !important; cursor: pointer;">
                            Ingat saya
                        </label>
                    </div>

                    <!-- Submit -->
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full flex items-center justify-center gap-2 rounded-lg font-semibold text-white transition-all disabled:opacity-60 disabled:cursor-not-allowed"
                        style="
                            background-color: var(--color-primary);
                            color: var(--color-primary-foreground);
                            height: 2.625rem;
                            font-size: 0.875rem;
                            border: none;
                            box-shadow: 0 1px 3px rgba(0,0,0,0.18), 0 1px 2px rgba(0,0,0,0.12);
                            padding: 0 1rem;
                        "
                    >
                        <svg v-if="form.processing" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        {{ form.processing ? 'Memproses...' : 'Masuk ke Sistem' }}
                    </button>
                </form>


            </div>
        </div>
    </div>
</template>
