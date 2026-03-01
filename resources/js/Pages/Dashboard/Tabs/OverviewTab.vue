<script setup>
import StatCard from '@/Components/StatCard.vue';

const props = defineProps({
    stats: Object,
    recentBookings: Array,
    chartData: Object,
    revenueChartRef: Object,
    occupancyChartRef: Object
});

const emit = defineEmits(['viewAllBookings']);

const formatMoney = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(value);
};
</script>

<template>
    <div class="space-y-6">
        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
            <StatCard 
                :title="$t('total_revenue')" 
                :value="formatMoney(stats.total_revenue)"
                icon="fa-solid fa-wallet"
                :is-dark="true"
                class="md:col-span-2 lg:col-span-2"
            >
                <template #footer>
                    <div class="mt-6 flex items-center gap-3 text-xs font-bold uppercase tracking-wider bg-gold-400/20 text-gold-300 w-fit px-4 py-2 rounded-xl backdrop-blur-sm border border-gold-400/30">
                        <i class="fa-solid fa-chart-line"></i>
                        <span>+12.5% vs Last Month</span>
                    </div>
                </template>
            </StatCard>

            <StatCard 
                :title="$t('active_bookings')" 
                :value="stats.active_bookings"
                icon="fa-solid fa-calendar-check"
                color-class="text-amber-600"
                bg-class="bg-amber-50"
            />

            <StatCard 
                :title="$t('available_rooms')" 
                :value="stats.available_rooms"
                :sub-value="stats.total_rooms"
                icon="fa-solid fa-bed"
                color-class="text-emerald-600"
                bg-class="bg-emerald-50"
            />
        </div>

        <!-- Charts Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="bg-white p-8 rounded-[2.5rem] border border-slate-200 shadow-xl shadow-slate-200/50">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="font-serif font-bold text-xl text-navy-900">{{ $t('revenue_trend') }}</h3>
                    <span class="text-[10px] uppercase font-bold text-slate-400 tracking-widest">{{ $t('last_6_months') }}</span>
                </div>
                <div class="h-[250px] w-full">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>

            <div class="bg-white p-8 rounded-[2.5rem] border border-slate-200 shadow-xl shadow-slate-200/50">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="font-serif font-bold text-xl text-navy-900">{{ $t('occupancy_stats') }}</h3>
                    <span class="text-[10px] uppercase font-bold text-slate-400 tracking-widest">{{ $t('last_30_days') }}</span>
                </div>
                <div class="h-[250px] w-full">
                    <canvas id="occupancyChart"></canvas>
                </div>
            </div>

            <div class="bg-white p-8 rounded-[2.5rem] border border-slate-200 shadow-xl shadow-slate-200/50">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="font-serif font-bold text-xl text-navy-900">{{ $t('popular_room_types') }}</h3>
                    <span class="text-[10px] uppercase font-bold text-slate-400 tracking-widest">{{ $t('total_bookings_label') }}</span>
                </div>
                <div class="h-[250px] w-full flex items-center justify-center">
                    <canvas id="roomTypeChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Recent Activity List -->
        <div class="bg-white rounded-[2rem] border border-slate-200 shadow-xl shadow-slate-200/50 overflow-hidden">
            <div class="px-8 py-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                <h3 class="font-serif font-bold text-xl text-slate-900">{{ $t('recent_activity') }}</h3>
                <button @click="$emit('viewAllBookings')" class="px-4 py-2 bg-slate-100 rounded-xl text-xs font-bold text-navy-900 hover:bg-gold-50 hover:text-gold-700 transition-all">{{ $t('view_all') }}</button>
            </div>
            <div class="divide-y divide-slate-50">
                <div v-for="booking in recentBookings" :key="booking.id" class="px-8 py-5 flex items-center justify-between hover:bg-slate-50/80 transition-colors group">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-600 font-bold text-sm">
                            {{ booking.user?.name?.charAt(0) }}
                        </div>
                        <div>
                            <p class="text-sm font-bold text-slate-800">{{ booking.user?.name }}</p>
                            <p class="text-xs text-slate-500">{{ booking.room?.room_type?.name }} • No. {{ booking.room?.room_number }}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-bold text-slate-900">{{ formatMoney(booking.total_price) }}</p>
                        <p class="text-[10px] uppercase font-bold tracking-widest text-slate-400">{{ booking.status }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
