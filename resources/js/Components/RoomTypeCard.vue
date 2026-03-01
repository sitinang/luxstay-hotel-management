<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    roomType: Object,
    index: Number
});
</script>

<template>
    <div 
        class="group bg-white rounded-xl overflow-hidden shadow-lg hover:shadow-2xl transition-all duration-500 border border-stone-100 animate-on-scroll opacity-0 translate-y-10"
        :style="{ transitionDelay: `${index * 150}ms` }"
    >
        <div class="relative h-80 overflow-hidden">
            <img :src="roomType.image" :alt="roomType.name" class="w-full h-full object-cover transition-transform duration-1000 group-hover:scale-110">
            <div class="absolute bottom-4 right-4 bg-navy-900/90 backdrop-blur-md text-white px-6 py-3 rounded-sm">
                <span class="block text-xs text-gray-300 uppercase tracking-wide">{{ $t('starting_from') }}</span>
                <span class="text-xl font-serif font-bold text-gold-400">{{ $formatPrice(roomType.price_per_night) }}</span>
                <span class="text-xs">{{ $t('per_night') }}</span>
            </div>
        </div>

        <div class="p-8">
            <div class="flex justify-between items-start mb-4">
                <h3 class="text-2xl font-serif font-bold text-navy-900 group-hover:text-gold-600 transition-colors">{{ roomType.name }}</h3>
                <div class="flex text-gold-400 text-xs">
                    <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i>
                </div>
            </div>
            
            <p class="text-gray-500 text-sm leading-relaxed mb-6 line-clamp-2">
                {{ roomType.description }}
            </p>
            
            <div class="flex items-center gap-4 text-gray-400 text-sm border-t border-gray-100 pt-4 mb-6">
                <span class="flex items-center gap-1"><i class="fa-solid fa-ruler-combined"></i> 45 m²</span>
                <span class="flex items-center gap-1"><i class="fa-solid fa-user-group"></i> {{ roomType.capacity }} {{ roomType.capacity > 1 ? $t('guests') : $t('guests') }}</span>
                <span class="flex items-center gap-1"><i class="fa-solid fa-wifi"></i> {{ $t('free_wifi') }}</span>
            </div>

            <div class="btn-premium-container btn-navy w-full mt-auto">
                <Link :href="route('rooms.show', roomType.id)" class="btn-premium-inner py-3.5 text-center font-bold text-sm tracking-widest uppercase">
                    {{ $t('detail_booking') }}
                </Link>
            </div>
        </div>
    </div>
</template>
