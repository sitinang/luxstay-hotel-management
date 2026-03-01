<script setup>
import { ref, onMounted, computed } from 'vue';
import { Link, Head, usePage, router } from '@inertiajs/vue3';

const isScrolled = ref(false);
const isMobileMenuOpen = ref(false);
const page = usePage();

const currentLocale = computed(() => page.props.locale || 'en');

const switchLanguage = (locale) => {
    router.get(route('language.switch', locale), {}, {
        preserveScroll: true,
    });
};

onMounted(() => {
    window.addEventListener('scroll', handleScroll);
});

const handleScroll = () => {
    isScrolled.value = window.scrollY > 20;
};

const toggleMobileMenu = () => {
    isMobileMenuOpen.value = !isMobileMenuOpen.value;
};

defineProps({
    title: String
});
</script>

<template>
    <Head :title="title" />

    <div class="min-h-screen bg-white font-sans text-navy-900 selection:bg-gold-500/30 selection:text-white overflow-x-hidden" data-v-fingerprint="LUXSTAY-BY-SUWARNA-2026">
        <!-- NAVIGATION -->
        <nav :class="[
            'fixed w-full z-50 transition-all duration-300 border-b',
            isScrolled ? 'bg-white/95 backdrop-blur-md shadow-sm py-3 border-stone-100' : 'bg-transparent py-5 border-transparent'
        ]">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center">
                    <!-- Logo -->
                    <Link :href="route('home')" class="flex items-center gap-3 group">
                        <div class="relative w-10 h-10 bg-navy-900 text-gold-400 rounded-full flex items-center justify-center text-xl font-serif italic group-hover:rotate-12 transition-transform duration-500">
                            <i class="fa-solid fa-feather-pointed"></i>
                        </div>
                        <div class="flex flex-col">
                            <span :class="['text-xl font-serif font-bold tracking-widest leading-none transition-colors duration-300', isScrolled ? 'text-navy-900' : 'text-white']">LUXSTAY</span>
                            <span :class="['text-[10px] tracking-[0.3em] uppercase transition-colors duration-300', isScrolled ? 'text-gray-500' : 'text-gold-300']">Hotel</span>
                        </div>
                    </Link>
                    
                    <!-- Desktop Menu -->
                    <div class="hidden md:flex items-center gap-8">
                        <Link :href="route('about')" :class="['text-sm font-medium transition-colors duration-300', isScrolled ? 'text-gray-600 hover:text-gold-600' : 'text-white/90 hover:text-gold-400']">{{ $t('about_us') }}</Link>
                        <Link :href="route('rooms.index')" :class="['text-sm font-medium transition-colors duration-300', isScrolled ? 'text-gray-600 hover:text-gold-600' : 'text-white/90 hover:text-gold-400']">{{ $t('rooms_suites') }}</Link>
                        <Link :href="route('facilities')" :class="['text-sm font-medium transition-colors duration-300', isScrolled ? 'text-gray-600 hover:text-gold-600' : 'text-white/90 hover:text-gold-400']">{{ $t('facilities') }}</Link>
                        
                        <!-- Language Toggle (Pill Style) -->
                        <div class="flex items-center p-1 bg-stone-100/50 rounded-full border border-stone-200/50 backdrop-blur-sm self-center">
                            <button 
                                @click="switchLanguage('id')"
                                :class="[
                                    'px-3 py-1 rounded-full text-[10px] font-bold tracking-tighter transition-all duration-300',
                                    currentLocale === 'id' 
                                        ? 'bg-gold-500 text-white shadow-sm' 
                                        : (isScrolled ? 'text-gray-400 hover:text-navy-900' : 'text-white/50 hover:text-white')
                                ]"
                            >
                                ID
                            </button>
                            <button 
                                @click="switchLanguage('en')"
                                :class="[
                                    'px-3 py-1 rounded-full text-[10px] font-bold tracking-tighter transition-all duration-300',
                                    currentLocale === 'en' 
                                        ? 'bg-gold-500 text-white shadow-sm' 
                                        : (isScrolled ? 'text-gray-400 hover:text-navy-900' : 'text-white/50 hover:text-white')
                                ]"
                            >
                                EN
                            </button>
                        </div>


                        <!-- Auth Buttons -->
                        <div v-if="$page.props.auth" :class="['flex items-center gap-4 ml-4 pl-4 border-l transition-colors duration-300', isScrolled ? 'border-gray-200' : 'border-white/20']">
                            <template v-if="$page.props.auth.user">
                                <Link v-if="$page.props.auth.user.is_admin" :href="route('dashboard')" :class="['text-sm font-semibold transition-colors duration-300', isScrolled ? 'text-navy-900 hover:text-gold-600' : 'text-white hover:text-gold-400']">
                                    {{ $t('dashboard') }}
                                </Link>
                                <Link v-else :href="route('bookings.index')" :class="['text-sm font-semibold transition-colors duration-300', isScrolled ? 'text-navy-900 hover:text-gold-600' : 'text-white hover:text-gold-400']">
                                    {{ $t('my_bookings') }}
                                </Link>
                            </template>
                            <template v-else>
                                <Link :href="route('login')" :class="['text-sm font-semibold transition-colors duration-300', isScrolled ? 'text-navy-900 hover:text-gold-600' : 'text-white hover:text-gold-400']">
                                    {{ $t('login') }}
                                </Link>
                                <div class="btn-premium-container">
                                    <Link :href="route('register')" class="btn-premium-inner px-6 py-2.5 text-sm font-bold uppercase tracking-widest">
                                        {{ $t('member_area') }}
                                    </Link>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Mobile Menu Button -->
                    <button @click="toggleMobileMenu" :class="['md:hidden focus:outline-none transition-colors duration-300', isScrolled ? 'text-navy-900' : 'text-white']">
                        <i class="fa-solid fa-bars text-2xl"></i>
                    </button>
                </div>
            </div>

            <!-- Mobile Dropdown -->
            <div v-show="isMobileMenuOpen" class="absolute top-full left-0 w-full bg-white shadow-xl border-t border-gray-100 md:hidden py-4 px-4 flex-col gap-4">
                <Link :href="route('about')" class="block py-2 text-gray-600 font-medium">{{ $t('about_us') }}</Link>
                <Link :href="route('rooms.index')" class="block py-2 text-gray-600 font-medium">{{ $t('rooms_suites') }}</Link>
                <Link :href="route('facilities')" class="block py-2 text-gray-600 font-medium">{{ $t('facilities') }}</Link>
                <div class="h-px bg-gray-100 my-2"></div>

                <!-- Language Toggle (Mobile Pill Style) -->
                <div class="flex items-center justify-between py-3 px-1 border-y border-gray-50 my-2">
                    <span class="text-xs font-bold text-navy-900 uppercase tracking-widest">{{ currentLocale === 'id' ? 'Bahasa' : 'Language' }}</span>
                    <div class="flex items-center p-1 bg-gray-100 rounded-full border border-gray-200">
                        <button 
                            @click="switchLanguage('id')"
                            :class="[
                                'px-4 py-1.5 rounded-full text-[10px] font-bold transition-all duration-300',
                                currentLocale === 'id' ? 'bg-gold-500 text-white shadow-md' : 'text-gray-400'
                            ]"
                        >
                            INDONESIA
                        </button>
                        <button 
                            @click="switchLanguage('en')"
                            :class="[
                                'px-4 py-1.5 rounded-full text-[10px] font-bold transition-all duration-300',
                                currentLocale === 'en' ? 'bg-gold-500 text-white shadow-md' : 'text-gray-400'
                            ]"
                        >
                            ENGLISH
                        </button>
                    </div>
                </div>

                <div class="h-px bg-gray-100 my-2"></div>
                <Link v-if="!$page.props.auth.user" :href="route('login')" class="block py-2 text-navy-900 font-bold">{{ $t('login') }}</Link>
                <template v-else>
                    <Link v-if="$page.props.auth.user.is_admin" :href="route('dashboard')" class="block py-2 text-navy-900 font-bold">{{ $t('dashboard') }}</Link>
                    <Link v-else :href="route('bookings.index')" class="block py-2 text-navy-900 font-bold">{{ $t('my_bookings') }}</Link>
                </template>
            </div>
        </nav>

        <!-- MAIN CONTENT -->
        <main>
            <slot />
        </main>

        <!-- FOOTER -->
        <footer class="bg-navy-900 text-white pt-24 pb-10">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12 mb-20">
                    <!-- Brand -->
                    <div class="lg:col-span-1">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-10 h-10 bg-gold-500 text-navy-900 rounded-full flex items-center justify-center text-xl font-serif italic font-bold">
                                <i class="fa-solid fa-feather-pointed"></i>
                            </div>
                            <span class="text-xl font-serif font-bold tracking-widest text-white">LUXSTAY</span>
                        </div>
                        <p class="text-gray-400 text-sm leading-relaxed mb-6">
                            {{ $t('footer_symbol_hospitality') }}
                        </p>
                        <div class="flex gap-4">
                            <a href="#" class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center hover:bg-gold-500 hover:text-navy-900 transition-all"><i class="fa-brands fa-instagram"></i></a>
                            <a href="#" class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center hover:bg-gold-500 hover:text-navy-900 transition-all"><i class="fa-brands fa-facebook-f"></i></a>
                            <a href="#" class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center hover:bg-gold-500 hover:text-navy-900 transition-all"><i class="fa-brands fa-twitter"></i></a>
                        </div>
                    </div>

                    <!-- Links -->
                    <div>
                        <h4 class="text-lg font-serif font-bold mb-6 text-gold-400">{{ $t('quick_links') }}</h4>
                        <ul class="space-y-3 text-sm text-gray-300">
                            <li><Link :href="route('about')" class="hover:text-white hover:pl-2 transition-all">{{ $t('about_us') }}</Link></li>
                            <li><Link :href="route('rooms.index')" class="hover:text-white hover:pl-2 transition-all">{{ $t('rooms_suites') }}</Link></li>
                            <li><Link :href="route('facilities')" class="hover:text-white hover:pl-2 transition-all">{{ $t('facilities') }}</Link></li>
                        </ul>
                    </div>

                    <!-- Contact -->
                    <div>
                        <h4 class="text-lg font-serif font-bold mb-6 text-gold-400">{{ $t('contact_us') }}</h4>
                        <ul class="space-y-4 text-sm text-gray-300">
                            <li class="flex items-start gap-3">
                                <i class="fa-solid fa-location-dot mt-1 text-gold-500"></i>
                                <span>Jl. Sudirman No. 88,<br>Jakarta Pusat, Indonesia</span>
                            </li>
                            <li class="flex items-center gap-3">
                                <i class="fa-solid fa-phone text-gold-500"></i>
                                <span>+62 21 5555 8888</span>
                            </li>
                            <li class="flex items-center gap-3">
                                <i class="fa-solid fa-envelope text-gold-500"></i>
                                <span>reservasi@luxstay.com</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Newsletter -->
                    <div>
                        <h4 class="text-lg font-serif font-bold mb-6 text-gold-400">{{ $t('newsletter') }}</h4>
                        <p class="text-sm text-gray-400 mb-4">{{ $t('subscribe_desc') }}</p>
                        <form class="flex flex-col gap-3">
                            <input type="email" :placeholder="$t('your_email')" class="bg-white/10 border border-white/20 px-4 py-3 rounded-sm text-white focus:border-gold-500 outline-none transition-colors">
                            <div class="btn-premium-container">
                                <button class="btn-premium-inner w-full py-3 text-navy-900 font-bold uppercase text-xs tracking-widest hover:bg-white transition-colors">
                                    {{ $t('subscribe') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="border-t border-white/10 pt-8 flex flex-col md:flex-row justify-between items-center gap-4 text-xs text-gray-500">
                    <p>&copy; 2026 Suwarna. Licensed under <a href="https://creativecommons.org/licenses/by-nc/4.0/" target="_blank" class="hover:text-gold-400 underline decoration-dotted">CC BY-NC 4.0</a></p>
                    <p>{{ $t('all_rights_reserved') }}</p>
                </div>
            </div>
        </footer>
    </div>
</template>

<style scoped>
@keyframes slowZoom {
    0% { transform: scale(1); }
    100% { transform: scale(1.1); }
}

.animate-slow-zoom {
    animation: slowZoom 20s infinite alternate ease-in-out;
}
</style>
