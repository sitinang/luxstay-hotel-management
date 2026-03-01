<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, usePage, Link } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted, computed, getCurrentInstance } from 'vue';
import ReviewModal from '@/Components/ReviewModal.vue';

const props = defineProps({
    bookings: Array,
});

const page = usePage();

const showingReviewModal = ref(false);
const selectedBookingForReview = ref(null);

const openReviewModal = (booking) => {
    selectedBookingForReview.value = booking;
    showingReviewModal.value = true;
};

const stats = computed(() => {
    const total = props.bookings.length;
    const active = props.bookings.filter(b => ['pending', 'confirmed'].includes(b.status)).length;
    const spent = props.bookings.filter(b => b.status === 'completed').reduce((acc, b) => acc + Number(b.total_price), 0);
    return { total, active, spent };
});

const loyaltyInfo = computed(() => {
    const points = page.props.auth.user.loyalty_points || 0;
    if (points >= 5000) return { label: 'Platinum Member', color: 'bg-gold-100 text-gold-700', icon: 'fa-crown' };
    if (points >= 1000) return { label: 'Gold Member', color: 'bg-amber-100 text-amber-700', icon: 'fa-medal' };
    return { label: 'Silver Member', color: 'bg-slate-100 text-slate-700', icon: 'fa-award' };
});

const { proxy } = getCurrentInstance();
const formatCurrency = (value) => proxy.$formatPrice(value);

const getStatusColor = (status) => {
    switch (status) {
        case 'confirmed': return 'bg-emerald-50 text-emerald-700 border-emerald-100 shadow-sm shadow-emerald-500/10';
        case 'pending': return 'bg-amber-50 text-amber-700 border-amber-100 shadow-sm shadow-amber-500/10';
        case 'cancelled': return 'bg-rose-50 text-rose-700 border-rose-100 shadow-sm shadow-rose-500/10';
        case 'completed': return 'bg-blue-50 text-blue-700 border-blue-100 shadow-sm shadow-blue-500/10';
        default: return 'bg-slate-50 text-slate-700 border-slate-100';
    }
};

const confirmedToast = ref(null);
const toastVisible = ref(false);


// --- Sound Engine ---
// Uses a SINGLE shared AudioContext to avoid browser Autoplay Policy block.
let _audioCtx = null;

const getAudioContext = () => {
    if (!_audioCtx) {
        _audioCtx = new (window.AudioContext || window.webkitAudioContext)();
    }
    return _audioCtx;
};

const unlockAudio = () => {
    const ctx = getAudioContext();
    if (ctx.state === 'suspended') ctx.resume();
    document.removeEventListener('click', unlockAudio);
    document.removeEventListener('keydown', unlockAudio);
    document.removeEventListener('touchstart', unlockAudio);
};
document.addEventListener('click', unlockAudio);
document.addEventListener('keydown', unlockAudio);
document.addEventListener('touchstart', unlockAudio);

const playStatusSound = (status = 'confirmed') => {
    try {
        const ctx = getAudioContext();

        const doPlay = () => {
            const playTone = (freq, startTime, duration, type = 'sine', vol = 0.45) => {
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.type = type;
                osc.frequency.setValueAtTime(freq, ctx.currentTime + startTime);
                gain.gain.setValueAtTime(vol, ctx.currentTime + startTime);
                gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + startTime + duration);
                osc.start(ctx.currentTime + startTime);
                osc.stop(ctx.currentTime + startTime + duration);
            };

            if (status === 'confirmed') {
                playTone(523, 0.00, 0.25, 'sine');
                playTone(659, 0.15, 0.25, 'sine');
                playTone(784, 0.30, 0.35, 'sine');
                playTone(1047,0.45, 0.45, 'sine', 0.55);
            } else if (status === 'cancelled') {
                playTone(440, 0.00, 0.30, 'triangle', 0.35);
                playTone(330, 0.25, 0.30, 'triangle', 0.30);
                playTone(220, 0.50, 0.40, 'triangle', 0.25);
            } else if (status === 'completed') {
                playTone(880, 0.00, 0.15, 'sine', 0.30);
                playTone(1175,0.12, 0.35, 'sine', 0.35);
            } else {
                playTone(660, 0.00, 0.20, 'sine', 0.30);
            }
        };

        if (ctx.state === 'suspended') {
            ctx.resume().then(doPlay);
        } else {
            doPlay();
        }
    } catch (e) {
        console.warn('Audio playback failed:', e);
    }
};



