<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineOptions({ layout: AuthenticatedLayout });


interface NotifSettings {
    low_stock_alert: boolean;
    low_stock_threshold: number;
    stock_in_notification: boolean;
    stock_out_notification: boolean;
    email_notifications: boolean;
    browser_notifications: boolean;
    daily_report: boolean;
    daily_report_time: string;
}

const props = defineProps<{ settings: NotifSettings }>();

const form = useForm({ ...props.settings });
const saved = ref(false);

const save = () => {
    form.put(route('notification-settings.update'), {
        onSuccess: () => {
            saved.value = true;
            setTimeout(() => saved.value = false, 3000);
        },
    });
};

interface ToggleItem {
    key: keyof NotifSettings;
    label: string;
    desc: string;
    iconPath: string;
    colorClass: string;
    colorBg: string;
}

const toggleItems: ToggleItem[] = [
    { 
        key: 'low_stock_alert', 
        label: 'Alert Stok Rendah', 
        desc: 'Notifikasi saat stok produk berada di bawah batas minimum', 
        iconPath: 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z', 
        colorClass: 'text-amber-600 dark:text-amber-400',
        colorBg: 'bg-amber-50 dark:bg-amber-950/20'
    },
    { 
        key: 'stock_in_notification', 
        label: 'Notifikasi Barang Masuk', 
        desc: 'Notifikasi setiap kali transaksi barang masuk dikonfirmasi', 
        iconPath: 'M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4', 
        colorClass: 'text-emerald-600 dark:text-emerald-400',
        colorBg: 'bg-emerald-50 dark:bg-emerald-950/20'
    },
    { 
        key: 'stock_out_notification', 
        label: 'Notifikasi Barang Keluar', 
        desc: 'Notifikasi setiap kali transaksi barang keluar dikonfirmasi', 
        iconPath: 'M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12', 
        colorClass: 'text-rose-600 dark:text-rose-400',
        colorBg: 'bg-rose-50 dark:bg-rose-950/20'
    },

    { 
        key: 'browser_notifications', 
        label: 'Notifikasi Browser', 
        desc: 'Tampilkan notifikasi langsung di browser (push notification)', 
        iconPath: 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9', 
        colorClass: 'text-cyan-600 dark:text-cyan-400',
        colorBg: 'bg-cyan-50 dark:bg-cyan-950/20'
    },
    { 
        key: 'daily_report', 
        label: 'Laporan Ringkasan Harian', 
        desc: 'Terima ringkasan aktivitas stok harian secara otomatis', 
        iconPath: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 002 2h2a2 2 0 002-2', 
        colorClass: 'text-teal-600 dark:text-teal-400',
        colorBg: 'bg-teal-50 dark:bg-teal-950/20'
    },
];
</script>

<template>
    <Head title="Pengaturan Notifikasi" />
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">Pengaturan Notifikasi</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Sesuaikan notifikasi sesuai kebutuhan operasional Anda</p>
            </div>

        <div class="py-6 px-4 sm:px-6 lg:px-8 space-y-5">
            <div>
                <!-- Success toast -->
                <Transition enter-active-class="transition duration-300" enter-from-class="opacity-0 -translate-y-2" enter-to-class="opacity-100 translate-y-0"
                    leave-active-class="transition duration-200" leave-from-class="opacity-100" leave-to-class="opacity-0">
                    <div v-if="saved" class="flex items-center gap-3 bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-700 text-emerald-700 dark:text-emerald-300 rounded-xl px-4 py-3 text-sm font-medium">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Pengaturan berhasil disimpan!
                    </div>
                </Transition>

                <!-- Toggle settings -->
                <div class="bg-surface-warm dark:bg-gray-800 rounded-2xl border border-border-warm shadow-sm divide-y divide-gray-150/40 dark:divide-gray-700/50 overflow-hidden">
                    <div v-for="item in toggleItems" :key="item.key" class="flex items-center justify-between p-5 hover:bg-orange-50/10 dark:hover:bg-orange-950/5 transition-colors">
                        <div class="flex items-center gap-4">
                            <div :class="['w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0', item.colorBg, item.colorClass]">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.iconPath" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-850 dark:text-gray-150">{{ item.label }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 leading-relaxed">{{ item.desc }}</p>
                            </div>
                        </div>
                        <!-- Toggle switch -->
                        <button type="button" @click="(form[item.key] as boolean) = !(form[item.key] as boolean)"
                            :class="['relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-200 flex-shrink-0 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800', (form[item.key] as boolean) ? 'bg-orange-600' : 'bg-gray-250 dark:bg-gray-700']">
                            <span :class="['inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform duration-200', (form[item.key] as boolean) ? 'translate-x-6' : 'translate-x-1']"/>
                        </button>
                    </div>
                </div>

                <!-- Additional settings -->
                <div class="bg-surface-warm dark:bg-gray-800 rounded-2xl border border-border-warm shadow-sm p-5 space-y-4">
                    <h3 class="text-sm font-bold text-gray-800 dark:text-gray-200">Pengaturan Tambahan</h3>
                    
                    <!-- Low stock threshold -->
                    <div class="flex items-center justify-between gap-4 py-2 border-b border-gray-150/40 dark:border-gray-700/50">
                        <div>
                            <p class="text-sm font-semibold text-gray-850 dark:text-gray-150">Batas Stok Minimum Alert</p>
                            <p class="text-xs text-gray-500 dark:text-gray-405 mt-0.5">Notifikasi saat stok ≤ nilai ini (dalam unit)</p>
                        </div>
                        <input v-model.number="form.low_stock_threshold" type="number" min="0"
                            class="w-24 px-3 py-2 text-sm text-center font-semibold rounded-xl border border-gray-250 dark:border-gray-650 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-orange-500"/>
                    </div>
                    
                    <!-- Daily report time -->
                    <div v-if="form.daily_report" class="flex items-center justify-between gap-4 py-2">
                        <div>
                            <p class="text-sm font-semibold text-gray-850 dark:text-gray-150">Waktu Pengiriman Laporan</p>
                            <p class="text-xs text-gray-500 dark:text-gray-405 mt-0.5">Laporan ringkasan harian dikirim setiap hari pada waktu ini</p>
                        </div>
                        <input v-model="form.daily_report_time" type="time"
                            class="w-28 px-3 py-2 text-sm text-center font-semibold rounded-xl border border-gray-250 dark:border-gray-650 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-orange-500"/>
                    </div>
                </div>

                <!-- Save button -->
                <div class="flex justify-end">
                    <button @click="save" :disabled="form.processing"
                        class="inline-flex items-center gap-2 px-6 py-2.5 text-sm font-semibold text-white bg-orange-600 hover:bg-orange-700 disabled:opacity-60 rounded-xl transition-colors shadow-sm active:scale-95 duration-100">
                        <svg v-if="form.processing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        {{ form.processing ? 'Menyimpan...' : 'Simpan Pengaturan' }}
                    </button>
                </div>
            </div>
        </div>
</template>
