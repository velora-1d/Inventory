<script setup lang="ts">
import { ref, onMounted, watch } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import { confirmState } from '@/confirm';

const executeConfirm = () => {
    if (confirmState.value.onConfirm) confirmState.value.onConfirm();
};

const cancelConfirm = () => {
    if (confirmState.value.onCancel) confirmState.value.onCancel();
};

const isSidebarOpen = ref(true);
const isMobileSidebarOpen = ref(false);

const toggleSidebar = () => {
    isSidebarOpen.value = !isSidebarOpen.value;
};

const toggleMobileSidebar = () => {
    isMobileSidebarOpen.value = !isMobileSidebarOpen.value;
};

// Check user permissions dynamically from page props
const page = usePage();
const user = page.props.auth.user;

// Notification Toast State
const toast = ref<{ show: boolean; message: string; type: 'success' | 'error' }>({
    show: false,
    message: '',
    type: 'success',
});

const showToast = (message: string, type: 'success' | 'error') => {
    toast.value = { show: true, message, type };
    setTimeout(() => {
        toast.value.show = false;
    }, 5000);
};

// Watch for flash messages from backend
watch(
    () => page.props.flash,
    (flash: any) => {
        if (flash?.success) {
            showToast(flash.success, 'success');
        } else if (flash?.error) {
            showToast(flash.error, 'error');
        }
    },
    { deep: true, immediate: true }
);

const hasPermission = (permission: string): boolean => {
    const perms = (page.props.auth as any).permissions as string[] ?? [];
    const roles = (page.props.auth as any).roles as string[] ?? [];
    if (roles.includes('super-admin')) return true;
    return perms.includes(permission);
};

const canSeeGroup = (items: { permission?: string }[]): boolean =>
    items.some(i => !i.permission || hasPermission(i.permission));

// Dark Mode Toggle
const isDark = ref(false);

const updateThemeClasses = (dark: boolean) => {
    if (typeof window !== 'undefined') {
        if (dark) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    }
};

const initTheme = () => {
    if (typeof window !== 'undefined') {
        const saved = localStorage.getItem('theme');
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        isDark.value = saved === 'dark' || (!saved && prefersDark);
        updateThemeClasses(isDark.value);
    }
};

// Run immediately during setup
initTheme();

const sidebarNav = ref<HTMLElement | null>(null);
const mobileNav = ref<HTMLElement | null>(null);

// Run on mount to ensure transition consistency
onMounted(() => {
    initTheme();
    // Scroll active menu item into view
    setTimeout(() => {
        if (sidebarNav.value) {
            const activeLink = sidebarNav.value.querySelector('.bg-primary');
            if (activeLink) {
                activeLink.scrollIntoView({ block: 'nearest', behavior: 'auto' });
            }
        }
        if (mobileNav.value) {
            const activeLink = mobileNav.value.querySelector('.bg-primary');
            if (activeLink) {
                activeLink.scrollIntoView({ block: 'nearest', behavior: 'auto' });
            }
        }
    }, 100);
});

const toggleDarkMode = () => {
    isDark.value = !isDark.value;
    updateThemeClasses(isDark.value);
    if (typeof window !== 'undefined') {
        localStorage.setItem('theme', isDark.value ? 'dark' : 'light');
    }
};