onMounted(() => {
    const userId = page.props.auth?.user?.id;
    if (window.Echo && userId) {
        window.Echo.channel(`user-${userId}`)
            .listen('.BookingConfirmed', (e) => {
                playStatusSound('confirmed');
                confirmedToast.value = e.booking;
                toastVisible.value = true;
                setTimeout(() => { toastVisible.value = false; }, 8000);
                router.reload({ data: { _t: Date.now() }, only: ['bookings'], preserveScroll: true, preserveState: true });
            });
    }
});

onUnmounted(() => {
    const userId = page.props.auth?.user?.id;
    if (window.Echo && userId) {
        window.Echo.leaveChannel(`user-${userId}`);
    }
});
</script>

<template>
    <Head title="LUXSTAY Hotel" />

    <AuthenticatedLayout>
        <!-- Booking Confirmed Toast -->
        <transition name="toast">
            <div v-if="toastVisible" class="fixed bottom-6 right-6 z-[9999] flex items-start gap-4 bg-navy-900/95 text-white px-6 py-5 rounded-2xl shadow-2xl border border-white/10 backdrop-blur-xl">
                <div class="w-12 h-12 rounded-xl bg-emerald-500/20 flex items-center justify-center flex-shrink-0 animate-pulse">
                    <i class="fa-solid fa-circle-check text-emerald-400 text-2xl"></i>
                </div>
                <div class="space-y-1">
                    <p class="font-bold text-base text-white">{{ $t('booking_confirmed_toast') }}</p>
                    <p class="text-xs text-slate-300 font-medium" v-if="confirmedToast">
                        {{ confirmedToast.room?.room_type?.name }} — {{ $t('room') }} #{{ confirmedToast.room?.room_number }}
                    </p>
                    <p class="text-[10px] text-gold-400 font-bold uppercase tracking-wider mt-2">{{ $t('prepare_arrival_desc') }}</p>
                </div>
                <button @click="toastVisible = false" class="text-white/30 hover:text-white transition-colors">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        </transition>

        <template #header>
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-8 pt-4 pb-2">
                <div class="space-y-1">
                    <div class="flex items-center gap-2 mb-2">
                        <span :class="['px-3 py-1 text-[10px] font-black uppercase tracking-widest rounded-md flex items-center gap-1.5', loyaltyInfo.color]">
                            <i :class="['fa-solid', loyaltyInfo.icon]"></i>
                            {{ loyaltyInfo.label }}
                        </span>
                    </div>
                    <h2 class="font-serif font-bold text-4xl text-navy-900 leading-tight">
                        Halo, {{ $page.props.auth.user.name }}!
                    </h2>
                    <p class="text-slate-500 text-sm font-medium flex items-center gap-2">
                        <i class="fa-solid fa-shield-halved text-navy-400"></i>
                        {{ $t('secure_portal_desc') }}
                    </p>
                </div>

                <!-- Stats Cards -->
                <div class="flex gap-4 sm:gap-6 overflow-x-auto pb-4 lg:pb-0 scrollbar-hide">
                    <div class="bg-white px-6 py-4 rounded-2xl border border-slate-100 shadow-sm flex-shrink-0 min-w-[140px]">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Loyalty Points</p>
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-star text-gold-500 text-xs"></i>
                            <p class="text-2xl font-serif font-bold text-navy-900">{{ $page.props.auth.user.loyalty_points || 0 }}</p>
                        </div>
                    </div>
                    <div class="bg-white px-6 py-4 rounded-2xl border border-slate-100 shadow-sm flex-shrink-0 min-w-[140px]">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">{{ $t('total_bookings') }}</p>
                        <p class="text-2xl font-serif font-bold text-navy-900">{{ stats.total }}</p>
                    </div>
                    <div class="bg-white px-6 py-4 rounded-2xl border border-slate-100 shadow-sm flex-shrink-0 min-w-[140px]">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">{{ $t('active_bookings') }}</p>
                        <p class="text-2xl font-serif font-bold text-emerald-600">{{ stats.active }}</p>
                    </div>
                    <div class="bg-white px-6 py-4 rounded-2xl border-2 border-gold-200 shadow-lg shadow-gold-500/5 flex-shrink-0 min-w-[160px] relative overflow-hidden group/spent">
                        <div class="absolute top-0 right-0 w-16 h-16 bg-gold-50 rounded-full -mr-8 -mt-8 transition-transform group-hover/spent:scale-110"></div>
                        <div class="relative z-10">
                            <p class="text-[10px] font-bold text-gold-600 uppercase tracking-widest mb-1">{{ $t('total_booking_value') }}</p>
                            <p class="text-2xl font-serif font-bold text-navy-900">{{ formatCurrency(stats.spent) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <div class="pt-1 pb-8 bg-slate-50 min-h-[calc(100vh-200px)] relative">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <!-- Empty State Redesign -->
                <div v-if="bookings.length === 0" class="bg-white p-20 rounded-[3rem] shadow-2xl shadow-slate-200/50 text-center space-y-8 border border-white">
                    <div class="relative w-32 h-32 mx-auto">
                        <div class="absolute inset-0 bg-gold-100 rounded-full animate-pulse opacity-40"></div>
                        <div class="relative w-32 h-32 bg-slate-50 rounded-full flex items-center justify-center text-slate-200 border border-slate-100">
                            <i class="fa-solid fa-bed text-5xl"></i>
                        </div>
                    </div>
                    <div class="space-y-3">
                        <h3 class="font-serif font-bold text-3xl text-navy-900">{{ $t('start_journey_title') }}</h3>
                        <p class="text-slate-500 max-w-sm mx-auto font-medium leading-relaxed">{{ $t('no_booking_history_desc') }}</p>
                    </div>
                    <Link :href="route('rooms.index')" class="inline-flex items-center gap-3 px-10 py-5 bg-navy-900 text-white rounded-full font-bold uppercase tracking-widest text-xs hover:bg-gold-500 hover:text-navy-900 transition-all shadow-xl shadow-navy-900/20 active:scale-95 group">
                        {{ $t('search_rooms_now') }} <i class="fa-solid fa-arrow-right group-hover:translate-x-1 transition-transform"></i>
                    </Link>
                </div>

                <!-- Redesigned Booking Cards -->
                <div v-else class="space-y-8">
                    <div v-for="booking in bookings" :key="booking.id" class="group bg-white rounded-[3rem] shadow-2xl shadow-slate-200/40 overflow-hidden border border-white hover:border-gold-200 transition-all duration-700">
                        <div class="flex flex-col lg:flex-row">
                            <!-- Room Image Section -->
                            <div class="lg:w-80 h-56 lg:h-auto relative overflow-hidden flex-shrink-0">
                                <img :src="booking.room.room_type.image" :alt="booking.room.room_type.name" class="w-full h-full object-cover transition-transform duration-1000 group-hover:scale-110">
                                <div class="absolute inset-0 bg-navy-900/20 group-hover:bg-navy-900/0 transition-colors duration-500"></div>
                                <div class="absolute bottom-6 left-6">
                                    <div class="px-5 py-3 bg-white/90 backdrop-blur-md rounded-2xl shadow-xl border border-white/20">
                                        <p class="text-[10px] font-black text-navy-900 uppercase tracking-widest opacity-40">{{ $t('room_number') }}</p>
                                        <p class="text-2xl font-serif font-bold text-navy-900">#{{ booking.room.room_number }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Content Section -->
                            <div class="flex-1 p-8 lg:p-10 flex flex-col justify-between space-y-10">
                                <div class="flex flex-wrap items-start justify-between gap-8">
                                    <div class="space-y-3">
                                        <h3 class="text-3xl lg:text-4xl font-serif font-bold text-navy-900 group-hover:text-gold-600 transition-colors">{{ booking.room.room_type.name }}</h3>
                                        <div class="flex flex-wrap items-center gap-3">
                                            <span :class="['px-5 py-2 rounded-full text-[10px] font-black uppercase tracking-[0.2em] border shadow-sm', getStatusColor(booking.status)]">
                                                {{ $t(booking.status) }}
                                            </span>
                                            <span class="text-xs text-slate-400 font-bold uppercase tracking-widest pl-2 border-l border-slate-100">Booking ID: {{ booking.id }}</span>
                                        </div>
                                    </div>
                                    <div class="lg:text-right flex flex-col items-end gap-3">
                                        <div class="text-right">
                                            <p class="text-[10px] text-slate-400 uppercase font-black tracking-[0.2em] mb-1">{{ $t('transaction_value') }}</p>
                                            <p class="text-3xl lg:text-4xl font-serif font-bold text-navy-900 tracking-tight">{{ formatCurrency(booking.total_price) }}</p>
                                        </div>
                                        
                                        <!-- Guest Actions (Hybrid Flow) -->
                                        <div class="flex flex-wrap gap-3">
                                            <!-- I Have Arrived Button -->
                                            <button 
                                                v-if="booking.status === 'confirmed' && booking.guest_status === 'waiting' && new Date(booking.check_in) <= new Date()"
                                                @click="router.patch(route('bookings.mark-arrived', booking.id))"
                                                class="inline-flex items-center gap-2 px-6 py-3 bg-emerald-500 text-white rounded-2xl font-bold uppercase tracking-widest text-[10px] hover:bg-emerald-600 transition-all shadow-lg active:scale-95"
                                            >
                                                <i class="fa-solid fa-location-dot"></i>
                                                {{ $t('mark_as_arrived') }}
                                            </button>

                                            <!-- Express Check-out Button -->
                                            <button 
                                                v-if="booking.status === 'confirmed' && booking.guest_status === 'arrived'"
                                                @click="router.patch(route('bookings.request-checkout', booking.id))"
                                                class="inline-flex items-center gap-2 px-6 py-3 bg-rose-500 text-white rounded-2xl font-bold uppercase tracking-widest text-[10px] hover:bg-rose-600 transition-all shadow-lg active:scale-95"
                                            >
                                                <i class="fa-solid fa-person-walking-luggage"></i>
                                                {{ $t('express_checkout') }}
                                            </button>

                                            <!-- Status Indicators -->
                                            <div v-if="booking.guest_status === 'arrived'" class="flex items-center gap-2 px-4 py-2 bg-emerald-50 text-emerald-600 rounded-xl text-[10px] font-bold uppercase tracking-widest border border-emerald-100">
                                                <i class="fa-solid fa-door-open"></i>
                                                {{ $t('arrived_status') }}
                                            </div>
                                            <div v-if="booking.guest_status === 'checkout_requested'" class="flex items-center gap-2 px-4 py-2 bg-rose-50 text-rose-600 rounded-xl text-[10px] font-bold uppercase tracking-widest border border-rose-100 italic">
                                                <i class="fa-solid fa-clock-rotate-left"></i>
                                                {{ $t('checkout_requested_status') }}
                                            </div>

                                            <!-- Review Button -->
                                            <button 
                                                v-if="booking.status === 'completed' && !booking.review"
                                                @click="openReviewModal(booking)"
                                                class="inline-flex items-center gap-2 px-6 py-3 bg-gold-500 text-navy-900 rounded-2xl font-bold uppercase tracking-widest text-[10px] hover:bg-navy-900 hover:text-white transition-all shadow-lg shadow-gold-500/10 active:scale-95 group"
                                            >
                                                <i class="fa-solid fa-star text-xs"></i>
                                                {{ $t('give_review') }}
                                            </button>
                                            <div v-else-if="booking.review" class="flex items-center gap-2 px-4 py-2 bg-slate-50 text-slate-400 rounded-xl text-[10px] font-bold uppercase tracking-widest border border-slate-100">
                                                <i class="fa-solid fa-check-circle"></i>
                                                {{ $t('already_reviewed') }}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Timeline Info Grid -->
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-8 py-8 border-y border-slate-50">
                                    <div class="space-y-2">
                                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ $t('set_arrival') }}</p>
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600">
                                                <i class="fa-solid fa-calendar-check text-sm"></i>
                                            </div>
                                            <div>
                                                <p class="text-sm font-black text-navy-900 capitalize">{{ new Date(booking.check_in).toLocaleDateString('en-US', { day: 'numeric', month: 'short', year: 'numeric' }) }}</p>
                                                <p class="text-[10px] text-slate-400 font-bold">{{ $t('check_in') }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="space-y-2">
                                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ $t('set_departure') }}</p>
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-xl bg-rose-50 flex items-center justify-center text-rose-600">
                                                <i class="fa-solid fa-calendar-xmark text-sm"></i>
                                            </div>
                                            <div>
                                                <p class="text-sm font-black text-navy-900 capitalize">{{ new Date(booking.check_out).toLocaleDateString('en-US', { day: 'numeric', month: 'short', year: 'numeric' }) }}</p>
                                                <p class="text-[10px] text-slate-400 font-bold">{{ $t('check_out') }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="space-y-2 hidden md:block">
                                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ $t('stay_duration') }}</p>
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-xl bg-gold-50 flex items-center justify-center text-gold-600">
                                                <i class="fa-solid fa-moon text-sm"></i>
                                            </div>
                                            <div>
                                                <p class="text-sm font-black text-navy-900">
                                                    {{ Math.ceil((new Date(booking.check_out) - new Date(booking.check_in)) / (1000 * 60 * 60 * 24)) }} {{ $t('nights') }}
                                                </p>
                                                <p class="text-[10px] text-slate-400 font-bold">{{ $t('comfortable_rest') }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="space-y-2" v-if="booking.phone">
                                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ $t('contact_info') }}</p>
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600">
                                                <i class="fa-solid fa-phone text-sm"></i>
                                            </div>
                                            <div>
                                                <p class="text-sm font-black text-navy-900">{{ booking.phone }}</p>
                                                <p class="text-[10px] text-slate-400 font-bold">{{ $t('phone_wa') }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Requests with better styling -->
                                <div v-if="booking.special_requests" class="relative group/req">
                                    <div class="absolute inset-0 bg-navy-50 rounded-2xl -rotate-1 group-hover/req:rotate-0 transition-transform duration-500"></div>
                                    <div class="relative p-6 bg-white border border-navy-100 rounded-2xl flex items-start gap-4 shadow-sm">
                                        <div class="w-10 h-10 rounded-full bg-gold-100 flex items-center justify-center text-gold-600 flex-shrink-0">
                                            <i class="fa-solid fa-wand-magic-sparkles text-sm"></i>
                                        </div>
                                        <div class="space-y-1">
                                            <p class="text-[10px] font-black text-navy-600 uppercase tracking-widest">{{ $t('extra_accommodations') }}</p>
                                            <p class="text-sm text-navy-900 italic font-bold leading-relaxed">"{{ booking.special_requests }}"</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <ReviewModal 
            :show="showingReviewModal" 
            :booking="selectedBookingForReview" 
            @close="showingReviewModal = false" 
        />
    </AuthenticatedLayout>
</template>

<style scoped>
.toast-enter-active {
    transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}
.toast-leave-active {
    transition: all 0.3s ease-in;
}
.toast-enter-from,
.toast-leave-to {
    opacity: 0;
    transform: translateX(100%) scale(0.9);
}
</style>
