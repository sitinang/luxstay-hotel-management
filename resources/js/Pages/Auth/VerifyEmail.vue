<script setup>
import { computed } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    code: '',
});

const resendForm = useForm({});

const submit = () => {
    form.post(route('verification.verify'));
};

const resend = () => {
    resendForm.post(route('verification.send'));
};

const verificationLinkSent = computed(
    () => props.status === 'otp-sent',
);
</script>

<template>
    <GuestLayout>
        <Head title="Verify Email" />

        <div class="mb-6 text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gold-400/10 mb-4">
                <i class="fa-solid fa-envelope-circle-check text-gold-400 text-3xl"></i>
            </div>
            <h2 class="font-serif text-2xl font-bold text-navy-900 mb-2">Check Your Email</h2>
            <p class="text-sm text-gray-500 max-w-xs mx-auto">
                We've sent a 6-digit verification code to your email address. Please enter it below to activate your account.
            </p>
        </div>

        <div
            class="mb-6 px-4 py-3 rounded-xl bg-emerald-50 text-sm font-medium text-emerald-600 border border-emerald-100 flex items-center gap-3"
            v-if="verificationLinkSent"
        >
            <i class="fa-solid fa-circle-check"></i>
            A new verification code has been sent.
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <div>
                <div class="flex justify-center gap-2">
                    <input
                        v-model="form.code"
                        type="text"
                        maxlength="6"
                        placeholder="000000"
                        class="w-full text-center text-3xl font-bold tracking-[1rem] py-4 bg-slate-50 border-slate-200 rounded-2xl focus:ring-2 focus:ring-navy-900 focus:border-navy-900 transition-all placeholder:tracking-normal placeholder:font-normal placeholder:text-gray-300"
                        required
                        autofocus
                    />
                </div>
                <InputError class="mt-2 text-center" :message="form.errors.code" />
            </div>

            <div class="flex flex-col gap-4 items-center">
                <div class="btn-premium-container w-full rounded-2xl">
                    <button
                        type="submit"
                        class="btn-premium-inner w-full py-4 flex justify-center text-sm font-bold uppercase rounded-2xl"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                    >
                        Verify Account
                    </button>
                </div>

                <div class="flex items-center gap-4 text-sm">
                    <button
                        type="button"
                        @click="resend"
                        class="text-navy-600 font-bold hover:text-navy-900 transition-colors"
                        :disabled="resendForm.processing"
                    >
                        {{ resendForm.processing ? 'Sending...' : 'Resend Code' }}
                    </button>
                    <span class="text-gray-300">|</span>
                    <Link
                        :href="route('logout')"
                        method="post"
                        as="button"
                        class="text-gray-500 hover:text-rose-600 transition-colors"
                    >
                        Log Out
                    </Link>
                </div>
            </div>
        </form>
    </GuestLayout>
</template>
