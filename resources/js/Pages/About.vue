<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';
import { onMounted } from 'vue';

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
    <MainLayout :title="$t('about_luxstay')">
        <!-- Header Section -->
        <section class="relative min-h-[40vh] lg:h-[60vh] flex items-center justify-center overflow-hidden py-20 lg:py-0">
            <div class="absolute inset-0 z-0">
                <img src="/images/about/hero.png" 
                     class="w-full h-full object-cover scale-105 animate-slow-zoom" 
                     alt="Hotel Architecture">
                <div class="absolute inset-0 bg-navy-900/60"></div>
            </div>
            <div class="relative z-10 text-center text-white space-y-4 pt-10 lg:pt-0 px-4">
                <span class="text-gold-400 font-bold tracking-widest uppercase text-xs sm:text-sm">{{ $t('legacy_of_luxury') }}</span>
                <h1 class="text-4xl sm:text-5xl lg:text-7xl font-serif font-bold">{{ $t('about_us') }}</h1>
            </div>
        </section>

        <!-- Our Story -->
        <section class="py-20 lg:py-32 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-20 items-center">
                    <div class="animate-on-scroll opacity-0 translate-y-10 transition-all duration-1000">
                        <h2 class="text-3xl sm:text-4xl font-serif font-bold text-navy-900 mb-6 lg:mb-8 leading-tight">{{ $t('excellence_since') }} <span class="text-gold-600 italic">1998</span></h2>
                        <div class="space-y-6 text-gray-600 leading-relaxed text-base sm:text-lg font-light">
                            <p>
                                {{ $t('about_description_1') }}
                            </p>
                            <p>
                                {{ $t('about_description_2') }}
                            </p>
                        </div>
                        <div class="grid grid-cols-3 gap-4 sm:gap-8 mt-10 lg:mt-12 pt-8 border-t border-stone-100">
                            <div>
                                <span class="block text-2xl sm:text-3xl font-serif font-bold text-gold-600">25+</span>
                                <span class="text-[10px] sm:text-xs uppercase tracking-widest text-gray-400">{{ $t('years_experience') }}</span>
                            </div>
                            <div>
                                <span class="block text-2xl sm:text-3xl font-serif font-bold text-gold-600">150</span>
                                <span class="text-[10px] sm:text-xs uppercase tracking-widest text-gray-400">{{ $t('luxury_rooms') }}</span>
                            </div>
                            <div>
                                <span class="block text-2xl sm:text-3xl font-serif font-bold text-gold-600">5★</span>
                                <span class="text-[10px] sm:text-xs uppercase tracking-widest text-gray-400">{{ $t('international_rating') }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="relative animate-on-scroll opacity-0 translate-y-10 transition-all duration-1000 delay-300">
                        <div class="aspect-[4/5] rounded-tl-[60px] lg:rounded-tl-[100px] rounded-br-[60px] lg:rounded-br-[100px] overflow-hidden shadow-2xl">
                            <img src="/images/about/story.png" class="w-full h-full object-cover" alt="Hotel Interior">
                        </div>
                        <div class="absolute -bottom-10 -left-10 w-48 sm:w-64 h-48 sm:h-64 bg-navy-900 rounded-tr-[50px] p-6 sm:p-10 text-white flex flex-col justify-center shadow-2xl hidden sm:flex">
                            <span class="text-gold-400 font-serif italic text-4xl mb-4">Quality</span>
                            <p class="text-sm font-light text-gray-400 leading-relaxed uppercase tracking-tighter">{{ $t('quality_quote') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Vision & Mission -->
        <section class="py-20 lg:py-32 bg-stone-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 lg:gap-12">
                    <div class="bg-white p-8 sm:p-16 rounded-2xl shadow-sm border border-stone-100 animate-on-scroll opacity-0 translate-y-10 transition-all duration-700">
                        <div class="w-12 h-12 sm:w-16 sm:h-16 bg-navy-900 rounded-full flex items-center justify-center text-gold-400 mb-6 sm:mb-8 text-xl sm:text-2xl shadow-lg">
                            <i class="fa-solid fa-eye"></i>
                        </div>
                        <h3 class="text-2xl sm:text-3xl font-serif font-bold text-navy-900 mb-4 sm:mb-6">{{ $t('our_mission') }}</h3>
                        <p class="text-gray-500 leading-relaxed text-sm sm:text-base">{{ $t('mission_description') }}</p>
                    </div>
                    <div class="bg-navy-900 p-8 sm:p-16 rounded-2xl shadow-xl animate-on-scroll opacity-0 translate-y-10 transition-all duration-700 delay-200">
                        <div class="w-12 h-12 sm:w-16 sm:h-16 bg-gold-500 rounded-full flex items-center justify-center text-navy-900 mb-6 sm:mb-8 text-xl sm:text-2xl shadow-lg">
                            <i class="fa-solid fa-gem"></i>
                        </div>
                        <h3 class="text-2xl sm:text-3xl font-serif font-bold text-white mb-4 sm:mb-6">{{ $t('core_values') }}</h3>
                        <p class="text-gray-400 leading-relaxed text-sm sm:text-base">{{ $t('values_description') }}</p>
                    </div>
                </div>
            </div>
        </section>
    </MainLayout>
</template>
