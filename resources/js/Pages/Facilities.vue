<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';
import { onMounted } from 'vue';

const facilities = [
    {
        name: 'Infinity Pool',
        description: 'Rooftop swimming pool with an endless view of the city skyline.',
        image: 'https://images.unsplash.com/photo-1519741497674-611481863552?q=80&w=2070&auto=format&fit=crop',
        icon: 'fa-solid fa-water-ladder'
    },
    {
        name: 'The Royal Spa',
        description: 'Total relaxation with expert therapists and the best international beauty products.',
        image: 'https://images.unsplash.com/photo-1544161515-4ab6ce6db874?q=80&w=2070&auto=format&fit=crop',
        icon: 'fa-solid fa-spa'
    },
    {
        name: 'Sky Lounge & Bar',
        description: 'Enjoy cocktails mixed by our mixologists under the starlit sky.',
        image: 'https://images.unsplash.com/photo-1574096079513-d8259312b785?q=80&w=2070&auto=format&fit=crop',
        icon: 'fa-solid fa-martini-glass-citrus'
    },
    {
        name: 'Signature Restaurant',
        description: 'Fine dining served by Michelin-starred chefs to pamper your palate.',
        image: 'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?q=80&w=2070&auto=format&fit=crop',
        icon: 'fa-solid fa-utensils'
    },
    {
        name: 'Fitness Sanctuary',
        description: 'Fitness center with state-of-the-art equipment and professional personal trainers.',
        image: 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=2070&auto=format&fit=crop',
        icon: 'fa-solid fa-dumbbell'
    },
    {
        name: 'Grand Ballroom',
        description: 'Grand meeting room for weddings or prestigious business meetings.',
        image: 'https://images.unsplash.com/photo-1431540015161-0bf868a2d407?q=80&w=2070&auto=format&fit=crop',
        icon: 'fa-solid fa-handshake'
    }
];

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
    <MainLayout :title="$t('exclusive_facilities')">
        <!-- Header Section -->
        <section class="relative h-[60vh] flex items-center justify-center overflow-hidden">
            <div class="absolute inset-0 z-0">
                <img src="https://images.unsplash.com/photo-1512918728675-ed5a9ecdebfd?q=80&w=2070&auto=format&fit=crop" 
                     class="w-full h-full object-cover scale-105 animate-slow-zoom" 
                     alt="Pool Area">
                <div class="absolute inset-0 bg-navy-900/60"></div>
            </div>
            <div class="relative z-10 text-center text-white space-y-4 px-4">
                <span class="text-gold-400 font-bold tracking-widest uppercase text-sm">{{ $t('unforgettable_experience') }}</span>
                <h1 class="text-5xl lg:text-7xl font-serif font-bold leading-tight">{{ $t('starlit_facilities') }}</h1>
            </div>
        </section>

        <!-- Gallery Section -->
        <section class="py-32 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-24 animate-on-scroll opacity-0 translate-y-10 transition-all duration-700">
                    <h2 class="text-4xl lg:text-5xl font-serif font-bold text-navy-900 mb-6">{{ $t('caring_moments') }}</h2>
                    <p class="text-gray-500 max-w-2xl mx-auto leading-relaxed">
                        {{ $t('facilities_desc') }}
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-12">
                    <div v-for="(facility, index) in facilities" :key="index" 
                         class="group animate-on-scroll opacity-0 translate-y-10 transition-all duration-1000"
                         :style="{ transitionDelay: `${index * 150}ms` }">
                        <div class="relative h-96 overflow-hidden rounded-2xl shadow-lg mb-8">
                            <img :src="facility.image" :alt="facility.name" class="w-full h-full object-cover transition-transform duration-1000 group-hover:scale-110">
                            <div class="absolute inset-0 bg-gradient-to-t from-navy-900/90 via-navy-900/20 to-transparent group-hover:opacity-80 transition-opacity"></div>
                            <div class="absolute bottom-10 left-10 right-10">
                                <div class="w-12 h-12 bg-gold-500 rounded-full flex items-center justify-center text-navy-900 mb-4 shadow-xl">
                                    <i :class="facility.icon"></i>
                                </div>
                                <h3 class="text-2xl font-serif font-bold text-white mb-2">{{ facility.name }}</h3>
                                <p class="text-gray-300 text-sm opacity-0 group-hover:opacity-100 transition-opacity duration-500 leading-relaxed translate-y-4 group-hover:translate-y-0 transition-transform">
                                    {{ facility.description }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Call to Action -->
        <section class="py-24 bg-navy-900 text-center relative overflow-hidden">
            <div class="absolute inset-0 -z-0 opacity-10">
                <div class="absolute top-0 left-0 w-full h-full repeating-lines"></div>
            </div>
            <div class="relative z-10 max-w-4xl mx-auto px-4">
                <h2 class="text-3xl md:text-4xl font-serif font-bold text-white mb-8">{{ $t('ready_for_dream_vacation') }}</h2>
                <div class="btn-premium-container">
                    <Link :href="route('rooms.index')" class="btn-premium-inner px-10 py-4 text-navy-900 font-bold tracking-widest uppercase">
                        {{ $t('book_now') }}
                    </Link>
                </div>
            </div>
        </section>
    </MainLayout>
</template>

<style scoped>
.repeating-lines {
    background: repeating-linear-gradient(
        45deg,
        #B08D22,
        #B08D22 1px,
        transparent 1px,
        transparent 20px
    );
}
</style>
