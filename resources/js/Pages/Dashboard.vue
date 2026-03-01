<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted, getCurrentInstance } from 'vue';
import Modal from '@/Components/Modal.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { Chart, registerables } from 'chart.js';
import OverviewTab from './Dashboard/Tabs/OverviewTab.vue';
import BookingsTab from './Dashboard/Tabs/BookingsTab.vue';
import RoomsTab from './Dashboard/Tabs/RoomsTab.vue';
import RoomTypesTab from './Dashboard/Tabs/RoomTypesTab.vue';
import FinancialsTab from './Dashboard/Tabs/FinancialsTab.vue';
import CalendarTab from './Dashboard/Tabs/CalendarTab.vue';
import RequestsTab from './Dashboard/Tabs/RequestsTab.vue';
Chart.register(...registerables);

const props = defineProps({
    stats: Object,
    recentBookings: Array,
    allBookings: Array,
    allRooms: Array,
    roomTypes: Array,
    transactions: Array,
    financialSummary: Object,
    chartData: Object,
    calendarData: Array,
    specialRequests: Array,
});

const { proxy } = getCurrentInstance();
const $t = (key) => proxy.$t(key);

const activeTab = ref('overview');

// Filter States
const bookingSearch = ref('');
const bookingStatusFilter = ref('all');
const roomSearch = ref('');
const roomStatusFilter = ref('all');
const roomTypeFilter = ref('all');

const filteredBookings = computed(() => {
    return props.allBookings.filter(booking => {
        const matchesSearch = booking.user?.name?.toLowerCase().includes(bookingSearch.value.toLowerCase()) || 
                             booking.room?.room_number?.toString().includes(bookingSearch.value);
        const matchesStatus = bookingStatusFilter.value === 'all' || booking.status === bookingStatusFilter.value;
        return matchesSearch && matchesStatus;
    });
});

const filteredRooms = computed(() => {
    return props.allRooms.filter(room => {
        const matchesSearch = room.room_number?.toString().includes(roomSearch.value);
        const matchesStatus = roomStatusFilter.value === 'all' || room.status === roomStatusFilter.value;
        const matchesType = roomTypeFilter.value === 'all' || room.room_type?.name === roomTypeFilter.value;
        return matchesSearch && matchesStatus && matchesType;
    });
});

const roomTypeNames = computed(() => {
    const types = props.allRooms.map(r => r.room_type?.name).filter(Boolean);
    return [...new Set(types)];
});

const formatCurrency = (value) => proxy.$formatPrice(value);   // For room prices (USD base → converts to IDR)
const formatMoney = (value) => proxy.$formatMoney(value);         // For financial amounts (stored as-is, no rate)

const roomStatusForm = useForm({
    status: '',
});

const bookingStatusForm = useForm({
    status: '',
});

const updateRoomStatus = (room, status) => {
    roomStatusForm.status = status;
    roomStatusForm.patch(route('dashboard.rooms.status', room.id), {
        preserveScroll: true,
    });
};

const updateBookingStatus = (booking, status) => {
    bookingStatusForm.status = status;
    playStatusSound(status);
    bookingStatusForm.patch(route('dashboard.bookings.status', booking.id), {
        preserveScroll: true,
    });
};

// --- Room Types Logic ---
const showRoomTypeModal = ref(false);
const editingRoomType = ref(false);
const currentRoomType = ref(null);

const roomTypeForm = useForm({
    name: '',
    description: '',
    price_per_night: '',
    capacity: '',
    image: null,
    _method: 'post', // Default to post
});

const openRoomTypeModal = (type = null) => {
    editingRoomType.value = !!type;
    currentRoomType.value = type;
    
    if (type) {
        roomTypeForm.name = type.name;
        roomTypeForm.description = type.description || '';
        roomTypeForm.price_per_night = type.price_per_night;
        roomTypeForm.capacity = type.capacity;
        roomTypeForm.image = null; // Don't pre-fill file input
        roomTypeForm._method = 'put'; // Spoof PUT
    } else {
        roomTypeForm.reset();
        roomTypeForm._method = 'post';
    }
    showRoomTypeModal.value = true;
};

const closeRoomTypeModal = () => {
    showRoomTypeModal.value = false;
    roomTypeForm.reset();
    roomTypeForm.clearErrors();
};

const submitRoomType = () => {
    const isEditing = editingRoomType.value;
    const url = isEditing 
        ? route('dashboard.room-types.update', currentRoomType.value.id)
        : route('dashboard.room-types.store');

    // For file uploads, even if it's an update, we must use POST and let Inertia spoof PUT
    roomTypeForm.post(url, {
        preserveScroll: true,
        onSuccess: () => closeRoomTypeModal(),
    });
};

