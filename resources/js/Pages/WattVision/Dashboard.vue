<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted, computed } from 'vue';

defineOptions({ layout: AuthenticatedLayout });

interface MetricData {
    current_watts: number;
    kwh_today: number;
    cost_today_bs: number;
    tariff_rate: number;
}

interface HistoryItem {
    label: string;
    watts: number;
    kwh: number;
}

interface AlertItem {
    id: number;
    title: string;
    description: string;
    time: string;
    severity: 'danger' | 'warning' | 'success';
}

const props = defineProps<{
    metrics: MetricData;
    history: HistoryItem[];
    alerts: AlertItem[];
}>();

// Live values that fluctuate slightly for realism
const liveWatts = ref(props.metrics.current_watts);
const accumulatedKwh = ref(props.metrics.kwh_today);
const costTodayBs = computed(() => accumulatedKwh.value * props.metrics.tariff_rate);

let timer: ReturnType<typeof setInterval>;

onMounted(() => {
    timer = setInterval(() => {
        // Fluctuate watts by +/- 5%
        const changePct = (Math.random() * 10 - 5) / 100;
        liveWatts.value = Math.max(20, Math.round(liveWatts.value * (1 + changePct) * 10) / 10);
        
        // Accumulate a tiny bit of kWh over time (simulating real consumption)
        // 1 hour has 3600 seconds, we update every 2 seconds -> 1/1800 of an hour
        const addedKwh = (liveWatts.value / 1000) * (2 / 3600);
        accumulatedKwh.value = Math.round((accumulatedKwh.value + addedKwh) * 10000) / 10000;
    }, 2000);
});

onUnmounted(() => {
    clearInterval(timer);
});

// Format helpers
const formatNumber = (n: number): string => {
    return new Intl.NumberFormat('id-ID', { maximumFractionDigits: 1 }).format(n);
};

const formatCurrency = (n: number): string => {
    return 'Bs. ' + new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(n);
};

// SVG Chart calculation
const chartData = computed(() => {
    const width = 800;
    const height = 300;
    const paddingX = 40;
    const paddingY = 20;
    
    const chartWidth = width - paddingX * 2;
    const chartHeight = height - paddingY * 2;
    
    const maxVal = Math.max(...props.history.map(h => h.watts), 3000);
    const len = props.history.length;
    
    if (len === 0) return {
        line: '',
        area: '',
        points: [],
        maxVal: 3000,
        chartWidth: 720,
        chartHeight: 260,
        paddingX: 40,
        paddingY: 20,
        width: 800,
        height: 300
    };
    
    const step = chartWidth / (len - 1);
    
    const points = props.history.map((item, idx) => {
        const x = paddingX + idx * step;
        const y = height - paddingY - (item.watts / maxVal) * chartHeight;
        return { x, y, watts: item.watts, label: item.label };
    });
    
    const linePath = points.map((p, i) => `${i === 0 ? 'M' : 'L'} ${p.x.toFixed(1)} ${p.y.toFixed(1)}`).join(' ');
    const areaPath = linePath ? `${linePath} L ${width - paddingX} ${height - paddingY} L ${paddingX} ${height - paddingY} Z` : '';
    
    return {
        line: linePath,
        area: areaPath,
        points,
        maxVal,
        chartWidth,
        chartHeight,
        paddingX,
        paddingY,
        width,
        height
    };
});

// Calculate current status badge class
const statusClass = computed(() => {
    if (liveWatts.value > 1500) {
        return {
            bg: 'bg-red-950/40 border-red-500/30',
            text: 'text-red-400',
            dot: 'bg-red-500 shadow-red-500/50',
            label: 'Consumo Alto'
        };
    } else if (liveWatts.value > 500) {
        return {
            bg: 'bg-amber-950/40 border-amber-500/30',
            text: 'text-amber-400',
            dot: 'bg-amber-500 shadow-amber-500/50',
            label: 'Consumo Medio'
        };
    } else {
        return {
            bg: 'bg-emerald-950/40 border-emerald-500/30',
            text: 'text-emerald-400',
            dot: 'bg-emerald-500 shadow-emerald-500/50',
            label: 'En Vivo'
        };
    }
});
</script>

