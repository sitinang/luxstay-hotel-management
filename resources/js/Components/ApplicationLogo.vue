<script setup>
defineProps({
    iconOnly: {
        type: Boolean,
        default: false
    }
});
</script>

<template>
    <div class="flex items-center gap-3 group">
        <!-- Logo Icon -->
        <div class="relative w-10 h-10 bg-navy-900 text-gold-400 rounded-full flex items-center justify-center text-xl font-serif italic group-hover:rotate-12 transition-transform duration-500 shadow-lg shadow-navy-900/10">
            <i class="fa-solid fa-feather-pointed"></i>
        </div>
        
        <!-- Logo Text -->
        <div v-if="!iconOnly" class="flex flex-col">
            <span class="text-xl font-serif font-bold tracking-widest leading-none text-navy-900 uppercase">LUXSTAY</span>
            <span class="text-[10px] tracking-[0.3em] uppercase text-gold-600 font-bold">Hotel</span>
        </div>
    </div>
</template>

<style scoped>
.text-navy-900 { color: #0f172a; }
.text-gold-400 { color: #d4af37; }
.text-gold-600 { color: #b8860b; }
.bg-navy-900 { background-color: #0f172a; }
</style>
