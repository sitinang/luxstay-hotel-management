<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="min-h-screen flex flex-col items-center justify-center bg-navy-950 px-6 relative overflow-hidden">
        <!-- Background Decorative Elements -->
        <div class="absolute top-0 left-0 w-96 h-96 bg-gold-500 opacity-5 rounded-full blur-[120px] -translate-x-1/2 -translate-y-1/2"></div>
        <div class="absolute bottom-0 right-0 w-96 h-96 bg-gold-400 opacity-5 rounded-full blur-[120px] translate-x-1/2 translate-y-1/2"></div>

        <div class="w-full max-w-md relative z-10">
            <!-- Main Content Card -->
            <div class="bg-white rounded-[2.5rem] shadow-2xl shadow-black/40 overflow-hidden border border-white/10">
                <div class="pt-10 pb-6 px-10 text-center">
                    <Link href="/" class="inline-block transform hover:scale-105 transition-transform duration-300">
                        <ApplicationLogo class="h-16 w-auto" />
                    </Link>
                </div>

                <div class="px-10 pb-12">
                    <slot />
                </div>
            </div>
        </div>
    </div>
</template>
