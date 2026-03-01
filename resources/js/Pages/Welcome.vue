<script setup>
import { onMounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import BookingWidget from '@/Components/BookingWidget.vue';
import RoomTypeCard from '@/Components/RoomTypeCard.vue';
import ReviewCard from '@/Components/ReviewCard.vue';

// --- Props ---
defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
    roomTypes: Array,
    recentReviews: Array,
});

// --- Lifecycle Hooks ---
onMounted(() => {
    animateOnScroll();
});

// --- Methods ---
const animateOnScroll = () => {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('opacity-100', 'translate-y-0');
                entry.target.classList.remove('opacity-0', 'translate-y-10');
            }
        });
    }, { threshold: 0.1 });

    document.querySelectorAll('.animate-on-scroll').forEach((el) => observer.observe(el));
};
</script>

<template>
    <MainLayout :title="$t('welcome_title')">
        <!-- HERO SECTION -->
        <section class="relative h-screen min-h-[700px] flex items-center justify-center overflow-hidden">
            <!-- Background Image -->
            <div class="absolute inset-0 z-0">
                <img src="https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?q=80&w=2070&auto=format&fit=crop" 
                     class="w-full h-full object-cover scale-105 animate-slow-zoom" 
                     alt="Luxury Hotel">
                <div class="absolute inset-0 bg-navy-900/40 bg-gradient-to-t from-navy-900/80 via-navy-900/20 to-transparent"></div>
            </div>

            <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full pt-0">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                    <div class="text-white space-y-8 animate-on-scroll opacity-0 translate-y-10 transition-all duration-1000 ease-out">
                        <div class="inline-flex items-center gap-2 px-4 py-1.5 border border-white/30 rounded-full bg-white/10 backdrop-blur-sm text-sm tracking-widest uppercase text-gold-300">
                            <span class="w-2 h-2 rounded-full bg-gold-400 animate-pulse"></span>
                            {{ $t('premium_choice') }}
                        </div>
                        <h1 class="text-5xl lg:text-7xl font-serif font-bold leading-tight">
                            {{ $t('hero_title').split(' ')[0] }} <br>
                            <span class="italic text-gold-400">{{ $t('hero_title').split(' ')[1] }}</span> <br>
                            {{ $t('hero_title').split(' ').slice(2).join(' ') }}
                        </h1>
                        <p class="text-lg text-gray-200 max-w-lg leading-relaxed font-light border-l-2 border-gold-500 pl-6">
                            {{ $t('hero_subtitle') }}
                        </p>
                        <div class="flex flex-wrap gap-4 pt-4">
                            <div class="btn-premium-container">
                                <a href="#rooms" class="btn-premium-inner px-8 py-4 text-sm font-bold tracking-widest uppercase">
                                    {{ $t('view_rooms') }}
                                </a>
                            </div>
                            <div class="btn-premium-container btn-navy">
                                <Link :href="route('about')" class="btn-premium-inner border border-white/40 px-8 py-4 text-sm font-bold tracking-widest uppercase backdrop-blur-sm">
                                    {{ $t('about_us') }}
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Booking Widget -->
                    <div class="lg:pl-10 animate-on-scroll opacity-0 translate-y-10 transition-all duration-1000 delay-300 ease-out">
                        <BookingWidget />
                    </div>
                </div>
            </div>
        </section>

        <!-- ROOMS SECTION -->
        <section id="rooms" class="py-32 bg-white relative">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-20 animate-on-scroll opacity-0 translate-y-10 transition-all duration-700">
                    <span class="text-gold-600 font-bold tracking-widest text-sm uppercase">{{ $t('exclusive_accommodation') }}</span>
                    <h2 class="text-4xl lg:text-5xl font-serif font-bold text-navy-900 mt-3 mb-6">{{ $t('rooms_suites') }}</h2>
                    <div class="w-24 h-1 bg-gold-500 mx-auto"></div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <RoomTypeCard 
                        v-for="(type, index) in roomTypes" 
                        :key="type.id" 
                        :room-type="type" 
                        :index="index" 
                    />
                </div>

                <div class="text-center mt-16">
                    <Link :href="route('rooms.index')" class="inline-flex items-center gap-2 text-navy-900 font-bold border-b-2 border-gold-500 pb-1 hover:text-gold-600 transition-colors">
                        {{ $t('view_all_rooms') }} <i class="fa-solid fa-arrow-right-long"></i>
                    </Link>
                </div>
            </div>
        </section>

        <!-- REVIEWS SECTION -->
        <section v-if="recentReviews && recentReviews.length > 0" class="py-32 bg-slate-50 relative overflow-hidden">
            <!-- Decorative Elements -->
            <div class="absolute top-0 right-0 w-96 h-96 bg-gold-200/20 rounded-full -mr-48 -mt-48 blur-3xl"></div>
            <div class="absolute bottom-0 left-0 w-96 h-96 bg-navy-200/20 rounded-full -ml-48 -mb-48 blur-3xl"></div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <div class="text-center mb-20 animate-on-scroll opacity-0 translate-y-10 transition-all duration-700">
                    <span class="text-gold-600 font-bold tracking-widest text-sm uppercase">{{ $t('reviews_title') }}</span>
                    <h2 class="text-4xl lg:text-5xl font-serif font-bold text-navy-900 mt-3 mb-6">{{ $t('reviews_subtitle') }}</h2>
                    <div class="w-24 h-1 bg-gold-500 mx-auto"></div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <ReviewCard 
                        v-for="(review, index) in recentReviews" 
                        :key="review.id" 
                        :review="review" 
                        :index="index" 
                    />
                </div>
            </div>
        </section>

        <!-- FEATURES SECTION -->
        <section class="py-32 relative bg-fixed bg-center bg-cover" style="background-image: url('https://images.unsplash.com/photo-1582719508461-905c673771fd?q=80&w=2000&auto=format&fit=crop');">
            <div class="absolute inset-0 bg-navy-900/85"></div>
            <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-16 items-center">
                    <div class="text-white space-y-6 animate-on-scroll opacity-0 translate-y-10 transition-all duration-700">
                        <h2 class="text-4xl font-serif font-bold">More Than Just <br><span class="text-gold-400 italic">a Stay</span></h2>
                        <p class="text-gray-300 leading-relaxed font-light text-lg">
                            {{ $t('luxury_desc') }}
                        </p>
                        <ul class="space-y-4 mt-8">
                            <li class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-full border border-gold-500/50 flex items-center justify-center text-gold-400">
                                    <i class="fa-solid fa-utensils"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-lg">Gourmet Dining</h4>
                                    <p class="text-sm text-gray-400">24-Hour International Chef Service</p>
                                </div>
                            </li>
                            <li class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-full border border-gold-500/50 flex items-center justify-center text-gold-400">
                                    <i class="fa-solid fa-spa"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-lg">Royal Spa</h4>
                                    <p class="text-sm text-gray-400">Traditional & modern body treatments</p>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>
    </MainLayout>
</template>

<style scoped>
</style>