const deleteRoomType = (type) => {
    if(confirm('Are you sure you want to delete this room type? All rooms with this type will also be deleted!')) {
        router.delete(route('dashboard.room-types.destroy', type.id), {
            preserveScroll: true
        });
    }
};

// --- Rooms Logic ---
const showRoomModal = ref(false);
const editingRoom = ref(false);
const currentRoom = ref(null);

const roomForm = useForm({
    room_number: '',
    room_type_id: '',
    status: 'available',
    _method: 'post',
});

const openRoomModal = (room = null) => {
    editingRoom.value = !!room;
    currentRoom.value = room;

    if (room) {
        roomForm.room_number = room.room_number;
        roomForm.room_type_id = room.room_type_id;
        roomForm.status = room.status;
        roomForm._method = 'put';
    } else {
        roomForm.reset();
        roomForm.status = 'available';
        roomForm._method = 'post';
    }
    showRoomModal.value = true;
};

const closeRoomModal = () => {
    showRoomModal.value = false;
    roomForm.reset();
    roomForm.clearErrors();
};

const submitRoom = () => {
    const isEditing = editingRoom.value;
    const url = isEditing 
        ? route('dashboard.rooms.update', currentRoom.value.id)
        : route('dashboard.rooms.store');

    roomForm.post(url, {
        preserveScroll: true,
        onSuccess: () => closeRoomModal(),
    });
};

const deleteRoom = (room) => {
    if(confirm('Are you sure you want to delete this room?')) {
        router.delete(route('dashboard.rooms.destroy', room.id), {
            preserveScroll: true
        });
    }
};

// --- Room Type Images Logic ---
const showImageModal = ref(false);
const currentImageType = ref(null);
const imageForm = useForm({
    image: null,
});

const openImageModal = (type) => {
    currentImageType.value = type;
    showImageModal.value = true;
};

const closeImageModal = () => {
    showImageModal.value = false;
    currentImageType.value = null;
    imageForm.reset();
    imageForm.clearErrors();
};

const uploadImage = () => {
    if(!imageForm.image) return;
    imageForm.post(route('dashboard.room-types.images.store', currentImageType.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            imageForm.reset('image');
            // Find and update currentImageType from the updated props.roomTypes
            // Inertia automatically updates the props, so the images array might be updated
            // We'll rely on Vue reactivity holding the object reference or we can just fetch it again if needed.
            // But since props.roomTypes is updated, the view will update.
        },
    });
};

const deleteImage = (imageId) => {
    if(confirm('Delete this photo?')) {
        router.delete(route('dashboard.room-types.images.destroy', imageId), {
            preserveScroll: true,
        });
    }
};

const setThumbnail = (imageId) => {
    router.patch(route('dashboard.room-types.images.thumbnail', imageId), {}, {
        preserveScroll: true,
    });
};

// --- Special Request Logic ---
const clearSpecialRequest = (booking) => {
    router.patch(route('dashboard.bookings.clear-request', booking.id), {}, {
        preserveScroll: true,
    });
};

// --- Financial Transactions Logic ---
const showTransactionModal = ref(false);
const transactionForm = useForm({
    type: 'expense',
    category: '',
    amount: '',
    description: '',
});

const openTransactionModal = () => {
    transactionForm.reset();
    showTransactionModal.value = true;
};

const closeTransactionModal = () => {
    showTransactionModal.value = false;
    transactionForm.reset();
    transactionForm.clearErrors();
};

const submitTransaction = () => {
    transactionForm.post(route('dashboard.transactions.store'), {
        preserveScroll: true,
        onSuccess: () => closeTransactionModal(),
    });
};

// --- Calendar Logic ---
const isOccupied = (room, day) => {
    const currentYear = new Date().getFullYear();
    const currentMonth = new Date().getMonth();
    const date = new Date(currentYear, currentMonth, day);
    
    return room.bookings.some(booking => {
        const start = new Date(booking.check_in);
        const end = new Date(booking.check_out);
        return date >= start && date < end && ['confirmed', 'completed'].includes(booking.status);
    });
};

const getBookingInfo = (room, day) => {
    const currentYear = new Date().getFullYear();
    const currentMonth = new Date().getMonth();
    const date = new Date(currentYear, currentMonth, day);
    
    const booking = room.bookings.find(booking => {
        const start = new Date(booking.check_in);
        const end = new Date(booking.check_out);
        return date >= start && date < end;
    });
    
    return booking ? `${booking.user?.name || 'Guest'}` : '';
};

// --- Real-time Notifications ---
const newBookingToast = ref(null);
const toastVisible = ref(false);

