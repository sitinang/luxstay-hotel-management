<script setup>
import { ref, onMounted, computed } from 'vue';
import MainLayout from '@/Layouts/MainLayout.vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    roomTypes: {
        type: Array,
        default: () => []
    },
    filters: {
        type: Object,
        default: () => ({})
    }
});

import { router } from '@inertiajs/vue3';

// State untuk filter pencarian
const searchForm = ref({
    check_in: props.filters.check_in || '',
    check_out: props.filters.check_out || '',
    guests: props.filters.guests || 1
});

// State untuk kontrol visibilitas form (Funnel Optimization)
const showFullSearch = ref(false);

const handleSearch = () => {
    router.get(route('rooms.index'), searchForm.value, {
        preserveState: true,
        replace: true,
        onSuccess: () => {
            showFullSearch.value = false;
        }
    });
};

import { getCurrentInstance } from 'vue';
const { proxy } = getCurrentInstance();
const $t = proxy.$t;

// State untuk filter kategori (client-side)
const activeFilter = ref('all');
const categoryFilters = computed(() => [
    { id: 'all', label: $t('all_rooms') },
    { id: 'deluxe', label: $t('deluxe') },
    { id: 'suite', label: $t('suite') },
    { id: 'family', label: $t('family') },
    { id: 'standard', label: $t('standard') }
]);

const filteredRoomTypes = computed(() => {
    const list = props.roomTypes || [];
    if (activeFilter.value === 'all') return list;
    
    const searchTerm = activeFilter.value.toLowerCase();
    return list.filter(type => 
        (type.name && type.name.toLowerCase().includes(searchTerm)) ||
        (type.description && type.description.toLowerCase().includes(searchTerm))
    );
});

onMounted(() => {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('opacity-100', 'translate-y-0');
            }
        });
    }, { threshold: 0.1 });

    document.querySelectorAll('.animate-on-scroll').forEach((el) => observer.observe(el));
});
</script>

