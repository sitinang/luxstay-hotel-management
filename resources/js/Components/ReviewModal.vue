<script setup>
import { ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    show: Boolean,
    booking: Object,
});

const emit = defineEmits(['close']);

const form = useForm({
    booking_id: null,
    rating: 5,
    comment: '',
});

watch(() => props.booking, (newBooking) => {
    if (newBooking) {
        form.booking_id = newBooking.id;
    }
}, { immediate: true });

const showSuccess = ref(false);

const submit = () => {
    form.post(route('reviews.store'), {
        preserveScroll: true,
        onSuccess: () => {
            showSuccess.value = true;
            form.reset();
        },
    });
};

const close = () => {
    showSuccess.value = false;
    emit('close');
};

const setRating = (n) => {
    form.rating = n;
};
</script>

<template>
    <Modal :show="show" @close="close">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900 border-b pb-3 mb-4">
                {{ $t('review_for') }} {{ booking?.room?.room_type?.name }}
            </h2>

            <!-- Success State -->
            <div v-if="showSuccess" class="py-8 flex flex-col items-center justify-center text-center space-y-4">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center text-green-500 mb-2">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900">Ulasan Berhasil Dikirim!</h3>
                <p class="text-sm text-gray-500">Terima kasih atas ulasan dan penilaian Anda. Tanggapan Anda sangat berarti bagi kami.</p>
                <div class="mt-6">
                    <PrimaryButton @click="close" type="button">
                        Tutup
                    </PrimaryButton>
                </div>
            </div>

            <!-- Form State -->
            <form v-else @submit.prevent="submit">
                <div class="mb-4">
                    <InputLabel :value="$t('rating_label')" class="mb-2" />
                    <div class="flex items-center space-x-1">
                        <button 
                            v-for="n in 5" 
                            :key="n" 
                            type="button"
                            @click="setRating(n)"
                            class="focus:outline-none transition-colors duration-200"
                        >
                            <svg 
                                class="w-8 h-8" 
                                :class="n <= form.rating ? 'text-yellow-400 fill-current' : 'text-gray-300 fill-current'"
                                viewBox="0 0 20 20"
                            >
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                        </button>
                    </div>
                    <InputError :message="form.errors.rating" class="mt-2" />
                </div>

                <div class="mb-4">
                    <InputLabel for="comment" :value="$t('comment_label')" class="mb-2" />
                    <textarea
                        id="comment"
                        v-model="form.comment"
                        class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                        rows="4"
                        :placeholder="$t('write_review_desc')"
                    ></textarea>
                    <InputError :message="form.errors.comment" class="mt-2" />
                </div>

                <div class="mt-6 flex justify-end">
                    <SecondaryButton @click="close" type="button">
                        {{ $t('cancel') }}
                    </SecondaryButton>

                    <PrimaryButton
                        class="ms-3"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                    >
                        {{ $t('submit_review') }}
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </Modal>
</template>
