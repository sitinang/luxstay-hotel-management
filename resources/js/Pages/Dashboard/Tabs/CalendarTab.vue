<script setup>
const props = defineProps({
    calendarData: Array
});

const isOccupied = (room, day) => {
    const currentYear = new Date().getFullYear();
    const currentMonth = new Date().getMonth();
    const date = new Date(currentYear, currentMonth, day);
    
    return room.bookings.some(booking => {
        const start = new Date(booking.check_in);
        const end = new Date(booking.check_out);
        return date >= start && date < end && ['confirmed', 'completed'].includes(booking.status);
    });
};

const getBookingInfo = (room, day) => {
    const currentYear = new Date().getFullYear();
    const currentMonth = new Date().getMonth();
    const date = new Date(currentYear, currentMonth, day);
    
    const booking = room.bookings.find(booking => {
        const start = new Date(booking.check_in);
        const end = new Date(booking.check_out);
        return date >= start && date < end;
    });
    
    return booking ? `${booking.user?.name || 'Guest'}` : '';
};
</script>

<template>
    <div class="space-y-6">
        <div class="bg-white rounded-[2rem] border border-slate-200 shadow-xl shadow-slate-200/50 p-8">
            <h3 class="font-serif font-bold text-2xl text-slate-900 mb-2">{{ $t('occupancy_calendar') }}</h3>
            <p class="text-sm text-slate-500">Daily room availability visualization (Current Month).</p>
        </div>

        <div class="bg-white rounded-[2rem] border border-slate-200 shadow-xl shadow-slate-200/50 overflow-hidden">
            <div class="overflow-x-auto p-4">
                <table class="w-full border-collapse">
                    <thead>
                        <tr>
                            <th class="sticky left-0 bg-white z-10 px-4 py-3 text-left border-b border-slate-100 text-xs font-bold uppercase text-slate-400 min-w-[120px]">Room</th>
                            <th v-for="day in 31" :key="day" class="px-2 py-3 text-center border-b border-slate-100 text-[10px] font-bold text-slate-400 min-w-[40px]">
                                {{ day }}
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="room in calendarData" :key="room.id" class="border-b border-slate-50">
                            <td class="sticky left-0 bg-white z-10 px-4 py-4 text-sm font-bold text-slate-700 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.05)]">
                                {{ room.room_number }} <span class="block text-[10px] font-normal text-slate-400 uppercase">{{ room.room_type?.name }}</span>
                            </td>
                            <td v-for="day in 31" :key="day" class="px-1 py-4 relative">
                                <div v-if="isOccupied(room, day)" class="h-6 w-full rounded-md bg-navy-800 flex items-center justify-center text-[8px] text-gold-400 font-bold overflow-hidden" :title="getBookingInfo(room, day)">
                                    OCC
                                </div>
                                <div v-else class="h-6 w-full rounded-md bg-slate-50 border border-dashed border-slate-200"></div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