<template>
    <MainLayout :title="$t('find_your_comfort')">
        
        <!-- HERO SECTION -->
        <section class="relative h-[40vh] lg:h-[50vh] flex items-center justify-center overflow-hidden">
            <div class="absolute inset-0 z-0">
                <img src="https://images.unsplash.com/photo-1590490360182-c33d57733427?q=80&w=1974&auto=format&fit=crop" 
                     class="w-full h-full object-cover scale-105" 
                     alt="Hotel Interior">
                <div class="absolute inset-0 bg-gradient-to-t from-stone-50 via-navy-900/60 to-transparent"></div>
            </div>
            
            <div class="relative z-10 text-center text-white space-y-4 px-4 animate-fade-in-up">
                <h1 class="text-4xl lg:text-6xl font-serif font-bold leading-tight">
                    {{ $t('hero_title').split(' ')[0] }} <span class="italic text-gold-400">{{ $t('hero_title').split(' ')[1] }}</span> {{ $t('hero_title').split(' ').slice(2).join(' ') }}
                </h1>
                <p class="max-w-2xl mx-auto text-gray-200 font-light text-lg">
                    {{ $t('room_list_desc') }}
                </p>
            </div>
        </section>

        <!-- AVAILABILITY CHECK BAR & SUMMARY (Funnel Optimization) -->
        <div class="relative z-50 -mt-8 max-w-6xl mx-auto px-4">
            <!-- Full Form: Shown only when needed -->
            <div v-if="showFullSearch || !props.filters.check_in" class="bg-white rounded-2xl shadow-xl p-6 lg:p-8 border border-stone-100 animate-fade-in-down">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-navy-900 font-bold uppercase tracking-widest text-sm flex items-center gap-2">
                        <i class="fa-solid fa-calendar-alt text-gold-500"></i> {{ $t('search_rooms') }}
                    </h3>
                    <button v-if="props.filters.check_in" @click="showFullSearch = false" class="text-xs font-bold text-gray-400 hover:text-navy-900 uppercase">
                        {{ $t('cancel') }}
                    </button>
                </div>
                <form @submit.prevent="handleSearch" class="grid grid-cols-1 md:grid-cols-4 gap-6 items-end">
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-navy-900 uppercase tracking-widest pl-1">{{ $t('check_in') }}</label>
                        <input type="date" v-model="searchForm.check_in" 
                               class="w-full bg-stone-50 border-stone-200 rounded-xl focus:ring-gold-500 focus:border-gold-500 transition-all">
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-navy-900 uppercase tracking-widest pl-1">{{ $t('check_out') }}</label>
                        <input type="date" v-model="searchForm.check_out" 
                               class="w-full bg-stone-50 border-stone-200 rounded-xl focus:ring-gold-500 focus:border-gold-500 transition-all">
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-navy-900 uppercase tracking-widest pl-1 flex items-center gap-2">{{ $t('guests') }}</label>
                        <select v-model="searchForm.guests" 
                                class="w-full bg-stone-50 border-stone-200 rounded-xl focus:ring-gold-500 focus:border-gold-500 transition-all">
                            <option :value="1">{{ $t('guest_count', { count: 1 }) }}</option>
                            <option :value="2">{{ $t('guests_count', { count: 2 }) }}</option>
                            <option :value="3">{{ $t('guests_count', { count: 3 }) }}</option>
                            <option :value="4">{{ $t('guests_count', { count: 4 }) }}</option>
                            <option :value="5">{{ $t('guests_count', { count: '5+' }) }}</option>
                        </select>
                    </div>
                    <div>
                        <div class="btn-premium-container btn-navy w-full btn-rounded">
                            <button type="submit" 
                                    class="btn-premium-inner w-full font-bold py-3.5 flex items-center justify-center gap-2">
                                <i class="fa-solid fa-magnifying-glass"></i> {{ $t('update_results') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Summary Bar: Shown when user already filtered -->
            <div v-else class="bg-navy-900 text-white rounded-2xl shadow-xl p-4 lg:px-8 flex flex-col sm:flex-row justify-between items-center gap-4 border border-navy-800 animate-fade-in-up">
                <div class="flex items-center gap-6">
                    <div class="flex flex-col">
                        <span class="text-[10px] uppercase tracking-widest text-gold-500 font-bold mb-1">{{ $t('period') }}</span>
                        <div class="flex items-center gap-2 font-medium text-sm">
                            <i class="fa-solid fa-calendar-day text-gold-500"></i>
                            {{ props.filters.check_in }} <i class="fa-solid fa-arrow-right text-[10px] mx-1"></i> {{ props.filters.check_out }}
                        </div>
                    </div>
                    <div class="w-px h-8 bg-white/10 hidden sm:block"></div>
                    <div class="flex flex-col">
                        <span class="text-[10px] uppercase tracking-widest text-gold-500 font-bold mb-1">{{ $t('capacity') }}</span>
                        <div class="flex items-center gap-2 font-medium text-sm">
                            <i class="fa-solid fa-user-group text-gold-500 text-xs"></i>
                            {{ props.filters.guests }} {{ props.filters.guests > 1 ? $t('guests') : $t('guests') }}
                        </div>
                    </div>
                </div>
                <div class="flex gap-4 items-center">
                    <button @click="showFullSearch = true" class="px-6 py-2 bg-white/10 hover:bg-white/20 border border-white/20 rounded-lg text-xs font-bold uppercase transition-all">
                        {{ $t('change_search') }}
                    </button>
                    <button @click="searchForm = {check_in: '', check_out: '', guests: 1}; handleSearch()" class="text-[10px] font-bold text-gray-400 hover:text-white uppercase tracking-widest transition-colors">
                        {{ $t('total_reset') }}
                    </button>
                </div>
            </div>
        </div>

        <!-- STICKY FILTER BAR -->
        <div class="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-gray-100 shadow-sm py-4 mt-8 transition-all duration-300">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row justify-between items-center gap-4">
                <div class="flex items-center gap-2 text-navy-900">
                    <i class="fa-solid fa-filter text-gold-500"></i>
                    <span class="font-bold text-sm uppercase tracking-wider">{{ $t('category') }}:</span>
                </div>
                
                <div class="flex flex-wrap justify-center gap-2">
                    <button 
                        v-for="filter in categoryFilters" 
                        :key="filter.id"
                        @click="activeFilter = filter.id"
                        :class="[
                            'px-5 py-2 rounded-full text-sm font-medium transition-all duration-300',
                            activeFilter === filter.id 
                                ? 'bg-navy-900 text-white shadow-lg' 
                                : 'bg-stone-100 text-gray-500 hover:bg-stone-200'
                        ]"
                    >
                        {{ filter.label }}
                    </button>
                </div>

                <div class="text-sm text-gray-500 hidden md:block">
                    {{ $t('showing_rooms', { count: filteredRoomTypes.length }) }}
                </div>
            </div>
        </div>

        <!-- ROOM LIST SECTION -->
        <section class="py-20 bg-stone-50 min-h-screen">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Filter Status Info -->
                <div v-if="props.filters.check_in" class="mb-12 flex items-center gap-3 bg-white p-4 rounded-2xl border border-stone-100 shadow-sm animate-fade-in">
                    <div class="w-10 h-10 bg-green-50 rounded-full flex items-center justify-center text-green-600">
                        <i class="fa-solid fa-check-circle"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 font-medium">{{ $t('showing_available_period') }}</p>
                        <p class="text-navy-900 font-bold">{{ props.filters.check_in }} — {{ props.filters.check_out }}</p>
                    </div>
                    <button @click="searchForm = {check_in: '', check_out: '', guests: 1}; handleSearch()" 
                            class="ml-auto text-xs font-bold text-red-500 uppercase tracking-widest hover:text-red-700 transition-colors">
                        {{ $t('total_reset') }}
                    </button>
                </div>

                <div v-if="filteredRoomTypes.length > 0" class="flex flex-col gap-16">
                    <div v-for="(type, index) in filteredRoomTypes" :key="type.id" 
                         class="group animate-on-scroll opacity-0 translate-y-10 transition-all duration-1000 ease-out
                                bg-white rounded-[2rem] overflow-hidden shadow-sm hover:shadow-2xl border border-white/50"
                         :style="{ transitionDelay: `${index * 100}ms` }">
                        
                        <div class="flex flex-col lg:flex-row h-full">
                            <!-- Image Side -->
                            <div class="w-full lg:w-5/12 h-80 lg:h-auto relative overflow-hidden">
                                <img :src="type.image" :alt="type.name" class="w-full h-full object-cover transition-transform duration-1000 group-hover:scale-105">
                                <div class="absolute top-6 left-6 flex flex-col gap-2">
                                    <div class="bg-white/90 backdrop-blur-md text-navy-900 px-4 py-2 rounded-lg shadow-lg border border-white/50 flex items-center gap-2 w-fit">
                                        <i class="fa-solid fa-medal text-gold-500 text-xs"></i>
                                        <span class="text-xs font-bold uppercase tracking-wider">{{ $t('popular_badge') }}</span>
                                    </div>
                                    <div v-if="props.filters.check_in" class="bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg border border-green-400/30 flex items-center gap-2 w-fit animate-fade-in">
                                        <i class="fa-solid fa-calendar-check text-xs"></i>
                                        <span class="text-xs font-bold uppercase tracking-wider">{{ $t('available_badge') }}</span>
                                    </div>
                                </div>
                                <div class="lg:hidden absolute bottom-0 left-0 w-full bg-gradient-to-t from-black/80 to-transparent p-6">
                                    <div class="text-white">
                                        <span class="text-xs uppercase tracking-widest opacity-80">{{ $t('starting_from') }}</span>
                                        <div class="text-2xl font-serif font-bold text-gold-400">{{ $formatPrice(type.price_per_night) }} <span class="text-sm font-sans font-normal text-white">/{{ $t('per_night') }}</span></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Content Side -->
                            <div class="w-full lg:w-7/12 p-8 lg:p-12 flex flex-col justify-center">
                                <div class="flex justify-between items-start mb-4">
                                    <div>
                                        <h3 class="text-3xl lg:text-4xl font-serif font-bold text-navy-900 mb-2 group-hover:text-gold-600 transition-colors">
                                            {{ type.name }}
                                        </h3>
                                        <div class="flex items-center gap-1 text-gold-500 text-sm">
                                            <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i>
                                            <span class="text-gray-400 ml-2 font-sans">{{ $t('exclusive_label') }}</span>
                                        </div>
                                    </div>
                                    <div class="hidden lg:block text-right">
                                        <span class="block text-xs text-gray-400 uppercase tracking-widest mb-1">{{ $t('price_per_night_label') }}</span>
                                        <span class="block text-3xl font-serif font-bold text-navy-900">{{ $formatPrice(type.price_per_night) }}</span>
                                        <span class="text-xs text-green-600 font-medium">{{ $t('premium_service') }}</span>
                                    </div>
                                </div>
                                
                                <p class="text-gray-500 leading-relaxed font-light text-lg mb-8 line-clamp-3">
                                    {{ type.description }}
                                </p>

                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-8 border-y border-stone-100 py-6">
                                    <div class="flex items-center gap-3 text-gray-600">
                                        <div class="w-8 h-8 rounded-full bg-stone-100 flex items-center justify-center text-navy-900"><i class="fa-solid fa-user-group text-xs"></i></div>
                                        <span class="text-sm font-medium">{{ type.capacity }} {{ $t('guests') }}</span>
                                    </div>
                                    <div class="flex items-center gap-3 text-gray-600">
                                        <div class="w-8 h-8 rounded-full bg-stone-100 flex items-center justify-center text-navy-900"><i class="fa-solid fa-expand text-xs"></i></div>
                                        <span class="text-sm font-medium">Exclusive</span>
                                    </div>
                                    <div class="flex items-center gap-3 text-gray-600">
                                        <div class="w-8 h-8 rounded-full bg-stone-100 flex items-center justify-center text-navy-900"><i class="fa-solid fa-bed text-xs"></i></div>
                                        <span class="text-sm font-medium">Premium Bed</span>
                                    </div>
                                    <div class="flex items-center gap-3 text-gray-600">
                                        <div class="w-8 h-8 rounded-full bg-stone-100 flex items-center justify-center text-navy-900"><i class="fa-solid fa-wifi text-xs"></i></div>
                                        <span class="text-sm font-medium">Free Wifi</span>
                                    </div>
                                </div>

                                <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mt-auto">
                                    <div class="flex flex-wrap gap-2">
                                        <span v-for="amenity in (type.amenities || []).slice(0, 3)" :key="amenity" class="text-xs text-gray-400 font-medium uppercase tracking-wide">
                                            <i class="fa-solid fa-check text-gold-500 mr-1"></i> {{ amenity }}
                                        </span>
                                    </div>
                                    <div class="flex gap-3 w-full sm:w-auto">
                                        <div class="btn-premium-container btn-navy btn-rounded sm:flex-none">
                                            <Link :href="route('rooms.show', { roomType: type.id, ...props.filters })" class="btn-premium-inner px-6 py-3 text-sm font-bold text-center">
                                                {{ $t('details') }}
                                            </Link>
                                        </div>
                                        <div class="btn-premium-container btn-rounded sm:flex-none">
                                            <Link :href="route('rooms.show', { roomType: type.id, ...props.filters })" class="btn-premium-inner px-8 py-3 text-sm font-bold flex items-center justify-center gap-2">
                                                {{ $t('book_now') }}
                                            </Link>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Empty State -->
                <div v-else class="text-center py-20 bg-white rounded-[2rem] border border-stone-100 shadow-sm">
                    <i class="fa-solid fa-calendar-times text-6xl text-stone-200 mb-6 block"></i>
                    <h3 class="text-2xl font-serif font-bold text-navy-900 mb-2">{{ $t('no_rooms_available_title') }}</h3>
                    <p class="text-gray-500 px-4 max-w-md mx-auto">{{ $t('no_rooms_available_desc') }}</p>
                    <div class="btn-premium-container btn-navy btn-rounded mt-8">
                        <button @click="searchForm = {check_in: '', check_out: '', guests: 1}; handleSearch()" class="btn-premium-inner px-8 py-3 font-bold">
                            {{ $t('see_all_periods_btn') }}
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA SECTION -->
        <section class="py-24 bg-navy-900 relative overflow-hidden">
            <div class="absolute inset-0 opacity-5" style="background-image: radial-gradient(#D4AF37 1px, transparent 1px); background-size: 20px 20px;"></div>
            <div class="max-w-4xl mx-auto px-4 text-center relative z-10">
                <i class="fa-solid fa-crown text-5xl text-gold-500 mb-6 opacity-80"></i>
                <h2 class="text-3xl lg:text-5xl font-serif font-bold text-white mb-6">{{ $t('need_special_offer') }}</h2>
                <p class="text-gray-300 text-lg mb-10 max-w-2xl mx-auto">
                    {{ $t('cta_description') }}
                </p>
                <div class="btn-premium-container btn-rounded bg-white">
                    <a href="tel:+622155558888" class="btn-premium-inner px-10 py-4 bg-white !text-navy-900 font-bold flex items-center gap-3">
                        <i class="fa-solid fa-phone"></i> {{ $t('call_us') }}
                    </a>
                </div>
            </div>
        </section>
    </MainLayout>
</template>

<style>
.animate-fade-in-up {
    animation: fadeInUp 0.8s ease-out forwards;
}
@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>