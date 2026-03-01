<script setup>
const props = defineProps({
    filteredRooms: Array,
    roomSearch: String,
    roomStatusFilter: String,
    roomTypeFilter: String,
    roomTypeNames: Array
});

const emit = defineEmits([
    'update:roomSearch', 
    'update:roomStatusFilter', 
    'update:roomTypeFilter',
    'openRoomModal',
    'deleteRoom',
    'updateRoomStatus'
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
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
                <div>
                    <h3 class="font-serif font-bold text-2xl text-slate-900">{{ $t('room_status') }}</h3>
                    <p class="text-sm text-slate-500 mt-1">Monitor room availability in real-time.</p>
                </div>
                <div class="flex flex-wrap items-center gap-3 w-full lg:w-auto">
                    <div class="relative flex-1 md:flex-initial min-w-[150px]">
                        <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                        <input 
                            :value="roomSearch"
                            @input="$emit('update:roomSearch', $event.target.value)"
                            type="text" 
                            placeholder="No. Kamar..."
                            class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-navy-900 focus:border-navy-900"
                        >
                    </div>
                    <select 
                        :value="roomTypeFilter"
                        @change="$emit('update:roomTypeFilter', $event.target.value)"
                        class="bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-bold text-slate-600 focus:ring-2 focus:ring-navy-900 h-[42px]"
                    >
                        <option value="all">All Types</option>
                        <option v-for="typeName in roomTypeNames" :key="typeName" :value="typeName">{{ typeName }}</option>
                    </select>
                    <select 
                        :value="roomStatusFilter"
                        @change="$emit('update:roomStatusFilter', $event.target.value)"
                        class="bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-bold text-slate-600 focus:ring-2 focus:ring-navy-900 h-[42px]"
                    >
                        <option value="all">{{ $t('all_status') }}</option>
                        <option value="available">{{ $t('room_available') }}</option>
                        <option value="occupied">{{ $t('room_occupied') }}</option>
                        <option value="maintenance">{{ $t('room_maintenance') }}</option>
                    </select>
                    <div class="btn-premium-container btn-navy">
                        <button @click="$emit('openRoomModal')" class="btn-premium-inner px-6 py-2.5 text-sm font-bold whitespace-nowrap">
                            <i class="fa-solid fa-plus mr-2 text-gold-400"></i> {{ $t('add_room') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Room Cards Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
            <div v-for="room in filteredRooms" :key="room.id" class="bg-white rounded-[2.5rem] border border-slate-200 shadow-xl shadow-slate-200/50 hover:shadow-2xl hover:border-gold-300 transition-all duration-500 group relative overflow-hidden hover:-translate-y-2">
                <div class="absolute top-0 right-0 w-32 h-32 bg-slate-50 rounded-full -mr-16 -mt-16 transition-transform group-hover:scale-150 duration-700"></div>
                <div class="p-8 relative z-10">
                    <div class="flex justify-between items-start mb-6">
                        <div class="w-16 h-16 rounded-3xl bg-navy-900 text-gold-400 flex items-center justify-center text-2xl font-serif font-bold shadow-xl shadow-navy-900/20 group-hover:rotate-6 transition-transform">
                            {{ room.room_number }}
                        </div>
                        <div class="flex flex-col items-end gap-2">
                            <div class="flex gap-2">
                                <button @click="$emit('openRoomModal', room)" class="text-slate-400 hover:text-navy-900 transition-colors"><i class="fa-solid fa-pen-to-square"></i></button>
                                <button @click="$emit('deleteRoom', room)" class="text-slate-400 hover:text-rose-500 transition-colors"><i class="fa-solid fa-trash-can"></i></button>
                            </div>
                            <span :class="[
                                'px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-widest border transition-colors',
                                room.status === 'available' ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 
                                room.status === 'occupied' ? 'bg-rose-50 text-rose-600 border-rose-100' : 'bg-amber-50 text-amber-600 border-amber-100'
                            ]">{{ room.status }}</span>
                        </div>
                    </div>
                    
                    <h4 class="font-bold text-slate-800 text-sm mb-1 lowercase"><span class="capitalize">{{ room.room_type?.name }}</span></h4>
                    <div class="flex items-center gap-2 text-xs text-slate-500 mb-4">
                        <i class="fa-solid fa-user-group text-gold-500"></i>
                        <span>Max {{ room.room_type?.capacity }} Person</span>
                    </div>
                    
                    <div class="pt-4 border-t border-slate-50 flex items-center justify-between">
                        <div>
                            <p class="text-xs text-slate-400">{{ $t('price_per_night_label') }}</p>
                            <p class="font-bold text-slate-800 text-sm">{{ formatCurrency(room.room_type?.price_per_night) }}</p>
                        </div>
                        <select 
                            @change="$emit('updateRoomStatus', room, $event.target.value)"
                            class="text-[10px] font-bold uppercase bg-slate-50 border border-slate-200 text-slate-600 rounded-lg py-1.5 px-2 focus:ring-1 focus:ring-navy-900 cursor-pointer hover:bg-navy-50 hover:text-navy-900 transition-colors"
                        >
                            <option value="available" :selected="room.status === 'available'">Available</option>
                            <option value="occupied" :selected="room.status === 'occupied'">Occupied</option>
                            <option value="maintenance" :selected="room.status === 'maintenance'">Maint.</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