<template>
    <Head title="WattVision - Monitoreo Eléctrico" />
    
    <div class="min-h-screen bg-[#121212] text-white p-6 font-sans">
        <!-- Header -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 border-b border-[#2C2C2E] pb-5">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight text-white flex items-center gap-2">
                    <!-- Lightning/Ray Icon -->
                    <svg class="w-7 h-7 text-[#00E5FF] animate-pulse" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd" />
                    </svg>
                    WattVision
                </h1>
                <p class="text-sm text-[#98989D] mt-1">Sistema de Monitoreo Eléctrico Residente &bull; Bolivia</p>
            </div>
            
            <!-- Live Indicator Badge -->
            <div class="mt-4 md:mt-0 flex items-center gap-2.5 px-4 py-2 rounded-full border" :class="statusClass.bg">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75" :class="statusClass.dot"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2" :class="statusClass.dot"></span>
                </span>
                <span class="text-xs font-mono font-bold tracking-wider uppercase" :class="statusClass.text">{{ statusClass.label }}</span>
            </div>
        </div>

        <!-- 3-Column Metrics Row -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- Metric 1: Watts (Live) -->
            <div class="bg-[#1E1E1E] border border-[#2C2C2E] rounded-[16px] p-5 shadow-lg hover:border-[#00E5FF]/40 transition-all duration-300">
                <div class="flex justify-between items-center mb-3">
                    <span class="text-sm text-[#98989D] font-medium uppercase tracking-wider">Potencia Instantánea</span>
                    <!-- Meter Icon -->
                    <svg class="w-5 h-5 text-[#00E5FF]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
                <div class="flex items-baseline gap-1 font-mono font-bold text-3xl text-[#00E5FF]">
                    {{ formatNumber(liveWatts) }}
                    <span class="text-lg font-sans font-medium text-[#98989D]">W</span>
                </div>
                <p class="text-xs text-[#98989D] mt-2">Lectura en tiempo real del medidor inteligente</p>
            </div>

            <!-- Metric 2: Today Consumed (kWh + Bs) -->
            <div class="bg-[#1E1E1E] border border-[#2C2C2E] rounded-[16px] p-5 shadow-lg hover:border-[#00E5FF]/40 transition-all duration-300">
                <div class="flex justify-between items-center mb-3">
                    <span class="text-sm text-[#98989D] font-medium uppercase tracking-wider">Consumo de Hoy</span>
                    <!-- Plug Icon -->
                    <svg class="w-5 h-5 text-[#00E5FF]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
                <div class="flex flex-col gap-0.5">
                    <div class="flex items-baseline gap-1 font-mono font-bold text-3xl text-white">
                        {{ formatNumber(accumulatedKwh) }}
                        <span class="text-lg font-sans font-medium text-[#98989D]">kWh</span>
                    </div>
                    <div class="text-sm font-semibold text-[#32D74B]">
                        Equivalente a: {{ formatCurrency(costTodayBs) }}
                    </div>
                </div>
                <p class="text-xs text-[#98989D] mt-2">Consumo diario acumulado y costo equivalente en Bs.</p>
            </div>

            <!-- Metric 3: Tariff Rate -->
            <div class="bg-[#1E1E1E] border border-[#2C2C2E] rounded-[16px] p-5 shadow-lg hover:border-[#00E5FF]/40 transition-all duration-300">
                <div class="flex justify-between items-center mb-3">
                    <span class="text-sm text-[#98989D] font-medium uppercase tracking-wider">Tarifa de Energía</span>
                    <!-- Bolivianos Label Icon -->
                    <span class="text-xs font-bold text-[#00E5FF] px-1.5 py-0.5 border border-[#00E5FF]/30 rounded">Bs</span>
                </div>
                <div class="flex items-baseline gap-1 font-mono font-bold text-3xl text-white">
                    {{ formatNumber(metrics.tariff_rate) }}
                    <span class="text-lg font-sans font-medium text-[#98989D]">Bs / kWh</span>
                </div>
                <p class="text-xs text-[#98989D] mt-2">Tarifa residencial básica aplicable en la región</p>
            </div>
        </div>

        <!-- Dashboard Layout: 8 columns Chart, 4 columns Alerts -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-8">
            <!-- 8 Columns: Main Area Chart -->
            <div class="lg:col-span-8 bg-[#1E1E1E] border border-[#2C2C2E] rounded-[16px] p-5 shadow-lg">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h3 class="text-base font-semibold text-white">Consumo Histórico (24 Horas)</h3>
                        <p class="text-xs text-[#98989D] mt-0.5">Fluctuación de carga de potencia medida en Watts</p>
                    </div>
                    <div class="text-xs font-mono text-[#00E5FF]">Máx: {{ formatNumber(chartData.maxVal) }} W</div>
                </div>

                <!-- SVG Area Chart -->
                <div class="w-full overflow-hidden">
                    <svg :viewBox="`0 0 ${chartData.width} ${chartData.height}`" class="w-full h-auto">
                        <defs>
                            <!-- Gradient for area fill -->
                            <linearGradient id="chartGradient" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#00E5FF" stop-opacity="0.35" />
                                <stop offset="100%" stop-color="#30D158" stop-opacity="0.0" />
                            </linearGradient>
                        </defs>

                        <!-- Grid Lines -->
                        <line x1="40" y1="20" x2="760" y2="20" stroke="#2C2C2E" stroke-width="1" stroke-dasharray="4" />
                        <line x1="40" y1="85" x2="760" y2="85" stroke="#2C2C2E" stroke-width="1" stroke-dasharray="4" />
                        <line x1="40" y1="150" x2="760" y2="150" stroke="#2C2C2E" stroke-width="1" stroke-dasharray="4" />
                        <line x1="40" y1="215" x2="760" y2="215" stroke="#2C2C2E" stroke-width="1" stroke-dasharray="4" />
                        <line x1="40" y1="280" x2="760" y2="280" stroke="#2C2C2E" stroke-width="1" />

                        <!-- Area Fill -->
                        <path :d="chartData.area" fill="url(#chartGradient)" />

                        <!-- Line Path -->
                        <path :d="chartData.line" fill="none" stroke="#00E5FF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />

                        <!-- Interactive Point Dots (only for peaks / reference points) -->
                        <g v-for="(p, idx) in chartData.points" :key="idx">
                            <circle v-if="idx % 3 === 0 || p.watts > 1800" :cx="p.x" :cy="p.y" r="4" fill="#1E1E1E" stroke="#00E5FF" stroke-width="2" />
                            <!-- Text Label for peaks -->
                            <text v-if="p.watts > 1800" :x="p.x" :y="p.y - 8" text-anchor="middle" fill="#FFFFFF" font-size="9" font-family="monospace" font-weight="bold">
                                {{ formatNumber(p.watts) }}W
                            </text>
                        </g>

                        <!-- X Axis Labels (Hours) -->
                        <g v-for="(p, idx) in chartData.points" :key="`lbl-${idx}`">
                            <text v-if="idx % 4 === 0" :x="p.x" :y="295" text-anchor="middle" fill="#98989D" font-size="9" font-family="monospace">
                                {{ p.label }}
                            </text>
                        </g>
                    </svg>
                </div>
            </div>

            <!-- 4 Columns: Alerts Panel -->
            <div class="lg:col-span-4 flex flex-col gap-4">
                <div class="bg-[#1E1E1E] border border-[#2C2C2E] rounded-[16px] p-5 shadow-lg flex-1">
                    <h3 class="text-base font-semibold text-white mb-4 flex items-center gap-2">
                        <!-- Notification Icon -->
                        <svg class="w-5 h-5 text-[#FF453A]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        Alertas y Eficiencia
                    </h3>

                    <div class="space-y-4">
                        <div v-for="alert in alerts" :key="alert.id" 
                            class="p-4 rounded-xl transition-all duration-300"
                            :class="[
                                alert.severity === 'danger' ? 'bg-[#3A1C1C] text-[#FF453A] border-l-4 border-[#FF453A]' : 
                                alert.severity === 'warning' ? 'bg-[#3A2D1C] text-[#FFD60A] border-l-4 border-[#FFD60A]' : 
                                'bg-[#1C3A21] text-[#30D158] border-l-4 border-[#30D158]'
                            ]"
                        >
                            <div class="flex justify-between items-start mb-1">
                                <h4 class="font-bold text-sm">{{ alert.title }}</h4>
                                <span class="text-[10px] opacity-80 font-mono">{{ alert.time }}</span>
                            </div>
                            <p class="text-xs leading-relaxed opacity-90">{{ alert.description }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Hourly Data Table -->
        <div class="bg-[#1E1E1E] border border-[#2C2C2E] rounded-[16px] p-5 shadow-lg">
            <h3 class="text-base font-semibold text-white mb-4">Registro de Lecturas Recientes</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-[#98989D]">
                    <thead class="text-xs uppercase text-[#98989D] border-b border-[#2C2C2E]">
                        <tr>
                            <th class="py-3 px-4">Hora</th>
                            <th class="py-3 px-4 text-right">Potencia (W)</th>
                            <th class="py-3 px-4 text-right">Consumo Equivalente (kWh)</th>
                            <th class="py-3 px-4 text-right">Costo Estimado (Bs.)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#2C2C2E]">
                        <tr v-for="(item, idx) in history.slice(-8).reverse()" :key="idx" 
                            class="hover:bg-[#252525] transition-colors duration-200 text-white"
                        >
                            <td class="py-3.5 px-4 font-mono text-sm">{{ item.label }}</td>
                            <td class="py-3.5 px-4 text-right font-mono font-semibold text-[#00E5FF]">
                                {{ formatNumber(item.watts) }} W
                            </td>
                            <td class="py-3.5 px-4 text-right font-mono">{{ formatNumber(item.kwh) }} kWh</td>
                            <td class="py-3.5 px-4 text-right font-mono text-[#32D74B]">
                                {{ formatCurrency(item.kwh * metrics.tariff_rate) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Scoped styles to ensure clean layout */
table {
    border-collapse: collapse;
}
</style>
