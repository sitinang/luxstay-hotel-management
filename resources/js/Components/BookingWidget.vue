<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

const bookingData = ref({
    checkIn: '',
    checkOut: '',
    guests: 1,
    roomType: 'all'
});

const isSearching = ref(false);

const handleBooking = (e) => {
    e.preventDefault();
    isSearching.value = true;
    
    router.get(route('rooms.index'), {
        check_in: bookingData.value.checkIn,
        check_out: bookingData.value.checkOut,
        guests: bookingData.value.guests
    });
};
</script>

<template>
    <div class="bg-white/10 backdrop-blur-md border border-white/20 p-8 rounded-2xl shadow-2xl">
        <div class="flex items-center justify-between mb-6 border-b border-white/20 pb-4">
            <h3 class="text-white font-serif text-2xl">{{ $t('reservation') }}</h3>
            <span class="text-gold-300 text-sm font-bold">{{ $t('best_rate') }}</span>
        </div>
        
        <form @submit="handleBooking" class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div class="group">
                    <label class="block text-gray-300 text-xs uppercase tracking-wider mb-2">{{ $t('check_in') }}</label>
                    <div class="relative">
                        <input type="date" v-model="bookingData.checkIn" required class="w-full bg-white/90 text-navy-900 px-4 py-3 rounded-none border border-stone-300 focus:border-gold-500 focus:ring-0 outline-none transition-colors">
                    </div>
                </div>
                <div>
                    <label class="block text-gray-300 text-xs uppercase tracking-wider mb-2">{{ $t('check_out') }}</label>
                    <div class="relative">
                        <input type="date" v-model="bookingData.checkOut" required class="w-full bg-white/90 text-navy-900 px-4 py-3 rounded-none border border-stone-300 focus:border-gold-500 focus:ring-0 outline-none transition-colors">
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-gray-300 text-xs uppercase tracking-wider mb-2">{{ $t('guests') }}</label>
                <select v-model="bookingData.guests" class="w-full bg-white/90 text-navy-900 px-4 py-3 rounded-none border border-stone-300 focus:border-gold-500 focus:ring-0 outline-none appearance-none">
                    <option value="1">{{ $t('guest_count', { count: 1 }) }}</option>
                    <option value="2">{{ $t('guests_count', { count: 2 }) }}</option>
                    <option value="family">{{ $t('family_room') }}</option>
                </select>
            </div>

            <div class="pt-4">
                <div class="btn-premium-container w-full">
                    <button type="submit" :disabled="isSearching" class="btn-premium-inner w-full py-4 text-navy-900 font-bold tracking-widest uppercase flex items-center justify-center gap-2 group">
                        <span v-if="!isSearching">{{ $t('search_availability') }}</span>
                        <span v-else>{{ $t('searching') }}</span>
                        <i v-if="isSearching" class="fa-solid fa-circle-notch fa-spin"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>
</template>
