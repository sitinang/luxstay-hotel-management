<script setup>
const props = defineProps({
    specialRequests: Array
});

const emit = defineEmits(['clearSpecialRequest']);
</script>

<template>
    <div class="space-y-6">
        <div class="bg-white rounded-[2rem] border border-slate-200 shadow-xl shadow-slate-200/50 p-8">
            <h3 class="font-serif font-bold text-2xl text-slate-900">Special Requests Management</h3>
            <p class="text-sm text-slate-500 mt-1 pb-4">Monitor guest "Special Requests" for service preparation.</p>
            <div v-if="specialRequests.length === 0" class="p-10 text-center text-slate-400">
                <i class="fa-solid fa-face-smile text-5xl mb-4 block"></i>
                <p class="font-bold">{{ $t('all_clear_requests') }}</p>
            </div>
            <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4">
                <div v-for="request in specialRequests" :key="request.id" class="bg-slate-50 rounded-3xl p-6 border border-slate-200 hover:border-gold-300 transition-all">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h4 class="font-bold text-slate-900">{{ request.user?.name }}</h4>
                            <p class="text-xs text-slate-500">Room #{{ request.room?.room_number }} — {{ request.check_in }}</p>
                        </div>
                        <span class="px-3 py-1 bg-amber-100 text-amber-700 text-[10px] font-bold uppercase rounded-full">{{ $t('new_request_badge') }}</span>
                    </div>
                    <div class="bg-white p-4 rounded-2xl border border-slate-100 italic text-slate-600 text-sm mb-4">
                        "{{ request.special_requests }}"
                    </div>
                    <div class="flex gap-2">
                        <button @click="$emit('clearSpecialRequest', request)" class="flex-1 px-4 py-2 bg-navy-900 text-white text-xs font-bold rounded-xl hover:bg-navy-800 transition-all">{{ $t('complete_preparation') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