// Define menu structure — each item has a `permission` key matching Spatie permission names
const menuGroups = [
    {
        name: 'Dashboard',
        items: [
            { name: 'Dashboard', route: 'dashboard', permission: 'dashboard.view', icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6' }
        ]
    },
    {
        name: 'Master Barang',
        items: [
            { name: 'Data Barang',     route: 'products.index',   permission: 'barang.view',   icon: 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4' },
            { name: 'Kategori Barang', route: 'categories.index', permission: 'kategori.view', icon: 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10' },
            { name: 'Satuan Barang',   route: 'units.index',      permission: 'satuan.view',   icon: 'M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3' }
        ]
    },
    {
        name: 'Partner & Lokasi',
        items: [
            { name: 'Supplier',      route: 'suppliers.index',  permission: 'supplier.view', icon: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z' },
            { name: 'Gudang/Lokasi', route: 'warehouses.index', permission: 'gudang.view',   icon: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4' }
        ]
    },
    {
        name: 'Transaksi Stok',
        items: [
            { name: 'Barang Masuk',  route: 'stock-ins.index',       permission: 'barang_masuk.view',   icon: 'M16 15v-6a4 4 0 00-4-4H4m12 10l-4 4m4-4l-4-4M4 9h8v10H4V9z' },
            { name: 'Barang Keluar', route: 'stock-outs.index',      permission: 'barang_keluar.view',  icon: 'M8 15v-6a4 4 0 014-4h8m-12 10l4 4m-4-4l4-4m8-2v10h-8V9h8z' },
            { name: 'Transfer Stok', route: 'stock-transfers.index', permission: 'transfer_stok.view',  icon: 'M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4' },
            { name: 'Stock Opname',  route: 'stock-opnames.index',   permission: 'stock_opname.view',   icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4' },
            { name: 'Retur Barang',  route: 'stock-returns.index',   permission: 'retur_barang.view',   icon: 'M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 8H17' }
        ]
    },
    {
        name: 'Laporan',
        items: [
            { name: 'Kartu Stok',       route: 'reports.ledger',     permission: 'laporan.view', icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z' },
            { name: 'Mutasi Barang',    route: 'reports.mutations',  permission: 'laporan.view', icon: 'M7 12l3-3 3 3 4-4M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z' },
            { name: 'Nilai Persediaan', route: 'reports.valuation',  permission: 'laporan.view', icon: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z' },
            { name: 'Stok Minimum',     route: 'reports.low-stock',  permission: 'laporan.view', icon: 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z' }
        ]
    },
    {
        name: 'Pengaturan',
        items: [
            { name: 'Manajemen User',    route: 'users.index',                  permission: 'user.view',        icon: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z' },
            { name: 'Role & Permission', route: 'roles.index',                  permission: 'role.view',        icon: 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z' },
            { name: 'Notifikasi Stok',   route: 'notification-settings.index', permission: 'notifikasi.view',  icon: 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9' }
        ]
    }
];
</script>

<template>
    <div class="h-screen overflow-hidden bg-bg-warm flex transition-colors duration-200">
        <!-- Desktop Sidebar -->
        <aside 
            :class="[
                isSidebarOpen ? 'w-64' : 'w-20',
                'hidden lg:flex flex-col h-full bg-surface-warm border-r border-border-warm transition-all duration-300 ease-in-out shrink-0'
            ]"
        >
            <!-- Sidebar Header -->
            <div class="h-16 flex items-center justify-between px-4 border-b border-border-warm">
                <Link :href="route('dashboard')" class="flex items-center space-x-3 overflow-hidden">
                    <svg class="h-8 w-8 text-primary shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                    <span v-show="isSidebarOpen" class="font-serif font-bold text-lg text-text-primary tracking-tight whitespace-nowrap transition-all duration-300">
                        INVENTORY
                    </span>
                </Link>
                <button 
                    @click="toggleSidebar" 
                    class="p-1.5 rounded-lg bg-surface-raised text-text-secondary hover:text-text-primary border border-border-warm focus:outline-none"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="isSidebarOpen ? 'M11 19l-7-7 7-7m8 14l-7-7 7-7' : 'M13 5l7 7-7 7M5 5l7 7-7 7'"></path>
                    </svg>
                </button>
            </div>

            <nav ref="sidebarNav" class="flex-1 overflow-y-auto p-3 space-y-4 custom-scrollbar">
                <template v-for="group in menuGroups" :key="group.name">
                    <div v-if="canSeeGroup(group.items)" class="space-y-1">
                        <span
                            v-show="isSidebarOpen"
                            class="px-3 text-xs font-semibold text-text-secondary uppercase tracking-wider block"
                        >
                            {{ group.name }}
                        </span>
                        <div class="h-px bg-border-warm my-2" v-show="!isSidebarOpen"></div>

                        <template v-for="item in group.items" :key="item.name">
                            <Link
                                v-if="!item.permission || hasPermission(item.permission)"
                                :href="route(item.route)"
                                :class="[
                                    route().current(item.route)
                                        ? 'bg-primary text-primary-foreground font-semibold shadow-sm'
                                        : 'text-text-secondary hover:bg-surface-raised hover:text-text-primary',
                                    'flex items-center space-x-3 px-3 py-2.5 rounded-xl text-sm transition-all duration-150'
                                ]"
                            >
                                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon"></path>
                                </svg>
                                <span v-show="isSidebarOpen" class="transition-all duration-300 whitespace-nowrap">
                                    {{ item.name }}
                                </span>
                            </Link>
                        </template>
                    </div>
                </template>
            </nav>
        </aside>

        <!-- Mobile Sidebar / Off-canvas -->
        <div v-show="isMobileSidebarOpen" class="fixed inset-0 flex z-40 lg:hidden" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="toggleMobileSidebar"></div>
            
            <aside class="relative flex flex-col max-w-xs w-64 bg-surface-warm border-r border-border-warm pt-5 pb-4">
                <div class="absolute top-0 right-0 -mr-12 pt-2">
                    <button 
                        @click="toggleMobileSidebar" 
                        class="ml-1 flex items-center justify-center h-10 w-10 rounded-full focus:outline-none focus:ring-2 focus:ring-inset focus:ring-white"
                    >
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <div class="flex-shrink-0 flex items-center px-4 mb-5">
                    <svg class="h-8 w-8 text-primary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                    <span class="font-serif font-bold text-lg text-text-primary tracking-tight">
                        INVENTORY
                    </span>
                </div>

                <nav ref="mobileNav" class="flex-1 h-0 overflow-y-auto px-2 space-y-4">
                    <template v-for="group in menuGroups" :key="group.name">
                        <div v-if="canSeeGroup(group.items)" class="space-y-1">
                            <span class="px-3 text-xs font-semibold text-text-secondary uppercase tracking-wider block">
                                {{ group.name }}
                            </span>
                            <template v-for="item in group.items" :key="item.name">
                                <Link
                                    v-if="!item.permission || hasPermission(item.permission)"
                                    :href="route(item.route)"
                                    :class="[
                                        route().current(item.route)
                                            ? 'bg-primary text-primary-foreground font-semibold shadow-sm'
                                            : 'text-text-secondary hover:bg-surface-raised hover:text-text-primary',
                                        'flex items-center space-x-3 px-3 py-2.5 rounded-xl text-sm transition-all duration-150'
                                    ]"
                                    @click="toggleMobileSidebar"
                                >
                                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon"></path>
                                    </svg>
                                    <span>{{ item.name }}</span>
                                </Link>
                            </template>
                        </div>
                    </template>
                </nav>

            </aside>
        </div>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <!-- Top Navbar -->
            <header class="h-16 flex items-center justify-between px-3 sm:px-4 lg:px-5 bg-surface-warm border-b border-border-warm z-10 shrink-0">
                <div class="flex items-center">
                    <button 
                        @click="toggleMobileSidebar" 
                        class="p-2 -ml-2 rounded-lg text-text-secondary hover:text-text-primary lg:hidden focus:outline-none"
                    >
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                    <div class="hidden lg:block">
                        <h2 class="text-sm font-medium text-text-secondary">
                            Selamat Datang, <span class="text-text-primary font-semibold">{{ user.name }}</span>
                        </h2>
                    </div>
                </div>

                <!-- User profile dropdown / settings -->
                <div class="flex items-center space-x-4">
                    <!-- Dark Mode Toggle Button -->
                    <button @click="toggleDarkMode" class="p-2 rounded-xl text-text-secondary hover:text-text-primary hover:bg-surface-raised transition-colors focus:outline-none" title="Ubah Tema">
                        <svg v-if="isDark" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707m0-12.728l.707.707m12.728 12.728l.707.707M12 8a4 4 0 100 8 4 4 0 000-8z"/></svg>
                        <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                    </button>

                    <Dropdown align="right" width="48">
                        <template #trigger>
                            <button class="flex items-center space-x-3 text-sm focus:outline-none group">
                                <div class="w-8 h-8 rounded-full bg-primary text-primary-foreground flex items-center justify-center font-bold uppercase ring-2 ring-primary/20 group-hover:ring-primary/45 transition-all duration-200">
                                    {{ user.name.charAt(0) }}
                                </div>
                                <span class="hidden md:block font-medium text-text-secondary group-hover:text-text-primary transition-colors">
                                    {{ user.name }}
                                </span>
                                <svg class="h-4 w-4 text-text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                        </template>

                        <template #content>
                            <DropdownLink :href="route('settings.user')">
                                Pengaturan Akun
                            </DropdownLink>
                            <DropdownLink :href="route('logout')" method="post" as="button">
                                Log Out
                            </DropdownLink>
                        </template>
                    </Dropdown>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto p-3 sm:p-4 lg:p-5 custom-scrollbar">
                <slot />
            </main>
        </div>

        <!-- Custom Confirm Modal Dialog -->
        <div v-if="confirmState.isOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="cancelConfirm"></div>
            <div class="bg-surface-warm border border-border-warm rounded-xl max-w-sm w-full shadow-2xl overflow-hidden relative z-10 p-6 space-y-4">
                <div class="flex items-start space-x-3">
                    <div class="p-2 rounded-lg" :class="confirmState.isAlert ? 'bg-rose-50 dark:bg-rose-950/20 text-rose-600 dark:text-rose-450' : 'bg-amber-50 dark:bg-amber-950/20 text-amber-600 dark:text-amber-400'">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <!-- Alert/Warning Icon -->
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div class="space-y-1 flex-1">
                        <h4 class="font-bold text-text-primary text-base">{{ confirmState.title || 'Konfirmasi Tindakan' }}</h4>
                        <p class="text-sm text-text-secondary">{{ confirmState.message }}</p>
                    </div>
                </div>
                <div class="flex justify-end space-x-2 pt-2">
                    <button v-if="!confirmState.isAlert" @click="cancelConfirm" class="px-3.5 py-2 rounded-lg bg-surface-raised hover:bg-border-warm text-text-secondary text-sm font-semibold transition-colors">
                        Batal
                    </button>
                    <button @click="executeConfirm" class="px-3.5 py-2 rounded-lg text-white text-sm font-semibold transition-colors" :class="confirmState.isAlert ? 'bg-slate-700 hover:bg-slate-800' : 'bg-red-600 hover:bg-red-700'">
                        {{ confirmState.isAlert ? 'Tutup' : 'Ya, Lanjutkan' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Global Toast Notification Banner -->
        <transition
            enter-active-class="transform ease-out duration-300 transition"
            enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
            enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
            leave-active-class="transition ease-in duration-100"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="toast.show" class="fixed bottom-4 right-4 z-50 max-w-sm w-full bg-white dark:bg-slate-800 border rounded-xl shadow-lg pointer-events-auto overflow-hidden" :class="toast.type === 'success' ? 'border-emerald-500' : 'border-rose-500'">
                <div class="p-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <!-- Success Icon -->
                            <svg v-if="toast.type === 'success'" class="h-6 w-6 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <!-- Error Icon -->
                            <svg v-else class="h-6 w-6 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-3 w-0 flex-1 pt-0.5">
                            <p class="text-sm font-semibold text-slate-900 dark:text-white">
                                {{ toast.type === 'success' ? 'Berhasil' : 'Gagal' }}
                            </p>
                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                {{ toast.message }}
                            </p>
                        </div>
                        <div class="ml-4 flex-shrink-0 flex">
                            <button @click="toast.show = false" class="bg-transparent rounded-md inline-flex text-slate-400 hover:text-slate-500 focus:outline-none">
                                <span class="sr-only">Tutup</span>
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </transition>
    </div>
</template>

<style>
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(148, 163, 184, 0.3);
    border-radius: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: rgba(148, 163, 184, 0.5);
}
</style>