// --- Sound Engine ---
// Uses a single shared AudioContext to avoid browser Autoplay Policy issues.
// The context is unlocked on first user interaction so it's ready for WebSocket events.
let _audioCtx = null;

const getAudioContext = () => {
    if (!_audioCtx) {
        _audioCtx = new (window.AudioContext || window.webkitAudioContext)();
    }
    return _audioCtx;
};

// Unlock the AudioContext proactively on first user interaction
const unlockAudio = () => {
    const ctx = getAudioContext();
    if (ctx.state === 'suspended') {
        ctx.resume();
    }
    // Only need to do this once
    document.removeEventListener('click', unlockAudio);
    document.removeEventListener('keydown', unlockAudio);
    document.removeEventListener('touchstart', unlockAudio);
};

document.addEventListener('click', unlockAudio);
document.addEventListener('keydown', unlockAudio);
document.addEventListener('touchstart', unlockAudio);

const playStatusSound = (status = 'pending') => {
    try {
        const ctx = getAudioContext();

        // Resume if suspended (e.g. background tab)
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

            if (status === 'pending') {
                // NEW ORDER: Urgent triple buzz
                playTone(880,  0.00, 0.15, 'square', 0.35);
                playTone(1100, 0.18, 0.15, 'square', 0.35);
                playTone(880,  0.36, 0.15, 'square', 0.35);
            } else if (status === 'confirmed') {
                // CONFIRMED: Triumphant ascending major chord
                playTone(523, 0.00, 0.25, 'sine');
                playTone(659, 0.15, 0.25, 'sine');
                playTone(784, 0.30, 0.35, 'sine');
                playTone(1047,0.45, 0.45, 'sine', 0.55);
            } else if (status === 'cancelled') {
                // CANCELLED: Low descending sad tones
                playTone(440, 0.00, 0.30, 'triangle', 0.35);
                playTone(330, 0.25, 0.30, 'triangle', 0.30);
                playTone(220, 0.50, 0.40, 'triangle', 0.25);
            } else if (status === 'completed') {
                // COMPLETED: Gentle success ding
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



let revenueChart = null;
let occupancyChart = null;
let roomTypeChart = null;

const initCharts = () => {
    // ... within initCharts ...
    const revenueChartElem = document.getElementById('revenueChart');
    if (revenueChartElem) {
        if (revenueChart) revenueChart.destroy();
        revenueChart = new Chart(revenueChartElem, {
            type: 'bar',
            data: {
                labels: props.chartData.monthlyRevenue.map(d => d.month),
                datasets: [{
                    label: 'Pendapatan (Rp)',
                    data: props.chartData.monthlyRevenue.map(d => d.revenue),
                    backgroundColor: '#B8860B', // Gold
                    borderRadius: 8,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { 
                        beginAtZero: true, 
                        ticks: { 
                            callback: value => 'Rp ' + value.toLocaleString('id-ID')
                        } 
                    }
                }
            }
        });
    }

    const occupancyChartElem = document.getElementById('occupancyChart');
    if (occupancyChartElem) {
        if (occupancyChart) occupancyChart.destroy();
        occupancyChart = new Chart(occupancyChartElem, {
            type: 'line',
            data: {
                labels: props.chartData.dailyOccupancy.map(d => d.date),
                datasets: [{
                    label: $t('room_occupied_count'),
                    data: props.chartData.dailyOccupancy.map(d => d.count),
                    borderColor: '#0A1128', // Navy
                    backgroundColor: 'rgba(10, 17, 40, 0.1)',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 0,
                    pointHoverRadius: 6,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1 } }
                }
            }
        });
    }

    const roomTypeChartRef = document.getElementById('roomTypeChart');
    if (roomTypeChartRef) {
        if (roomTypeChart) roomTypeChart.destroy();
        roomTypeChart = new Chart(roomTypeChartRef, {
            type: 'doughnut',
            data: {
                labels: props.chartData.roomTypeFavorites.map(d => d.name),
                datasets: [{
                    data: props.chartData.roomTypeFavorites.map(d => d.booking_count),
                    backgroundColor: ['#B8860B', '#0A1128', '#1E293B', '#475569', '#94A3B8'],
                    borderWidth: 0,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 12, usePointStyle: true } }
                }
            }
        });
    }
};

onMounted(() => {
    initCharts();
    if (window.Echo) {
        window.Echo.channel('admin-notifications')
            .listen('.BookingCreated', (e) => {
                // Play unique alert sound for new incoming order
                playStatusSound('pending');

                // Show toast notification
                newBookingToast.value = e.booking;
                toastVisible.value = true;
                setTimeout(() => { toastVisible.value = false; }, 6000);
                
                // Refresh dashboard data to show the new booking
                router.reload({ 
                    data: { _t: Date.now() },
                    only: ['stats', 'recentBookings', 'allBookings', 'chartData'],
                    preserveScroll: true,
                    preserveState: true,
                    onSuccess: () => {
                        // Re-init charts with new data
                        setTimeout(initCharts, 100);
                    }
                });
            });

        window.Echo.channel('admin-notifications')
            .listen('.GuestArrived', (e) => {
                playStatusSound('completed');
                newBookingToast.value = e.booking;
                newBookingToast.value.isGuestAction = 'arrived';
                toastVisible.value = true;
                setTimeout(() => { toastVisible.value = false; }, 8000);
                router.reload({ data: { _t: Date.now() }, only: ['allBookings', 'recentBookings', 'stats'], preserveScroll: true, preserveState: true });
            })
            .listen('.CheckoutRequested', (e) => {
                playStatusSound('cancelled');
                newBookingToast.value = e.booking;
                newBookingToast.value.isGuestAction = 'checkout';
                toastVisible.value = true;
                setTimeout(() => { toastVisible.value = false; }, 8000);
                router.reload({ data: { _t: Date.now() }, only: ['allBookings', 'recentBookings', 'stats'], preserveScroll: true, preserveState: true });
            });
    }
});

onUnmounted(() => {
    if (revenueChart) revenueChart.destroy();
    if (occupancyChart) occupancyChart.destroy();
    if (window.Echo) {
        window.Echo.channel('admin-notifications').stopListening('.BookingCreated');
        window.Echo.leaveChannel('admin-notifications');
    }
});

</script>

<template>
    <Head title="Admin Dashboard" />

    <AuthenticatedLayout :minimalHeader="true">
        <transition name="toast">
            <div v-if="toastVisible" class="fixed bottom-6 right-6 z-[9999] flex items-start gap-4 bg-navy-900 text-white px-6 py-4 rounded-2xl shadow-2xl shadow-navy-900/30 max-w-sm border border-white/10 backdrop-blur-sm">
                <div v-if="newBookingToast?.isGuestAction === 'arrived'" class="w-10 h-10 rounded-xl bg-emerald-400/20 flex items-center justify-center flex-shrink-0 mt-0.5">
                    <i class="fa-solid fa-location-dot text-emerald-400 text-lg animate-bounce"></i>
                </div>
                <div v-else-if="newBookingToast?.isGuestAction === 'checkout'" class="w-10 h-10 rounded-xl bg-rose-400/20 flex items-center justify-center flex-shrink-0 mt-0.5">
                    <i class="fa-solid fa-person-walking-luggage text-rose-400 text-lg animate-bounce"></i>
                </div>
                <div v-else class="w-10 h-10 rounded-xl bg-gold-400/20 flex items-center justify-center flex-shrink-0 mt-0.5">
                    <i class="fa-solid fa-bell text-gold-400 text-lg animate-bounce"></i>
                </div>

                <div>
                    <p class="font-bold text-sm mb-0.5" v-if="newBookingToast?.isGuestAction === 'arrived'">{{ $t('arrival_notified') }}!</p>
                    <p class="font-bold text-sm mb-0.5" v-else-if="newBookingToast?.isGuestAction === 'checkout'">{{ $t('checkout_requested_msg') }}!</p>
                    <p class="font-bold text-sm mb-0.5" v-else>{{ $t('new_order_toast') }}</p>
                    
                    <p class="text-xs text-navy-200" v-if="newBookingToast">
                        {{ newBookingToast.user?.name }} — Room #{{ newBookingToast.room?.room_number || newBookingToast.room_id }}
                    </p>
                    <p class="text-xs text-navy-300 mt-1" v-if="!newBookingToast?.isGuestAction">{{ $t('waiting_confirmation') }}</p>
                </div>
                <button @click="toastVisible = false" class="text-white/40 hover:text-white ml-2 flex-shrink-0">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        </transition>

        <div class="flex bg-slate-50 overflow-hidden font-sans text-slate-800" style="height: calc(100vh - 65px);">
            
            <!-- SIDEBAR (Pengganti Topbar) -->
            <aside class="w-64 bg-white border-r border-slate-200 flex-shrink-0 flex flex-col hidden md:flex z-20 pb-4">
                <!-- Navigation Links -->
                <nav class="flex-1 overflow-y-auto py-6 px-4 space-y-1">
                    <button 
                        @click="activeTab = 'overview'" 
                        :class="[
                            'w-full flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200',
                            activeTab === 'overview' 
                                ? 'bg-navy-50 text-navy-900 shadow-sm' 
                                : 'text-slate-500 hover:bg-slate-50 hover:text-navy-900'
                        ]"
                    >
                        <i class="fa-solid fa-chart-line text-lg w-6 text-center"></i>
                        {{ $t('dashboard_overview') }}
                    </button>
                    <button 
                        @click="activeTab = 'bookings'" 
                        :class="[
                            'w-full flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200',
                            activeTab === 'bookings' 
                                ? 'bg-navy-50 text-navy-900 shadow-sm' 
                                : 'text-slate-500 hover:bg-slate-50 hover:text-navy-900'
                        ]"
                    >
                        <i class="fa-solid fa-calendar-check text-lg w-6 text-center"></i>
                        {{ $t('order_management') }}
                        <span v-if="stats.active_bookings > 0" class="ml-auto bg-rose-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">{{ stats.active_bookings }}</span>
                    </button>
                    <button 
                        @click="activeTab = 'rooms'" 
                        :class="[
                            'w-full flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200',
                            activeTab === 'rooms' 
                                ? 'bg-navy-50 text-navy-900 shadow-sm' 
                                : 'text-slate-500 hover:bg-slate-50 hover:text-navy-900'
                        ]"
                    >
                        <i class="fa-solid fa-door-closed text-lg w-6 text-center"></i>
                        {{ $t('room_status') }}
                    </button>
                    <button 
                        @click="activeTab = 'roomTypes'" 
                        :class="[
                            'w-full flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200',
                            activeTab === 'roomTypes' 
                                ? 'bg-navy-50 text-navy-900 shadow-sm' 
                                : 'text-slate-500 hover:bg-slate-50 hover:text-navy-900'
                        ]"
                    >
                        <i class="fa-solid fa-layer-group text-lg w-6 text-center"></i>
                        {{ $t('room_type') }}
                    </button>
                    <button 
                        @click="activeTab = 'financials'" 
                        :class="[
                            'w-full flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200',
                            activeTab === 'financials' 
                                ? 'bg-navy-50 text-navy-900 shadow-sm' 
                                : 'text-slate-500 hover:bg-slate-50 hover:text-navy-900'
                        ]"
                    >
                        <i class="fa-solid fa-file-invoice-dollar text-lg w-6 text-center"></i>
                        {{ $t('financial_reports') }}
                    </button>
                    <button 
                        @click="activeTab = 'calendar'" 
                        :class="[
                            'w-full flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200',
                            activeTab === 'calendar' 
                                ? 'bg-navy-50 text-navy-900 shadow-sm' 
                                : 'text-slate-500 hover:bg-slate-50 hover:text-navy-900'
                        ]"
                    >
                        <i class="fa-solid fa-calendar-days text-lg w-6 text-center"></i>
                        {{ $t('occupancy_calendar') }}
                    </button>
                    <button 
                        @click="activeTab = 'requests'" 
                        :class="[
                            'w-full flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200',
                            activeTab === 'requests' 
                                ? 'bg-navy-50 text-navy-900 shadow-sm' 
                                : 'text-slate-500 hover:bg-slate-50 hover:text-navy-900'
                        ]"
                    >
                        <i class="fa-solid fa-bell-concierge text-lg w-6 text-center"></i>
                        {{ $t('guest_requests') }}
                        <span v-if="specialRequests.length > 0" class="ml-auto bg-amber-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">{{ specialRequests.length }}</span>
                    </button>
                </nav>

                <!-- User Profile & Logout (Bottom Sidebar) -->
                <div class="p-4 border-t border-slate-100 mt-auto">
                    <div class="flex items-center gap-3 p-2 rounded-xl transition-colors">
                        <img :src="`https://ui-avatars.com/api/?name=${$page.props.auth.user.name}&background=0A1128&color=fff`" alt="Admin" class="w-10 h-10 rounded-full border-2 border-white shadow-sm">
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-bold text-slate-900 truncate">{{ $page.props.auth.user.name }}</p>
                            <p class="text-[10px] text-slate-500 truncate uppercase tracking-wider font-bold">Admin</p>
                        </div>
                        <Link 
                            :href="route('logout')" 
                            method="post" 
                            as="button"
                            class="w-10 h-10 flex items-center justify-center text-rose-500 hover:text-rose-600 bg-rose-50 hover:bg-rose-100 rounded-xl transition-all duration-200"
                            title="Logout"
                        >
                            <i class="fa-solid fa-right-from-bracket"></i>
                        </Link>
                    </div>
                </div>
            </aside>

            <!-- MAIN CONTENT AREA -->
            <main class="flex-1 overflow-y-auto relative focus:outline-none">
                
                <!-- Mobile Header -->
                <div class="md:hidden flex items-center justify-between px-6 py-4 bg-white border-b border-slate-200 sticky top-0 z-10">
                    <ApplicationLogo :iconOnly="false" />
                    <button class="text-slate-500 hover:text-navy-900">
                        <i class="fa-solid fa-bars text-xl"></i>
                    </button>
                </div>

                <div class="p-6 lg:p-10 max-w-7xl mx-auto space-y-6 pb-20">
                    <!-- No Spacer - Reduced top margin -->

                    <!-- DYNAMIC CONTENT TABS -->
                    <transition name="fade" mode="out-in">
                        <OverviewTab 
                            v-if="activeTab === 'overview'" 
                            key="overview"
                            :stats="stats"
                            :recent-bookings="recentBookings"
                            :chart-data="chartData"
                            @view-all-bookings="activeTab = 'bookings'"
                        />

                        <BookingsTab 
                            v-else-if="activeTab === 'bookings'" 
                            key="bookings"
                            :filtered-bookings="filteredBookings"
                            v-model:booking-search="bookingSearch"
                            v-model:booking-status-filter="bookingStatusFilter"
                            @update-status="updateBookingStatus"
                        />

                        <RoomsTab 
                            v-else-if="activeTab === 'rooms'" 
                            key="rooms"
                            :filtered-rooms="filteredRooms"
                            v-model:room-search="roomSearch"
                            v-model:room-status-filter="roomStatusFilter"
                            v-model:room-type-filter="roomTypeFilter"
                            :room-type-names="roomTypeNames"
                            @open-room-modal="openRoomModal"
                            @delete-room="deleteRoom"
                            @update-room-status="updateRoomStatus"
                        />

                        <RoomTypesTab 
                            v-else-if="activeTab === 'roomTypes'" 
                            key="roomTypes"
                            :room-types="roomTypes"
                            @open-room-type-modal="openRoomTypeModal"
                            @delete-room-type="deleteRoomType"
                            @open-image-modal="openImageModal"
                        />

                        <FinancialsTab 
                            v-else-if="activeTab === 'financials'" 
                            key="financials"
                            :financial-summary="financialSummary"
                            :transactions="transactions"
                            @open-transaction-modal="openTransactionModal"
                        />

                        <CalendarTab 
                            v-else-if="activeTab === 'calendar'" 
                            key="calendar"
                            :calendar-data="calendarData"
                        />

                        <RequestsTab 
                            v-else-if="activeTab === 'requests'" 
                            key="requests"
                            :special-requests="specialRequests"
                            @clear-special-request="clearSpecialRequest"
                        />
                    </transition>
                </div>
            </main>
        </div>

        <!-- Room Type Modal -->
        <Modal :show="showRoomTypeModal" @close="closeRoomTypeModal" max-width="md">
            <div class="p-6">
                <h2 class="text-lg font-bold text-slate-900 font-serif mb-6">{{ editingRoomType ? $t('room_type_modal_title_edit') : $t('room_type_modal_title_add') }}</h2>
                <form @submit.prevent="submitRoomType" class="space-y-4">
                    <div>
                        <InputLabel for="name" :value="$t('room_type_name_label')" />
                        <TextInput id="name" v-model="roomTypeForm.name" type="text" class="mt-1 block w-full" required />
                    </div>
                    <div>
                        <InputLabel for="description" :value="$t('description_label')" />
                        <textarea id="description" v-model="roomTypeForm.description" class="mt-1 block w-full rounded-xl border-slate-300 shadow-sm focus:border-navy-500 focus:ring-navy-500 bg-slate-50 text-slate-900 px-4 py-2" rows="3"></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel for="capacity" :value="$t('capacity_label')" />
                            <TextInput id="capacity" v-model="roomTypeForm.capacity" type="number" class="mt-1 block w-full" required min="1" />
                        </div>
                        <div>
                            <InputLabel for="price" :value="$t('price_per_night_label')" />
                            <TextInput id="price" v-model="roomTypeForm.price_per_night" type="number" class="mt-1 block w-full" required min="0" />
                        </div>
                    </div>
                    <div>
                        <InputLabel for="image" :value="$t('room_photo_label')" />
                        <input id="image" type="file" @input="roomTypeForm.image = $event.target.files[0]" class="mt-1 block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-navy-50 file:text-navy-900 hover:file:bg-navy-100" />
                    </div>
                    
                    <div class="mt-6 flex justify-end gap-3">
                        <SecondaryButton @click="closeRoomTypeModal">{{ $t('cancel') }}</SecondaryButton>
                        <PrimaryButton type="submit" class="bg-navy-900" :disabled="roomTypeForm.processing">{{ $t('save_type_btn') }}</PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Room Modal -->
        <Modal :show="showRoomModal" @close="closeRoomModal" max-width="sm">
            <div class="p-6">
                <h2 class="text-lg font-bold text-slate-900 font-serif mb-6">{{ editingRoom ? $t('room_modal_title_edit') : $t('room_modal_title_add') }}</h2>
                <form @submit.prevent="submitRoom" class="space-y-4">
                    <div>
                        <InputLabel for="room_number" :value="$t('room_number')" />
                        <TextInput id="room_number" v-model="roomForm.room_number" type="text" class="mt-1 block w-full" required />
                    </div>
                    <div>
                        <InputLabel for="room_type" :value="$t('room_type')" />
                        <select id="room_type" v-model="roomForm.room_type_id" class="mt-1 block w-full rounded-xl border-slate-300 shadow-sm focus:border-navy-500 focus:ring-navy-500 bg-slate-50 text-slate-900 px-4 py-2" required>
                            <option value="" disabled>{{ $t('choose_type') }}</option>
                            <option v-for="type in props.roomTypes" :key="type.id" :value="type.id">{{ type.name }}</option>
                        </select>
                    </div>
                    <div>
                        <InputLabel for="room_status" :value="$t('initial_status')" />
                        <select id="room_status" v-model="roomForm.status" class="mt-1 block w-full rounded-xl border-slate-300 shadow-sm focus:border-navy-500 focus:ring-navy-500 bg-slate-50 text-slate-900 px-4 py-2" required>
                            <option value="available">Available</option>
                            <option value="occupied">Occupied</option>
                            <option value="maintenance">Maintenance</option>
                        </select>
                    </div>
                    
                    <div class="mt-6 flex justify-end gap-3">
                        <SecondaryButton @click="closeRoomModal">{{ $t('cancel_btn') }}</SecondaryButton>
                        <PrimaryButton type="submit" class="bg-navy-900" :disabled="roomForm.processing">{{ $t('save_room_btn') }}</PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Image Management Modal -->
        <Modal :show="showImageModal" @close="closeImageModal" max-width="lg">
            <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-lg font-bold text-slate-900 font-serif">{{ $t('manage_photos') }}: {{ currentImageType?.name }}</h2>
                    <button @click="closeImageModal" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark text-xl"></i></button>
                </div>
                
                <!-- Upload Form -->
                <form @submit.prevent="uploadImage" class="mb-6 flex flex-wrap items-end gap-4 p-4 bg-slate-50 rounded-2xl border border-slate-200">
                    <div class="flex-1 min-w-[200px]">
                        <InputLabel for="new_image" :value="$t('upload_new_photo')" class="mb-2" />
                        <input id="new_image" type="file" @input="imageForm.image = $event.target.files[0]" class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-white file:text-navy-900 hover:file:bg-slate-100 border border-slate-200 rounded-xl bg-white focus:ring-navy-900 focus:border-navy-900" required accept="image/*" />
                    </div>
                    <PrimaryButton type="submit" :disabled="imageForm.processing" class="mb-1 py-2.5">
                        <i class="fa-solid fa-cloud-arrow-up mr-2 text-gold-400"></i> Upload
                    </PrimaryButton>
                    <div v-if="imageForm.progress" class="w-full mt-2 h-1.5 bg-slate-200 rounded-full overflow-hidden">
                        <div class="bg-navy-900 h-full transition-all duration-300" :style="{ width: imageForm.progress.percentage + '%' }"></div>
                    </div>
                </form>

                <!-- Initial single image fallback support -->
                <div v-if="currentImageType?.images?.length === 0 && currentImageType?.image" class="mb-6">
                    <p class="text-xs text-amber-600 bg-amber-50 p-3 rounded-xl border border-amber-100 font-bold mb-4">
                        Poto lama sedang digunakan. Silakan upload gambar baru agar dapat dikelola sebagai thumbnail yang bisa diganti-ganti.
                    </p>
                </div>

                <!-- Image Grid -->
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4 max-h-[400px] overflow-y-auto pr-2 pb-2">
                    <div v-for="img in currentImageType?.images" :key="img.id" class="relative group rounded-2xl overflow-hidden border-2 transition-all duration-300" :class="img.is_thumbnail ? 'border-gold-500 shadow-md shadow-gold-500/20' : 'border-slate-200 hover:border-navy-300'">
                        <img :src="img.image_path" class="w-full h-32 object-cover" />
                        
                        <!-- Top actions overlay -->
                        <div class="absolute inset-x-0 top-0 p-2 flex justify-between items-start opacity-0 group-hover:opacity-100 transition-opacity duration-300 bg-gradient-to-b from-slate-900/60 to-transparent">
                            <span v-if="img.is_thumbnail" class="bg-gold-500 text-white text-[9px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wider shadow-sm">Thumbnail</span>
                            <span v-else></span>
                            <button @click.prevent="deleteImage(img.id)" class="w-7 h-7 rounded-full bg-rose-500/90 text-white flex items-center justify-center hover:bg-rose-600 backdrop-blur-sm transition-colors shadow-sm" :title="$t('delete')">
                                <i class="fa-solid fa-trash-can text-xs"></i>
                            </button>
                        </div>
                        
                        <!-- Bottom actions overlay -->
                        <div v-if="!img.is_thumbnail" class="absolute inset-x-0 bottom-0 p-3 opacity-0 group-hover:opacity-100 transition-opacity duration-300 bg-gradient-to-t from-slate-900/90 via-slate-900/50 to-transparent flex justify-center backdrop-blur-[2px]">
                            <button @click.prevent="setThumbnail(img.id)" class="text-[10px] text-white font-bold tracking-widest uppercase hover:text-gold-400 transition-colors py-1 w-full text-center">
                                {{ $t('set_as_thumbnail') }}
                            </button>
                        </div>
                    </div>

                    <div v-if="!currentImageType?.images?.length" class="col-span-full py-12 text-center text-slate-400 bg-slate-50 rounded-2xl border border-dashed border-slate-300">
                        <i class="fa-regular fa-images text-4xl mb-3 block"></i>
                        <p class="text-sm font-bold">{{ $t('no_photos_uploaded') }}</p>
                    </div>
                </div>
            </div>
        </Modal>

        <!-- Transaction Modal -->
        <Modal :show="showTransactionModal" @close="closeTransactionModal" max-width="md">
            <div class="p-6">
                <h2 class="text-lg font-bold text-slate-900 font-serif mb-6">{{ $t('new_transaction') }}</h2>
                <form @submit.prevent="submitTransaction" class="space-y-4">
                    <div>
                        <InputLabel for="tx_type" :value="$t('transaction_type')" />
                        <select 
                            id="tx_type"
                            v-model="transactionForm.type"
                            class="mt-1 block w-full bg-slate-50 border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-navy-900 focus:border-navy-900 font-bold"
                        >
                            <option value="income">{{ $t('income_label') }}</option>
                            <option value="expense">{{ $t('expense_label') }}</option>
                        </select>
                    </div>

                    <div>
                        <InputLabel for="tx_category" :value="$t('category_label')" />
                        <TextInput 
                            id="tx_category"
                            v-model="transactionForm.category"
                            type="text"
                            class="mt-1 block w-full"
                            :placeholder="$t('category_placeholder')"
                            required
                        />
                    </div>

                    <div>
                        <InputLabel for="tx_amount" :value="$t('amount_label')" />
                        <TextInput 
                            id="tx_amount"
                            v-model="transactionForm.amount"
                            type="number"
                            class="mt-1 block w-full"
                            required
                        />
                    </div>

                    <div>
                        <InputLabel for="tx_description" :value="$t('notes_optional_label')" />
                        <textarea 
                            id="tx_description"
                            v-model="transactionForm.description"
                            rows="3"
                            class="mt-1 block w-full bg-slate-50 border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-navy-900 focus:border-navy-900 p-3"
                            :placeholder="$t('transaction_detail_placeholder')"
                        ></textarea>
                    </div>

                    <div class="mt-6 flex justify-end gap-3">
                        <SecondaryButton @click="closeTransactionModal">{{ $t('cancel_btn') }}</SecondaryButton>
                        <PrimaryButton type="submit" class="bg-navy-900" :disabled="transactionForm.processing">{{ $t('save_transaction_btn') }}</PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

    </AuthenticatedLayout>
</template>

<style scoped>
/* Hide scrollbar for clean look */
::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}
::-webkit-scrollbar-track {
    background: transparent;
}
::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 3px;
}
::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

/* Smooth Fade Transition */
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.25s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

/* Toast Slide-in Transition */
.toast-enter-active {
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}
.toast-leave-active {
    transition: all 0.3s ease-in;
}
.toast-enter-from,
.toast-leave-to {
    opacity: 0;
    transform: translateX(100%) scale(0.9);
}

/* Custom Select Arrow */
select {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
    background-position: right 0.5rem center;
    background-repeat: no-repeat;
    background-size: 1em;
    padding-right: 1.8rem;
    -webkit-appearance: none;
    appearance: none;
}
</style>