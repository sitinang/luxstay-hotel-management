<script setup>
const props = defineProps({
    filteredBookings: Array,
    bookingSearch: String,
    bookingStatusFilter: String
});

const emit = defineEmits([
    'update:bookingSearch', 
    'update:bookingStatusFilter', 
    'updateStatus'
]);

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(value);
};
</script>

<template>
    <div class="space-y-6">
        <div class="bg-white rounded-[2rem] border border-slate-200 shadow-xl shadow-slate-200/50 p-8">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h3 class="font-serif font-bold text-2xl text-slate-900">{{ $t('order_management') }}</h3>
                    <p class="text-sm text-slate-500 mt-1">Manage guest check-in and check-out status.</p>
                </div>
                <div class="flex flex-wrap items-center gap-3 w-full md:w-auto">
                    <div class="relative flex-1 md:flex-initial min-w-[200px]">
                        <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                        <input 
                            :value="bookingSearch"
                            @input="$emit('update:bookingSearch', $event.target.value)"
                            type="text" 
                            :placeholder="$t('search_booking_placeholder')"
                            class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-navy-900 focus:border-navy-900"
                        >
                    </div>
                    <select 
                        :value="bookingStatusFilter"
                        @change="$emit('update:bookingStatusFilter', $event.target.value)"
                        class="bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-bold text-slate-600 focus:ring-2 focus:ring-navy-900 h-[42px]"
                    >
                        <option value="all">{{ $t('all_status') }}</option>
                        <option value="pending">{{ $t('pending') }}</option>
                        <option value="confirmed">{{ $t('confirmed') }}</option>
                        <option value="completed">{{ $t('completed') }}</option>
                        <option value="cancelled">{{ $t('cancelled') }}</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-[2rem] border border-slate-200 shadow-xl shadow-slate-200/50 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/80 text-slate-500 text-xs font-bold uppercase tracking-wider border-b border-slate-100">
                            <th class="px-8 py-5">{{ $t('guest_details') }}</th>
                            <th class="px-8 py-5">{{ $t('room') }}</th>
                            <th class="px-8 py-5">{{ $t('stay_dates') }}</th>
                            <th class="px-8 py-5">{{ $t('status') }}</th>
                            <th class="px-8 py-5 text-right">{{ $t('total') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <tr v-for="booking in filteredBookings" :key="booking.id" class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-8 py-5">
                                <div class="font-bold text-slate-800 text-sm">{{ booking.user?.name }}</div>
                                <div class="text-xs text-slate-500 mt-0.5 flex items-center gap-1.5"><i class="fa-solid fa-envelope text-[10px]"></i> {{ booking.user?.email }}</div>
                                <div v-if="booking.phone" class="text-xs text-navy-600 mt-1 flex items-center gap-1.5 font-medium"><i class="fa-solid fa-phone text-[10px]"></i> {{ booking.phone }}</div>
                            </td>
                            <td class="px-8 py-5">
                                <span class="px-3 py-1 bg-slate-100 rounded-lg text-sm font-bold text-slate-700 border border-slate-200">{{ booking.room?.room_number }}</span>
                                <span class="text-xs text-slate-500 ml-2 uppercase font-bold">{{ booking.room?.room_type?.name }}</span>
                            </td>
                            <td class="px-8 py-5">
                                <div class="text-xs text-slate-600">
                                    <div class="flex items-center gap-2 mb-1"><span class="w-1.5 h-1.5 bg-emerald-500 rounded-full"></span> <span class="font-bold">In:</span> {{ booking.check_in }}</div>
                                    <div class="flex items-center gap-2 mb-2"><span class="w-1.5 h-1.5 bg-rose-500 rounded-full"></span> <span class="font-bold">Out:</span> {{ booking.check_out }}</div>
                                    <div v-if="booking.special_requests" class="mt-2 p-2 bg-slate-50 rounded-lg border border-slate-100 italic text-slate-500 max-w-[200px] leading-tight">
                                        "{{ booking.special_requests }}"
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-5">
                                <div class="flex flex-col gap-2">
                                    <div class="flex items-center gap-2">
                                        <!-- Buttons with emits -->
                                        <div v-if="booking.status === 'pending'" class="btn-premium-container">
                                            <button @click="$emit('updateStatus', booking, 'confirmed')" class="btn-premium-inner px-4 py-2 text-[10px] font-bold uppercase tracking-widest flex items-center gap-2">
                                                <i class="fa-solid fa-user-check"></i> {{ $t('check_in_btn') }}
                                            </button>
                                        </div>
                                        <div v-else-if="booking.status === 'confirmed'" class="btn-premium-container btn-navy">
                                            <button @click="$emit('updateStatus', booking, 'completed')" class="btn-premium-inner px-4 py-2 text-[10px] font-bold uppercase tracking-widest flex items-center gap-2">
                                                <i class="fa-solid fa-person-walking-luggage"></i> {{ $t('check_out_btn') }}
                                            </button>
                                        </div>
                                        <span v-else :class="['px-4 py-2 text-[10px] font-bold uppercase tracking-widest rounded-xl border', booking.status === 'completed' ? 'bg-blue-100 text-blue-700 border-blue-100' : 'bg-rose-100 text-rose-700 border-rose-100']">
                                            {{ booking.status === 'completed' ? $t('completed') : $t('cancelled') }}
                                        </span>

                                        <div v-if="['pending', 'confirmed'].includes(booking.status)" class="btn-premium-container btn-navy ml-2">
                                            <button @click="$emit('updateStatus', booking, 'cancelled')" class="btn-premium-inner px-4 py-2 text-[10px] font-bold uppercase tracking-widest flex items-center gap-2">
                                                <i class="fa-solid fa-xmark"></i> {{ $t('cancel_btn') }}
                                            </button>
                                        </div>
                                    </div>
                                    <div v-if="booking.guest_status !== 'waiting' && booking.status !== 'completed'" :class="['inline-flex items-center gap-2 px-3 py-1 rounded-lg text-[10px] font-bold uppercase tracking-tight w-fit border shadow-sm', booking.guest_status === 'arrived' ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-rose-50 text-rose-600 border-rose-100 animate-pulse']">
                                        <i :class="['fa-solid', booking.guest_status === 'arrived' ? 'fa-location-dot' : 'fa-bell-concierge']"></i>
                                        {{ $t(booking.guest_status + '_status') }}
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-5 text-right">
                                <div class="font-serif font-bold text-slate-900 text-lg">{{ formatCurrency(booking.total_price) }}</div>
                            </td>
                        </tr>
                        <tr v-if="filteredBookings.length === 0">
                            <td colspan="5" class="py-12 text-center text-slate-400">
                                <i class="fa-solid fa-folder-open text-4xl mb-3 block"></i>
                                {{ $t('no_bookings_found') }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
