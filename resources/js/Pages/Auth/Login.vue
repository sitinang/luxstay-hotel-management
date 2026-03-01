<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Log in" />

        <div class="mb-10 text-center">
            <h2 class="text-3xl font-serif font-bold text-navy-900 mb-2">{{ $t('welcome_back') }}</h2>
            <p class="text-sm text-slate-500 font-medium">{{ $t('login_to_account') }}</p>
        </div>

        <div v-if="status" class="mb-6 p-4 bg-emerald-50 text-emerald-700 text-sm font-bold rounded-2xl border border-emerald-100">
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <div class="space-y-1.5">
                <InputLabel for="email" :value="$t('email')" class="text-xs font-bold uppercase tracking-widest text-slate-400 pl-1" />
                <TextInput
                    id="email"
                    type="email"
                    class="block w-full py-4 text-sm"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                    :placeholder="$t('your_email')"
                />
                <InputError class="mt-1" :message="form.errors.email" />
            </div>

            <div class="space-y-1.5">
                <div class="flex justify-between items-center px-1">
                    <InputLabel for="password" :value="$t('password_label')" class="text-xs font-bold uppercase tracking-widest text-slate-400" />
                    <Link
                        v-if="canResetPassword"
                        :href="route('password.request')"
                        class="text-[10px] font-bold uppercase tracking-wider text-gold-600 hover:text-gold-700"
                    >
                        {{ $t('forgot_password') }}
                    </Link>
                </div>
                <TextInput
                    id="password"
                    type="password"
                    class="block w-full py-4 text-sm"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                    placeholder="••••••••"
                />
                <InputError class="mt-1" :message="form.errors.password" />
            </div>

            <div class="flex items-center">
                <label class="flex items-center cursor-pointer group">
                    <Checkbox name="remember" v-model:checked="form.remember" />
                    <span class="ms-2 text-xs font-bold text-slate-500 group-hover:text-navy-900 transition-colors uppercase tracking-wider"
                        >{{ $t('remember_me') }}</span
                    >
                </label>
            </div>

            <div class="pt-2">
                <div class="btn-premium-container btn-navy w-full rounded-2xl">
                    <button
                        type="submit"
                        class="btn-premium-inner w-full py-5 flex justify-center text-sm tracking-[0.2em] font-bold uppercase rounded-2xl"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                    >
                        {{ $t('login_now') }}
                    </button>
                </div>
            </div>

            <div class="text-center pt-4">
                <p class="text-xs font-medium text-slate-500">
                    {{ $t('no_account') }} 
                    <Link :href="route('register')" class="text-navy-900 font-bold hover:text-gold-600 transition-colors">
                        {{ $t('register_member') }}
                    </Link>
                </p>
            </div>
        </form>
    </GuestLayout>
</template>
