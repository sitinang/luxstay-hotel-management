<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const props = defineProps({
    roomType: Object,
    filters: Object,
    addons: Array,
});

// Defensive coding: check if roomType exists
const roomData = computed(() => props.roomType || {});

const form = useForm({
    room_type_id: props.roomType?.id || '',
    check_in: props.filters?.check_in || '',
    check_out: props.filters?.check_out || '',
    phone: '',
    special_requests: '',
    selected_addons: [], // Array of addon IDs
    coupon_code: '',
});

// Coupon State
const couponLoading = ref(false);
const couponError = ref('');
const appliedCoupon = ref(null);

const applyCoupon = async () => {
    if (!form.coupon_code) return;
    couponLoading.value = true;
    couponError.value = '';
    
    try {
        const response = await axios.post(route('coupons.validate'), { code: form.coupon_code });
        appliedCoupon.value = response.data.coupon;
        couponError.value = '';
    } catch (e) {
        appliedCoupon.value = null;
        couponError.value = e.response?.data?.message || 'Failed to apply coupon.';
    } finally {
        couponLoading.value = false;
    }
};

const gallery = computed(() => {
    const images = props.roomType?.images?.map(img => img.image_path) || [];
    if (props.roomType?.image && !images.includes(props.roomType.image)) {
        images.unshift(props.roomType.image);
    }
    return images;
});

const activeImage = ref(props.roomType?.image || '');

const nights = computed(() => {
    if (!form.check_in || !form.check_out) return 0;
    const start = new Date(form.check_in);
    const end = new Date(form.check_out);
    const diff = end - start;
    return Math.max(0, Math.ceil(diff / (1000 * 60 * 60 * 24)));
});

const addonPrice = computed(() => {
    return props.addons
        .filter(a => form.selected_addons.includes(a.id))
        .reduce((sum, a) => sum + Number(a.price), 0);
});

const subtotal = computed(() => {
    const roomPrice = props.roomType?.price_per_night || 0;
    return (nights.value * roomPrice) + addonPrice.value;
});

const discountAmount = computed(() => {
    if (!appliedCoupon.value) return 0;
    if (appliedCoupon.value.discount_type === 'percent') {
        return subtotal.value * (Number(appliedCoupon.value.discount_amount) / 100);
    }
    return Number(appliedCoupon.value.discount_amount);
});

const totalPrice = computed(() => {
    return Math.max(0, subtotal.value - discountAmount.value);
});

const minCheckOut = computed(() => {
    if (!form.check_in) return '';
    const date = new Date(form.check_in);
    date.setDate(date.getDate() + 1);
    return date.toISOString().split('T')[0];
});

watch(() => form.check_in, (newVal) => {
    if (form.check_out && new Date(form.check_out) <= new Date(newVal)) {
        form.check_out = '';
    }
});

const submit = () => {
    form.post(route('bookings.store'), {
        onSuccess: () => form.reset(),
    });
};
</script>

