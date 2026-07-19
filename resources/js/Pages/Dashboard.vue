<script setup lang="ts">
import { formatNumber, formatCurrency } from '@/utils/format';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';

defineOptions({ layout: AuthenticatedLayout });


interface Stat {
    total_sku: number;
    total_value: number;
    total_low_stock: number;
    total_warehouses: number;
    total_suppliers: number;
    total_transactions: number;
    total_qty: number;
    total_drafts: number;
}

interface Warehouse {
    id: number;
    name: string;
    code: string;
}

interface LowStockItem {
    id: number;
    sku: string;
    name: string;
    min_stock: number;
    current_stock: number;
    unit: string;
    warehouse: string;
}

interface RecentTransaction {
    id: number;
    date: string;
    product_name: string;
    sku: string;
    warehouse_name: string;
    type: string;
    qty_in: number;
    qty_out: number;
    balance: number;
}

interface ChartItem {
    date: string;
    in: number;
    out: number;
}

const props = defineProps<{
    stats: Stat;
    warehouses: Warehouse[];
    selected_warehouse_id: string | number | null;
    low_stock_items: LowStockItem[];
    recent_transactions: RecentTransaction[];
    chart_data: ChartItem[];
}>();

// Compute maximum value in chart data for scaling
const maxVal = computed(() => {
    const vals = props.chart_data.flatMap(d => [d.in, d.out]);
    const max = Math.max(...vals);
    return max > 0 ? max : 10;
});

// Calculate SVG coordinate pathways for the dynamic area/line chart
const chartPaths = computed(() => {
    const width = 600;
    const height = 180;
    const padding = 15;
    const chartHeight = height - padding * 2;
    const max = maxVal.value;
    const len = props.chart_data.length;
    
    if (len === 0) return { lineIn: '', areaIn: '', lineOut: '', areaOut: '', pointsIn: [], pointsOut: [] };
    
    const step = width / (len - 1);
    
    const pointsIn = props.chart_data.map((day, idx) => {
        const x = idx * step;
        const y = height - padding - (day.in / max) * chartHeight;
        return { x, y, val: day.in, date: day.date };
    });
    
    const pointsOut = props.chart_data.map((day, idx) => {
        const x = idx * step;
        const y = height - padding - (day.out / max) * chartHeight;
        return { x, y, val: day.out, date: day.date };
    });
    
    const lineInPath = pointsIn.map((p, i) => `${i === 0 ? 'M' : 'L'} ${p.x.toFixed(1)} ${p.y.toFixed(1)}`).join(' ');
    const areaInPath = lineInPath ? `${lineInPath} L ${width} ${height - padding} L 0 ${height - padding} Z` : '';
    
    const lineOutPath = pointsOut.map((p, i) => `${i === 0 ? 'M' : 'L'} ${p.x.toFixed(1)} ${p.y.toFixed(1)}`).join(' ');
    const areaOutPath = lineOutPath ? `${lineOutPath} L ${width} ${height - padding} L 0 ${height - padding} Z` : '';
    
    return {
        lineIn: lineInPath,
        areaIn: areaInPath,
        lineOut: lineOutPath,
        areaOut: areaOutPath,
        pointsIn,
        pointsOut
    };
});

const warehouseFilter = ref(props.selected_warehouse_id || '');

const handleFilterChange = () => {
    router.get(route('dashboard'), { warehouse_id: warehouseFilter.value }, {
        preserveState: true,
        preserveScroll: true
    });
};

watch(warehouseFilter, () => {
    handleFilterChange();
});

// Format Currency




// Get type styling for transaction badges
const getTransactionBadgeClass = (type: string) => {
    switch (type) {
        case 'stock_in':
            return 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400';
        case 'stock_out':
            return 'bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-400';
        case 'transfer_in':
        case 'transfer_out':
            return 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400';
        case 'opname':
            return 'bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-400';
        case 'return_in':
        case 'return_out':
            return 'bg-purple-50 text-purple-700 dark:bg-purple-950/40 dark:text-purple-400';
        default:
            return 'bg-slate-50 text-slate-700 dark:bg-slate-800 dark:text-slate-400';
    }
};

const getTransactionLabel = (type: string) => {
    switch (type) {
        case 'stock_in': return 'Barang Masuk';
        case 'stock_out': return 'Barang Keluar';
        case 'transfer_in': return 'Transfer Masuk';
        case 'transfer_out': return 'Transfer Keluar';
        case 'opname': return 'Opname';
        case 'return_in': return 'Retur Masuk';
        case 'return_out': return 'Retur Keluar';
        default: return type;
    }
};
</script>

