<script setup>
const props = defineProps({
    roomTypes: Array
});

const emit = defineEmits([
    'openRoomTypeModal',
    'deleteRoomType',
    'openImageModal'
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
            <div class="flex justify-between items-center gap-6">
                <div>
                    <h3 class="font-serif font-bold text-2xl text-slate-900">{{ $t('room_type') }}</h3>
                    <p class="text-sm text-slate-500 mt-1">Manage room types and their facilities for the front page.</p>
                </div>
                <div class="btn-premium-container btn-navy">
                    <button @click="$emit('openRoomTypeModal')" class="btn-premium-inner px-6 py-2.5 text-sm font-bold whitespace-nowrap">
                        <i class="fa-solid fa-plus mr-2 text-gold-400"></i> {{ $t('add_type') }}
                    </button>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div v-for="type in roomTypes" :key="type.id" class="bg-white rounded-3xl border border-slate-200 shadow-md p-6 group">
                <div class="w-full h-40 bg-slate-100 rounded-2xl mb-4 overflow-hidden">
                    <img v-if="type.image" :src="type.image" alt="Room Photo" class="w-full h-full object-cover">
                    <div v-else class="w-full h-full flex items-center justify-center text-slate-400">
                        <i class="fa-regular fa-image text-3xl"></i>
                    </div>
                </div>
                <div class="flex justify-between items-start">
                    <h4 class="font-bold text-lg text-slate-900 font-serif">{{ type.name }}</h4>
                    <div class="flex gap-2">
                        <button @click="$emit('openImageModal', type)" class="w-8 h-8 rounded-full bg-slate-50 text-slate-400 hover:text-navy-900 hover:bg-slate-100 flex items-center justify-center transition-colors" title="Kelola Foto"><i class="fa-solid fa-images"></i></button>
                        <button @click="$emit('openRoomTypeModal', type)" class="w-8 h-8 rounded-full bg-slate-50 text-slate-400 hover:text-navy-900 hover:bg-slate-100 flex items-center justify-center transition-colors"><i class="fa-solid fa-pen-to-square"></i></button>
                        <button @click="$emit('deleteRoomType', type)" class="w-8 h-8 rounded-full bg-slate-50 text-slate-400 hover:text-rose-500 hover:bg-rose-50 flex items-center justify-center transition-colors"><i class="fa-solid fa-trash-can"></i></button>
                    </div>
                </div>
                <p class="text-xs text-slate-500 mt-2 line-clamp-2 my-4">{{ type.description || $t('no_description') }}</p>
                <div class="flex items-center justify-between border-t border-slate-100 pt-4">
                    <div class="flex items-center gap-2 text-sm text-slate-600 font-bold">
                        <i class="fa-solid fa-user-group text-gold-500"></i> {{ type.capacity }} Pax
                    </div>
                    <div class="text-right text-navy-900 font-bold">
                        {{ formatCurrency(type.price_per_night) }} 
                        <span class="text-[10px] text-slate-400 font-normal uppercase">/ {{ $t('night') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