<template>
    <MainLayout :title="roomType?.name || $t('room_details')">
        <template v-if="roomType">
            <section class="relative h-[50vh] flex items-center justify-center overflow-hidden">
                <div class="absolute inset-0 z-0">
                    <img :src="roomType.image" 
                         class="w-full h-full object-cover scale-105 animate-slow-zoom" 
                         alt="Room Banner">
                    <div class="absolute inset-0 bg-navy-900/50"></div>
                </div>
                <div class="relative z-10 text-center text-white space-y-4">
                    <span class="text-gold-400 font-bold tracking-widest uppercase text-sm">{{ $t('room_details') }}</span>
                    <h1 class="text-5xl lg:text-7xl font-serif font-bold">{{ roomType.name }}</h1>
                </div>
            </section>

            <section class="py-24 bg-white">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-16">
                        <!-- Left Column: Info -->
                        <div class="lg:col-span-2 space-y-12">
                            <div class="space-y-4">
                                <div class="aspect-video rounded-[40px] overflow-hidden shadow-2xl bg-stone-100">
                                    <img :src="activeImage || roomType.image" :alt="roomType.name" class="w-full h-full object-cover transition-all duration-500">
                                </div>
                                <!-- Mini Gallery -->
                                <div class="flex gap-4 overflow-x-auto pb-4 scrollbar-hide">
                                    <button v-for="(img, idx) in gallery" :key="idx" 
                                            @click="activeImage = img"
                                            :class="['w-32 h-20 rounded-2xl overflow-hidden border-2 transition-all flex-shrink-0', activeImage === img ? 'border-gold-500 scale-105 shadow-lg' : 'border-transparent opacity-60 hover:opacity-100']">
                                        <img :src="img" class="w-full h-full object-cover">
                                    </button>
                                </div>
                            </div>

                            <div class="space-y-6">
                                <h2 class="text-4xl font-serif font-bold text-navy-900 border-b border-stone-100 pb-6">{{ $t('description') }}</h2>
                                <p class="text-gray-600 leading-relaxed text-xl font-light">
                                    {{ roomType.description }}
                                </p>
                            </div>

                            <div class="space-y-8">
                                <h2 class="text-4xl font-serif font-bold text-navy-900 border-b border-stone-100 pb-6">{{ $t('room_amenities') }}</h2>
                                <div class="grid grid-cols-2 md:grid-cols-3 gap-6">
                                    <div v-for="amenity in roomType.amenities" :key="amenity" class="flex items-center gap-4 p-4 bg-stone-50 rounded-2xl border border-stone-100 transition-hover hover:border-gold-500/50">
                                        <div class="w-10 h-10 bg-white rounded-full flex items-center justify-center text-gold-600 shadow-sm border border-stone-100">
                                            <i class="fa-solid fa-check text-xs"></i>
                                        </div>
                                        <span class="text-sm font-medium text-gray-700 tracking-wide">{{ amenity }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Room Reviews Section -->
                            <div class="space-y-10 pt-10">
                                <div class="flex items-center justify-between border-b border-stone-100 pb-6">
                                    <h2 class="text-4xl font-serif font-bold text-navy-900">{{ $t('total_reviews') }} ({{ roomType.reviews?.length || 0 }})</h2>
                                    <div v-if="roomType.reviews?.length > 0" class="flex items-center gap-3 bg-gold-50 px-5 py-2 rounded-2xl border border-gold-100 shadow-sm">
                                        <i class="fa-solid fa-star text-gold-500 text-sm"></i>
                                        <span class="text-xl font-serif font-bold text-navy-900">{{ roomType.average_rating }}</span>
                                        <span class="text-xs text-navy-400 font-bold uppercase tracking-widest mt-1">/ 5.0</span>
                                    </div>
                                </div>

                                <div v-if="roomType.reviews?.length > 0" class="space-y-8">
                                    <div v-for="review in roomType.reviews" :key="review.id" class="p-8 bg-stone-50 rounded-[2.5rem] border border-stone-100 transition-all hover:bg-white hover:shadow-xl hover:border-gold-200 group">
                                        <div class="flex items-start justify-between mb-4">
                                            <div class="flex items-center gap-4">
                                                <div class="w-12 h-12 rounded-full overflow-hidden border-2 border-white shadow-sm bg-navy-900 flex items-center justify-center">
                                                    <img v-if="review.user.avatar_url" :src="review.user.avatar_url" :alt="review.user.name" class="w-full h-full object-cover" />
                                                    <span v-else class="text-white font-bold text-lg">{{ review.user.name.charAt(0) }}</span>
                                                </div>
                                                <div>
                                                    <h4 class="font-bold text-navy-900">{{ review.user.name }}</h4>
                                                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">{{ new Date(review.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) }}</p>
                                                </div>
                                            </div>
                                            <div class="flex text-gold-400 gap-0.5">
                                                <i v-for="star in 5" :key="star" :class="star <= review.rating ? 'fa-solid fa-star' : 'fa-regular fa-star'" class="text-[10px]"></i>
                                            </div>
                                        </div>
                                        <p class="text-gray-600 leading-relaxed italic border-l-4 border-gold-300 pl-6 py-1">
                                            "{{ review.comment || $t('no_comment_not_provided') }}"
                                        </p>
                                    </div>
                                </div>
                                <div v-else class="text-center py-20 bg-stone-50 rounded-[3rem] border border-dashed border-stone-200">
                                    <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center mx-auto mb-6 shadow-sm border border-stone-100 text-stone-300">
                                        <i class="fa-solid fa-comment-slash text-2xl"></i>
                                    </div>
                                    <p class="text-stone-400 font-medium italic">{{ $t('no_reviews_yet') }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column: Booking Widget -->
                        <div class="lg:col-span-1">
                            <div class="sticky top-32 bg-white border border-stone-100 shadow-[0_20px_50px_rgba(0,0,0,0.05)] rounded-[40px] p-10">
                                <div class="mb-8 border-b border-stone-100 pb-6">
                                    <h3 class="text-2xl font-serif font-bold text-navy-900 mb-2">{{ $t('booking_details') }}</h3>
                                    <p class="text-sm text-gray-500">{{ $t('booking_desc') }}</p>
                                </div>

                                <form @submit.prevent="submit" class="space-y-6">
                                    <div class="space-y-4">
                                        <div class="grid grid-cols-2 gap-4">
                                            <div class="group">
                                                <label class="block text-navy-900 text-[10px] font-bold uppercase tracking-widest mb-2 pl-1">{{ $t('check_in') }}</label>
                                                <input v-model="form.check_in" type="date" class="w-full bg-stone-50 border border-stone-200 px-4 py-3 rounded-xl text-sm text-navy-900 focus:border-gold-500 focus:ring-0 outline-none transition-all" required>
                                            </div>
                                            <div class="group">
                                                <label class="block text-navy-900 text-[10px] font-bold uppercase tracking-widest mb-2 pl-1">{{ $t('check_out') }}</label>
                                                <input v-model="form.check_out" :min="minCheckOut" type="date" class="w-full bg-stone-50 border border-stone-200 px-4 py-3 rounded-xl text-sm text-navy-900 focus:border-gold-500 focus:ring-0 outline-none transition-all" :disabled="!form.check_in" required>
                                            </div>
                                        </div>

                                        <div class="pt-4 border-t border-stone-50">
                                            <span class="block text-[10px] font-bold text-gold-600 uppercase tracking-[0.2em] mb-4">{{ $t('additional_services') }}</span>
                                            <div class="space-y-3">
                                                <div v-for="addon in addons" :key="addon.id" 
                                                     class="flex items-center justify-between p-3 rounded-xl border border-stone-100 hover:border-gold-300 transition-colors cursor-pointer group"
                                                     @click="form.selected_addons.includes(addon.id) ? form.selected_addons = form.selected_addons.filter(id => id !== addon.id) : form.selected_addons.push(addon.id)">
                                                    <div class="flex items-center gap-3">
                                                        <div :class="['w-5 h-5 rounded border flex items-center justify-center transition-colors', form.selected_addons.includes(addon.id) ? 'bg-gold-500 border-gold-500' : 'border-stone-300 bg-white']">
                                                            <i v-if="form.selected_addons.includes(addon.id)" class="fa-solid fa-check text-[10px] text-white"></i>
                                                        </div>
                                                        <div class="flex flex-col">
                                                            <span class="text-sm font-bold text-navy-900">{{ addon.name }}</span>
                                                            <span class="text-[10px] text-gray-400">{{ $formatPrice(addon.price) }}</span>
                                                        </div>
                                                    </div>
                                                    <i class="fa-solid fa-circle-info text-stone-200 group-hover:text-gold-400 text-xs" :title="addon.description"></i>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="pt-6 border-t border-stone-50">
                                            <span class="block text-[10px] font-bold text-gold-600 uppercase tracking-[0.2em] mb-4">{{ $t('coupon_code') }}</span>
                                            <div class="flex gap-2">
                                                <input v-model="form.coupon_code" type="text" placeholder="COUPON CODE" class="flex-1 bg-stone-50 border border-stone-200 px-4 py-3 rounded-xl text-xs text-navy-900 focus:border-gold-500 focus:ring-0 outline-none uppercase font-black tracking-widest">
                                            <div class="btn-premium-container btn-navy">
                                                <button type="button" @click="applyCoupon" :disabled="couponLoading || !form.coupon_code" class="btn-premium-inner px-6 py-3 text-[10px] font-bold uppercase tracking-widest disabled:opacity-50">
                                                    {{ couponLoading ? '...' : (appliedCoupon ? 'OK' : $t('apply')) }}
                                                </button>
                                            </div>
                                            </div>
                                            <p v-if="couponError" class="mt-2 text-[10px] text-red-500 font-bold"><i class="fa-solid fa-circle-xmark mr-1"></i> {{ couponError }}</p>
                                            <p v-if="appliedCoupon" class="mt-2 text-[10px] text-emerald-600 font-bold"><i class="fa-solid fa-circle-check mr-1"></i> Coupon applied: {{ appliedCoupon.code }}</p>
                                        </div>

                                        <div class="pt-4 border-t border-stone-50">
                                            <span class="block text-[10px] font-bold text-gold-600 uppercase tracking-[0.2em] mb-4">Informasi Tamu</span>
                                            <div class="space-y-4">
                                                <div class="group">
                                                    <label class="block text-navy-900 text-xs font-bold mb-2 pl-1">{{ $t('phone_number') }}</label>
                                                    <input v-model="form.phone" type="tel" placeholder="e.g.: +62..." class="w-full bg-stone-50 border border-stone-200 px-6 py-4 rounded-2xl text-navy-900 focus:border-gold-500 focus:ring-0 outline-none transition-all placeholder-gray-300" required>
                                                </div>
                                                <div class="group">
                                                    <label class="block text-navy-900 text-xs font-bold mb-2 pl-1">{{ $t('special_requests') }}</label>
                                                    <textarea v-model="form.special_requests" rows="3" placeholder="e.g.: extra bed, check-in time..." class="w-full bg-stone-50 border border-stone-200 px-6 py-4 rounded-2xl text-navy-900 focus:border-gold-500 focus:ring-0 outline-none transition-all placeholder-gray-300"></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Summary Calculation -->
                                    <div v-if="nights > 0" class="p-6 bg-gold-50/50 rounded-2xl border border-gold-100 space-y-3 animate-fade-in">
                                        <div class="flex justify-between text-xs">
                                            <span class="text-gray-500">Room ({{ nights }} Night)</span>
                                            <span class="font-bold text-navy-900">{{ $formatPrice(nights * roomType.price_per_night) }}</span>
                                        </div>
                                        <div v-if="addonPrice > 0" class="flex justify-between text-xs">
                                            <span class="text-gray-500">{{ $t('additional_services') }}</span>
                                            <span class="font-bold text-navy-900">{{ $formatPrice(addonPrice) }}</span>
                                        </div>
                                        <div v-if="appliedCoupon" class="flex justify-between text-xs text-emerald-600">
                                            <span>{{ $t('discount') }} ({{ appliedCoupon.code }})</span>
                                            <span class="font-bold">- {{ $formatPrice(discountAmount) }}</span>
                                        </div>
                                        <div class="flex justify-between text-xs border-t border-gold-200 pt-2">
                                            <span class="text-gray-500">{{ $t('tax_service') }}</span>
                                            <span class="font-bold text-navy-900">{{ $formatPrice(totalPrice * 0.1) }}</span>
                                        </div>
                                        <div class="pt-3 border-t border-gold-200 flex justify-between items-center">
                                            <span class="font-bold text-navy-900 uppercase tracking-wider text-xs">{{ $t('total_payment') }}</span>
                                            <span class="text-2xl font-serif font-bold text-gold-600">{{ $formatPrice(totalPrice * 1.1) }}</span>
                                        </div>
                                    </div>

                                    <div v-if="$page.props.flash?.error" class="p-4 bg-red-50 text-red-600 text-xs rounded-xl border border-red-100 flex items-center gap-2">
                                        <i class="fa-solid fa-circle-exclamation"></i> {{ $page.props.flash.error }}
                                    </div>

                                    <div class="btn-premium-container btn-navy w-full">
                                        <button type="submit" 
                                                :disabled="form.processing" 
                                                class="btn-premium-inner w-full py-5 text-white font-bold tracking-[0.2em] uppercase flex items-center justify-center gap-3">
                                            {{ form.processing ? 'Processing...' : $t('continue_booking') }}
                                        </button>
                                    </div>
                                </form>

                                <div class="mt-8 pt-8 border-t border-stone-100 flex flex-col gap-4">
                                    <div class="flex items-center gap-3 text-sm text-gray-500">
                                        <i class="fa-solid fa-shield-halved text-gold-500"></i>
                                        <span>Best Price Guarantee</span>
                                    </div>
                                    <div class="flex items-center gap-3 text-sm text-gray-500">
                                        <i class="fa-solid fa-clock-rotate-left text-gold-500"></i>
                                        <span>Free Cancellation (Terms Apply)</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </template>
        <div v-else class="h-screen flex items-center justify-center">
            <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-gold-500"></div>
        </div>
    </MainLayout>
</template>

<style scoped>
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-fade-in {
    animation: fadeIn 0.5s ease-out forwards;
}
</style>
