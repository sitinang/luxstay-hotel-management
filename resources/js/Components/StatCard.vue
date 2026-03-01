<script setup>
defineProps({
    title: String,
    value: [String, Number],
    subValue: String,
    icon: String,
    colorClass: {
        type: String,
        default: 'text-gold-400'
    },
    bgClass: {
        type: String,
        default: 'bg-white/10'
    },
    isDark: {
        type: Boolean,
        default: false
    }
});
</script>

<template>
    <div 
        :class="[
            'p-8 rounded-[2rem] transition-all duration-300 group hover:-translate-y-1',
            isDark 
                ? 'bg-gradient-to-br from-navy-800 to-navy-900 text-white shadow-2xl shadow-navy-900/20 border border-white/10' 
                : 'bg-white border border-slate-200 shadow-xl shadow-slate-200/50 hover:shadow-2xl hover:border-gold-200'
        ]"
    >
        <div v-if="isDark" class="absolute top-0 right-0 -mr-12 -mt-12 w-48 h-48 bg-gold-500 opacity-10 rounded-full blur-3xl group-hover:opacity-20 transition-opacity"></div>
        <div class="relative z-10 flex flex-col h-full justify-between">
            <div class="flex justify-between items-start">
                <div>
                    <p :class="[isDark ? 'text-navy-200' : 'text-slate-500', 'text-sm font-bold uppercase tracking-widest mb-2']">
                        {{ title }}
                    </p>
                    <h3 :class="[isDark ? 'text-gold-400 drop-shadow-sm' : 'text-slate-900', 'text-4xl font-serif font-bold']">
                        {{ value }}
                        <span v-if="subValue" class="text-xl text-slate-400 font-normal">/ {{ subValue }}</span>
                    </h3>
                </div>
                <div :class="[bgClass, 'w-14 h-14 rounded-2xl flex items-center justify-center backdrop-blur-md border border-white/20 shadow-inner group-hover:scale-110 transition-transform']">
                    <i :class="[icon, colorClass, 'text-2xl']"></i>
                </div>
            </div>
            <slot name="footer"></slot>
        </div>
    </div>
</template>