<template>
    <Head title="Dashboard" />
            <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-slate-800 dark:text-slate-100">
                        Dashboard
                    </h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                        Real-time manajemen stok barang dan gudang.
                    </p>
                </div>

                <!-- Warehouse filter dropdown (only show if user is super admin/doesn't have locked warehouse) -->
                <div class="flex items-center space-x-2" v-if="!$page.props.auth.user.warehouse_id">
                    <label for="warehouse-select" class="text-sm font-medium text-slate-600 dark:text-slate-400">
                        Filter Gudang:
                    </label>
                    <select 
                        id="warehouse-select"
                        v-model="warehouseFilter" 
                        class="rounded-xl border-border-warm dark:border-border-warm bg-surface-warm dark:bg-surface-warm text-slate-700 dark:text-slate-300 text-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200/50 shadow-sm"
                    >
                        <option value="">Semua Gudang</option>
                        <option v-for="wh in warehouses" :key="wh.id" :value="wh.id">
                            {{ wh.name }} ({{ wh.code }})
                        </option>
                    </select>
                </div>
                <div v-else class="bg-indigo-50 dark:bg-indigo-950/30 border border-indigo-100 dark:border-indigo-900/50 rounded-xl px-4 py-2 text-sm text-indigo-700 dark:text-indigo-400">
                    Akses Terbatas: <span class="font-bold">{{ warehouses.find(w => w.id === $page.props.auth.user.warehouse_id)?.name }}</span>
                </div>
            </div>

        <!-- Stats Overview Cards Grid -->
        <!-- ═══ KPI CARDS GRID (8 CARDS) ═══ -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
            <!-- Total SKU Card -->
            <div class="bg-surface-warm p-5 rounded-xl border border-border-warm shadow-sm flex items-center justify-between transition-all duration-200 hover:shadow-md">
                <div class="space-y-1">
                    <p class="text-xs font-semibold uppercase tracking-wider text-text-secondary">
                        Total SKU Aktif
                    </p>
                    <h3 class="text-2xl font-extrabold text-text-primary">
                        {{ formatNumber(stats.total_sku) }}
                    </h3>
                    <p class="text-[11px] text-text-secondary mt-0.5">
                        Jumlah item barang aktif.
                    </p>
                </div>
                <div class="p-3 bg-zinc-100 dark:bg-zinc-800 text-text-primary rounded-xl shrink-0">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                </div>
            </div>

            <!-- Total Valuation Card -->
            <div class="bg-surface-warm p-5 rounded-xl border border-border-warm shadow-sm flex items-center justify-between transition-all duration-200 hover:shadow-md">
                <div class="space-y-1">
                    <p class="text-xs font-semibold uppercase tracking-wider text-text-secondary">
                        Nilai Persediaan
                    </p>
                    <h3 class="text-2xl font-extrabold text-text-primary">
                        {{ formatCurrency(stats.total_value) }}
                    </h3>
                    <p class="text-[11px] text-text-secondary mt-0.5">
                        Valuasi metode Average.
                    </p>
                </div>
                <div class="p-3 bg-emerald-50 dark:bg-emerald-950/20 text-emerald-600 dark:text-emerald-400 rounded-xl shrink-0">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>

            <!-- Total Quantity Stock -->
            <div class="bg-surface-warm p-5 rounded-xl border border-border-warm shadow-sm flex items-center justify-between transition-all duration-200 hover:shadow-md">
                <div class="space-y-1">
                    <p class="text-xs font-semibold uppercase tracking-wider text-text-secondary">
                        Total Qty Barang
                    </p>
                    <h3 class="text-2xl font-extrabold text-text-primary">
                        {{ formatNumber(stats.total_qty) }}
                    </h3>
                    <p class="text-[11px] text-text-secondary mt-0.5">
                        Jumlah fisik unit barang.
                    </p>
                </div>
                <div class="p-3 bg-blue-50 dark:bg-blue-950/20 text-blue-600 dark:text-blue-400 rounded-xl shrink-0">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                </div>
            </div>

            <!-- Total Low Stock Card -->
            <div class="bg-surface-warm p-5 rounded-xl border border-border-warm shadow-sm flex items-center justify-between transition-all duration-200 hover:shadow-md">
                <div class="space-y-1">
                    <p class="text-xs font-semibold uppercase tracking-wider text-text-secondary">
                        Stok Menipis
                    </p>
                    <h3 class="text-2xl font-extrabold text-rose-600 dark:text-rose-450">
                        {{ formatNumber(stats.total_low_stock) }}
                    </h3>
                    <p class="text-[11px] text-text-secondary mt-0.5">
                        Di bawah stok minimum.
                    </p>
                </div>
                <div class="p-3 bg-rose-50 dark:bg-rose-950/20 text-rose-600 dark:text-rose-400 rounded-xl shrink-0">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>
            </div>

            <!-- Total Warehouses Card -->
            <div class="bg-surface-warm p-5 rounded-xl border border-border-warm shadow-sm flex items-center justify-between transition-all duration-200 hover:shadow-md">
                <div class="space-y-1">
                    <p class="text-xs font-semibold uppercase tracking-wider text-text-secondary">
                        Total Gudang
                    </p>
                    <h3 class="text-2xl font-extrabold text-text-primary">
                        {{ formatNumber(stats.total_warehouses) }}
                    </h3>
                    <p class="text-[11px] text-text-secondary mt-0.5">
                        Gudang aktif beroperasi.
                    </p>
                </div>
                <div class="p-3 bg-amber-50 dark:bg-amber-950/20 text-amber-600 dark:text-amber-400 rounded-xl shrink-0">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                </div>
            </div>

            <!-- Total Suppliers Card -->
            <div class="bg-surface-warm p-5 rounded-xl border border-border-warm shadow-sm flex items-center justify-between transition-all duration-200 hover:shadow-md">
                <div class="space-y-1">
                    <p class="text-xs font-semibold uppercase tracking-wider text-text-secondary">
                        Mitra Supplier
                    </p>
                    <h3 class="text-2xl font-extrabold text-text-primary">
                        {{ formatNumber(stats.total_suppliers) }}
                    </h3>
                    <p class="text-[11px] text-text-secondary mt-0.5">
                        Supplier aktif terdaftar.
                    </p>
                </div>
                <div class="p-3 bg-teal-50 dark:bg-teal-950/20 text-teal-600 dark:text-teal-400 rounded-xl shrink-0">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
            </div>

            <!-- Total Transactions Card -->
            <div class="bg-surface-warm p-5 rounded-xl border border-border-warm shadow-sm flex items-center justify-between transition-all duration-200 hover:shadow-md">
                <div class="space-y-1">
                    <p class="text-xs font-semibold uppercase tracking-wider text-text-secondary">
                        Aktivitas Mutasi
                    </p>
                    <h3 class="text-2xl font-extrabold text-text-primary">
                        {{ formatNumber(stats.total_transactions) }}
                    </h3>
                    <p class="text-[11px] text-text-secondary mt-0.5">
                        Mutasi terkonfirmasi.
                    </p>
                </div>
                <div class="p-3 bg-cyan-50 dark:bg-cyan-950/20 text-cyan-600 dark:text-cyan-400 rounded-xl shrink-0">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 002 2h2a2 2 0 002-2"></path>
                    </svg>
                </div>
            </div>

            <!-- Total Draft Transactions -->
            <div class="bg-surface-warm p-5 rounded-xl border border-border-warm shadow-sm flex items-center justify-between transition-all duration-200 hover:shadow-md">
                <div class="space-y-1">
                    <p class="text-xs font-semibold uppercase tracking-wider text-text-secondary">
                        Transaksi Draft
                    </p>
                    <h3 class="text-2xl font-extrabold text-orange-600 dark:text-orange-400">
                        {{ formatNumber(stats.total_drafts) }}
                    </h3>
                    <p class="text-[11px] text-text-secondary mt-0.5">
                        Menunggu konfirmasi.
                    </p>
                </div>
                <div class="p-3 bg-orange-50 dark:bg-orange-950/20 text-orange-600 dark:text-orange-400 rounded-xl shrink-0">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- ═══ CHARTS AND LOW STOCK SECTION ═══ -->
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-6">
            <!-- Premium SVG Graph for In vs Out (30 days) -->
            <div class="xl:col-span-2 space-y-6">
                <div class="bg-surface-warm p-6 rounded-2xl border border-border-warm shadow-sm">
                    <div class="mb-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div>
                            <h3 class="font-bold text-text-primary text-base">
                                Aktivitas Stok (30 Hari Terakhir)
                            </h3>
                            <p class="text-xs text-text-secondary mt-0.5">
                                Visualisasi mutasi barang masuk vs barang keluar secara kronologis.
                            </p>
                        </div>
                        <!-- Legend -->
                        <div class="flex items-center space-x-4 text-xs font-semibold">
                            <div class="flex items-center">
                                <span class="w-3 h-3 rounded bg-emerald-500 mr-1.5 inline-block"></span>
                                <span class="text-text-secondary">Barang Masuk</span>
                            </div>
                            <div class="flex items-center">
                                <span class="w-3 h-3 rounded bg-rose-500 mr-1.5 inline-block"></span>
                                <span class="text-text-secondary">Barang Keluar</span>
                            </div>
                        </div>
                    </div>

                    <!-- SVG Chart Container -->
                    <div class="relative w-full overflow-hidden">
                        <svg viewBox="0 0 600 180" width="100%" height="180" class="overflow-visible">
                            <defs>
                                <linearGradient id="gradIn" x1="0" y1="0" x2="0" y2="1">
                                    <stop offset="0%" stop-color="#10b981" stop-opacity="0.25"/>
                                    <stop offset="100%" stop-color="#10b981" stop-opacity="0.0"/>
                                </linearGradient>
                                <linearGradient id="gradOut" x1="0" y1="0" x2="0" y2="1">
                                    <stop offset="0%" stop-color="#f43f5e" stop-opacity="0.25"/>
                                    <stop offset="100%" stop-color="#f43f5e" stop-opacity="0.0"/>
                                </linearGradient>
                            </defs>

                            <!-- Grid Lines -->
                            <g stroke="var(--color-border-warm)" stroke-width="1" stroke-dasharray="4 4" opacity="0.4">
                                <line x1="0" y1="15" x2="600" y2="15" />
                                <line x1="0" y1="90" x2="600" y2="90" />
                                <line x1="0" y1="165" x2="600" y2="165" />
                            </g>

                            <!-- Area Fills -->
                            <path :d="chartPaths.areaIn" fill="url(#gradIn)" />
                            <path :d="chartPaths.areaOut" fill="url(#gradOut)" />

                            <!-- Stroke Lines -->
                            <path :d="chartPaths.lineIn" fill="none" stroke="#10b981" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
                            <path :d="chartPaths.lineOut" fill="none" stroke="#f43f5e" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />

                            <!-- Interactive Tooltip dots on hover -->
                            <g class="dots">
                                <g v-for="(p, i) in chartPaths.pointsIn" :key="'in-g-'+i" class="group/dot cursor-pointer">
                                    <circle :cx="p.x" :cy="p.y" r="4" fill="#10b981" stroke="#ffffff" stroke-width="1.5" class="opacity-0 group-hover/dot:opacity-100 transition-opacity duration-150" />
                                    <!-- Simple SVG Tooltip -->
                                    <g class="opacity-0 group-hover/dot:opacity-100 transition-opacity duration-150">
                                        <rect :x="p.x - 45" :y="p.y - 32" width="90" height="24" rx="4" fill="var(--color-primary)" />
                                        <text :x="p.x" :y="p.y - 16" fill="var(--color-primary-foreground)" font-size="9" text-anchor="middle" font-weight="bold">
                                            {{ p.date }}: +{{ p.val }}
                                        </text>
                                    </g>
                                </g>

                                <g v-for="(p, i) in chartPaths.pointsOut" :key="'out-g-'+i" class="group/dot cursor-pointer">
                                    <circle :cx="p.x" :cy="p.y" r="4" fill="#f43f5e" stroke="#ffffff" stroke-width="1.5" class="opacity-0 group-hover/dot:opacity-100 transition-opacity duration-150" />
                                    <!-- Simple SVG Tooltip -->
                                    <g class="opacity-0 group-hover/dot:opacity-100 transition-opacity duration-150">
                                        <rect :x="p.x - 45" :y="p.y - 32" width="90" height="24" rx="4" fill="var(--color-primary)" />
                                        <text :x="p.x" :y="p.y - 16" fill="var(--color-primary-foreground)" font-size="9" text-anchor="middle" font-weight="bold">
                                            {{ p.date }}: -{{ p.val }}
                                        </text>
                                    </g>
                                </g>
                            </g>
                        </svg>
                    </div>

                    <!-- X-Axis Labels (show every 5th item) -->
                    <div class="flex justify-between mt-2 px-1 text-[10px] font-semibold text-text-secondary uppercase tracking-wider">
                        <span v-for="(day, idx) in chart_data" :key="idx" v-show="idx % 5 === 0">
                            {{ day.date }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Stok Menipis Card (right column) -->
            <div class="space-y-6">
                <div class="bg-surface-warm dark:bg-surface-warm p-6 rounded-2xl border border-border-warm dark:border-border-warm shadow-sm">
                    <div class="flex items-center justify-between pb-4 border-b border-border-warm dark:border-border-warm mb-4">
                        <h3 class="font-bold text-slate-800 dark:text-slate-200 flex items-center">
                            <span class="w-2.5 h-2.5 bg-rose-500 rounded-full mr-2"></span>
                            Stok Menipis
                        </h3>
                        <span class="text-xs font-semibold px-2 py-1 rounded bg-rose-50 dark:bg-rose-950/20 text-rose-600 dark:text-rose-400">
                            {{ low_stock_items.length }} Item
                        </span>
                    </div>

                    <div class="space-y-4 max-h-[190px] overflow-y-auto custom-scrollbar">
                        <div v-for="item in low_stock_items" :key="item.id" class="p-3 border border-slate-150 dark:border-border-warm rounded-xl hover:border-slate-300 dark:hover:border-slate-700 transition-colors flex justify-between items-center">
                            <div class="space-y-1">
                                <h4 class="font-semibold text-slate-800 dark:text-slate-200 text-sm">
                                    {{ item.name }}
                                </h4>
                                <p class="text-xs text-slate-400">SKU: {{ item.sku }}</p>
                                <p class="text-[10px] bg-slate-100 dark:bg-slate-800 px-1.5 py-0.5 rounded text-slate-500 dark:text-slate-400 inline-block">
                                    {{ item.warehouse }}
                                </p>
                            </div>
                            <div class="text-right">
                                <div class="text-rose-600 dark:text-rose-400 font-bold text-sm">
                                    {{ item.current_stock }} {{ item.unit }}
                                </div>
                                <div class="text-[10px] text-slate-400">Min: {{ item.min_stock }}</div>
                            </div>
                        </div>
                        <div v-if="low_stock_items.length === 0" class="text-center py-8 text-slate-400 dark:text-slate-500 text-sm flex flex-col items-center justify-center gap-2">
                            <svg class="w-8 h-8 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                            </svg>
                            <span>Semua stok barang aman dan tercukupi.</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ═══ LOG TRANSAKSI (TERBARU) ═══ -->
        <div class="bg-surface-warm dark:bg-surface-warm rounded-2xl border border-border-warm dark:border-border-warm shadow-sm overflow-hidden mb-6">
            <div class="p-6 border-b border-border-warm dark:border-border-warm flex items-center justify-between">
                <h3 class="font-bold text-slate-800 dark:text-slate-200">
                    Transaksi Terbaru
                </h3>
                <span class="text-xs text-slate-500 dark:text-slate-400">5 Transaksi terakhir</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/50 dark:bg-surface-warm/50 text-slate-400 dark:text-slate-500 text-xs font-semibold uppercase">
                            <th class="p-4">Tanggal</th>
                            <th class="p-4">Barang</th>
                            <th class="p-4">Gudang</th>
                            <th class="p-4">Tipe</th>
                            <th class="p-4 text-right">In / Out</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-150 dark:divide-slate-800 text-sm">
                        <tr v-for="tx in recent_transactions" :key="tx.id" class="text-slate-700 dark:text-slate-300 hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors">
                            <td class="p-4 whitespace-nowrap">{{ tx.date }}</td>
                            <td class="p-4 font-medium">
                                <div>{{ tx.product_name }}</div>
                                <div class="text-xs text-slate-400">{{ tx.sku }}</div>
                            </td>
                            <td class="p-4">{{ tx.warehouse_name }}</td>
                            <td class="p-4">
                                <span :class="[getTransactionBadgeClass(tx.type), 'px-2.5 py-1 rounded-full text-xs font-semibold tracking-wide border border-transparent']">
                                    {{ getTransactionLabel(tx.type) }}
                                </span>
                            </td>
                            <td class="p-4 text-right font-semibold">
                                <span v-if="tx.qty_in > 0" class="text-emerald-600 dark:text-emerald-400">+{{ tx.qty_in }}</span>
                                <span v-else-if="tx.qty_out > 0" class="text-rose-600 dark:text-rose-400">-{{ tx.qty_out }}</span>
                                <span v-else>0</span>
                            </td>
                        </tr>
                        <tr v-if="recent_transactions.length === 0">
                            <td colspan="5" class="p-8 text-center text-slate-400 dark:text-slate-500">
                                Belum ada transaksi terekam.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
</template>
