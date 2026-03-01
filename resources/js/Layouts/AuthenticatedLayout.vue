<script setup>
import { ref, computed } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link, usePage, router } from '@inertiajs/vue3';

const props = defineProps({
    minimalHeader: {
        type: Boolean,
        default: false
    }
});

const page = usePage();
const currentLocale = computed(() => page.props.locale || 'en');

const switchLanguage = (locale) => {
    router.get(route('language.switch', locale), {}, {
        preserveScroll: true,
    });
};

const showingNavigationDropdown = ref(false);
</script>

<template>
    <div>
        <div class="min-h-screen bg-slate-50">
            <nav
                class="border-b border-slate-100 bg-white/80 backdrop-blur-md sticky top-0 z-[60]"
            >

                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="flex h-20 justify-between items-center">
                        <div class="flex items-center gap-10">
                            <!-- Logo -->
                            <div class="flex shrink-0 items-center">
                                <Link :href="$page.props.auth.user.is_admin ? route('dashboard') : route('home')" class="group">
                                    <ApplicationLogo />
                                </Link>
                            </div>

                            <!-- Navigation Links -->
                            <div
                                v-if="!minimalHeader"
                                class="hidden space-x-8 sm:flex"
                            >
                                <NavLink
                                    v-if="$page.props.auth.user.is_admin"
                                    :href="route('dashboard')"
                                    :active="route().current('dashboard')"
                                    class="text-sm font-black uppercase tracking-widest"
                                >
                                    Admin Panel
                                </NavLink>
                                <NavLink
                                    :href="route('home')"
                                    :active="route().current('home')"
                                    class="text-sm font-black uppercase tracking-widest"
                                >
                                    Home
                                </NavLink>
                                <NavLink
                                    :href="route('rooms.index')"
                                    :active="route().current('rooms.*')"
                                    class="text-sm font-black uppercase tracking-widest"
                                >
                                    Rooms
                                </NavLink>
                                <NavLink
                                    :href="route('bookings.index')"
                                    :active="route().current('bookings.*')"
                                    class="text-sm font-black uppercase tracking-widest"
                                >
                                    {{ $t('my_reservations') }}
                                </NavLink>
                            </div>
                        </div>

                        <div class="hidden sm:flex sm:items-center sm:ms-6 gap-6">
                            <!-- Language Toggle (Pill Style) -->
                            <div class="flex items-center p-1 bg-slate-100/80 rounded-full border border-slate-200/50 backdrop-blur-sm shadow-inner">
                                <button 
                                    @click="switchLanguage('id')"
                                    :class="[
                                        'px-3 py-1 rounded-full text-[10px] font-black tracking-widest transition-all duration-300',
                                        currentLocale === 'id' ? 'bg-navy-900 text-gold-400 shadow-sm' : 'text-slate-400 hover:text-navy-900'
                                    ]"
                                >
                                    ID
                                </button>
                                <button 
                                    @click="switchLanguage('en')"
                                    :class="[
                                        'px-3 py-1 rounded-full text-[10px] font-black tracking-widest transition-all duration-300',
                                        currentLocale === 'en' ? 'bg-navy-900 text-gold-400 shadow-sm' : 'text-slate-400 hover:text-navy-900'
                                    ]"
                                >
                                    EN
                                </button>
                            </div>

                            <!-- User Dropdown with Premium Styling -->
                            <div class="relative ms-3">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <button
                                            type="button"
                                            class="inline-flex items-center gap-3 rounded-2xl border border-slate-100 bg-slate-50 px-4 py-2 text-sm font-medium leading-4 text-navy-900 transition duration-150 ease-in-out hover:bg-white hover:shadow-md focus:outline-none"
                                        >
                                            <div class="w-8 h-8 rounded-lg bg-navy-900 text-gold-400 flex items-center justify-center font-serif font-bold">
                                                {{ $page.props.auth.user.name.charAt(0) }}
                                            </div>
                                            <span class="font-bold">{{ $page.props.auth.user.name }}</span>

                                            <svg
                                                class="-me-0.5 ms-2 h-4 w-4"
                                                xmlns="http://www.w3.org/2000/svg"
                                                viewBox="0 0 20 20"
                                                fill="currentColor"
                                            >
                                                <path
                                                    fill-rule="evenodd"
                                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                    clip-rule="evenodd"
                                                />
                                            </svg>
                                        </button>
                                    </template>

                                    <template #content>
                                        <div class="px-4 py-3 border-b border-slate-50">
                                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-0.5">{{ $t('registered_account') }}</p>
                                            <p class="text-xs font-bold text-navy-900 truncate">{{ $page.props.auth.user.email }}</p>
                                        </div>
                                        <DropdownLink :href="route('profile.edit')">
                                            <div class="flex items-center gap-2">
                                                <i class="fa-solid fa-user-gear text-navy-400"></i> {{ $t('profile_settings') }}
                                            </div>
                                        </DropdownLink>
                                        <DropdownLink
                                            :href="route('logout')"
                                            method="post"
                                            as="button"
                                            class="hover:text-rose-600"
                                        >
                                            <div class="flex items-center gap-2">
                                                <i class="fa-solid fa-power-off text-rose-400"></i> {{ $t('logout_session') }}
                                            </div>
                                        </DropdownLink>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>

                        <!-- Hamburger -->
                        <div class="-me-2 flex items-center sm:hidden">
                            <button
                                @click="
                                    showingNavigationDropdown =
                                        !showingNavigationDropdown
                                "
                                class="inline-flex items-center justify-center rounded-xl p-3 text-navy-900 transition duration-150 ease-in-out hover:bg-slate-100 focus:outline-none"
                            >
                                <i :class="showingNavigationDropdown ? 'fa-solid fa-xmark' : 'fa-solid fa-bars-staggered'" class="text-xl"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Responsive Navigation Menu -->
                <div
                    v-show="showingNavigationDropdown"
                    class="sm:hidden border-t border-slate-100 bg-white animate-in slide-in-from-top duration-300"
                >
                    <div class="space-y-1 pb-3 pt-2">
                        <ResponsiveNavLink
                            v-if="$page.props.auth.user.is_admin"
                            :href="route('dashboard')"
                            :active="route().current('dashboard')"
                        >
                            Admin Dashboard
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                            :href="route('home')"
                            :active="route().current('home')"
                        >
                            Home
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                            :href="route('rooms.index')"
                            :active="route().current('rooms.*')"
                        >
                            Rooms
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                            :href="route('bookings.index')"
                            :active="route().current('bookings.*')"
                        >
                            My Bookings
                        </ResponsiveNavLink>
                    </div>

                    <!-- Responsive Settings Options -->
                    <div
                        class="border-t border-slate-100 pb-1 pt-4"
                    >
                        <div class="px-4 flex items-center gap-4 mb-4">
                            <div class="w-12 h-12 rounded-2xl bg-navy-900 text-gold-400 flex items-center justify-center text-xl font-serif font-bold">
                                {{ $page.props.auth.user.name.charAt(0) }}
                            </div>
                            <div>
                                <div
                                    class="text-lg font-black text-navy-900"
                                >
                                    {{ $page.props.auth.user?.name }}
                                </div>
                                <div class="text-sm font-medium text-slate-500">
                                    {{ $page.props.auth.user?.email }}
                                </div>
                            </div>
                        </div>

                        <div class="space-y-1">
                            <ResponsiveNavLink :href="route('profile.edit')">
                                Profile Settings
                            </ResponsiveNavLink>

                            <!-- Language Toggle (Mobile Pill Style) -->
                            <div class="px-4 py-4 flex items-center justify-between border-t border-slate-50 mt-2 bg-slate-50/30">
                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">{{ currentLocale === 'id' ? 'BAHASA' : 'LANGUAGE' }}</span>
                                <div class="flex items-center p-1 bg-slate-200/50 rounded-full border border-slate-200">
                                    <button 
                                        @click="switchLanguage('id')"
                                        :class="[
                                            'px-4 py-1.5 rounded-full text-[10px] font-black transition-all duration-300',
                                            currentLocale === 'id' ? 'bg-navy-900 text-gold-400 shadow-md' : 'text-slate-400'
                                        ]"
                                    >
                                        ID
                                    </button>
                                    <button 
                                        @click="switchLanguage('en')"
                                        :class="[
                                            'px-4 py-1.5 rounded-full text-[10px] font-black transition-all duration-300',
                                            currentLocale === 'en' ? 'bg-navy-900 text-gold-400 shadow-md' : 'text-slate-400'
                                        ]"
                                    >
                                        EN
                                    </button>
                                </div>
                            </div>

                            <ResponsiveNavLink
                                :href="route('logout')"
                                method="post"
                                as="button"
                                class="text-rose-600"
                            >
                                Log Out
                            </ResponsiveNavLink>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Heading -->
            <header
                class="bg-white shadow"
                v-if="$slots.header"
            >
                <div class="mx-auto max-w-7xl px-4 pt-4 pb-0 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main>
                <slot />
            </main>
        </div>
    </div>
</template>
