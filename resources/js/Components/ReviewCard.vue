<script setup>
defineProps({
    review: Object,
    index: Number
});
</script>

<template>
    <div 
        class="bg-white p-10 rounded-[2.5rem] shadow-xl shadow-slate-200/60 border border-white hover:border-gold-200 transition-all duration-500 group animate-on-scroll opacity-0 translate-y-10"
        :style="{ transitionDelay: `${index * 100}ms` }"
    >
        <div class="flex items-center gap-1 text-gold-400 mb-6">
            <i v-for="star in 5" :key="star" 
               :class="star <= review.rating ? 'fa-solid fa-star' : 'fa-regular fa-star'"
               class="text-xs">
            </i>
        </div>

        <div class="relative mb-8">
            <i class="fa-solid fa-quote-left block text-gold-200 text-4xl mb-4 group-hover:text-gold-300 transition-colors"></i>
            <p class="text-navy-900/80 leading-relaxed italic min-h-[80px]">
                "{{ review.comment || $t('default_review_comment') }}"
            </p>
        </div>

        <div class="flex items-center gap-4 pt-6 border-t border-slate-50">
            <div class="w-12 h-12 rounded-full overflow-hidden border-2 border-white shadow-md">
                <img :src="review.user.avatar_url" :alt="review.user.name" class="w-full h-full object-cover" />
            </div>
            <div>
                <h4 class="font-bold text-navy-900">{{ review.user.name }}</h4>
                <p class="text-[10px] text-gold-600 font-black uppercase tracking-widest">{{ review.room_type?.name }}</p>
            </div>
        </div>
    </div>
</template>
