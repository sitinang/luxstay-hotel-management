<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Register Member" />

        <div class="mb-10 text-center">
            <h2 class="text-3xl font-serif font-bold text-navy-900 mb-2">{{ $t('join_luxstay') }}</h2>
            <p class="text-sm text-slate-500 font-medium">{{ $t('create_member_account') }}</p>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <div class="space-y-1.5">
                <InputLabel for="name" :value="$t('name')" class="text-xs font-bold uppercase tracking-widest text-slate-400 pl-1" />
                <TextInput
                    id="name"
                    type="text"
                    class="block w-full py-4 text-sm"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                    :placeholder="$t('name')"
                />
                <InputError class="mt-1" :message="form.errors.name" />
            </div>

            <div class="space-y-1.5">
                <InputLabel for="email" :value="$t('email')" class="text-xs font-bold uppercase tracking-widest text-slate-400 pl-1" />
                <TextInput
                    id="email"
                    type="email"
                    class="block w-full py-4 text-sm"
                    v-model="form.email"
                    required
                    autocomplete="username"
                    :placeholder="$t('your_email')"
                />
                <InputError class="mt-1" :message="form.errors.email" />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <InputLabel for="password" :value="$t('password_label')" class="text-xs font-bold uppercase tracking-widest text-slate-400 pl-1" />
                    <TextInput
                        id="password"
                        type="password"
                        class="block w-full py-4 text-sm"
                        v-model="form.password"
                        required
                        autocomplete="new-password"
                        placeholder="••••••••"
                    />
                    <InputError class="mt-1" :message="form.errors.password" />
                </div>

                <div class="space-y-1.5">
                    <InputLabel
                        for="password_confirmation"
                        :value="$t('confirm_password')"
                        class="text-xs font-bold uppercase tracking-widest text-slate-400 pl-1"
                    />
                    <TextInput
                        id="password_confirmation"
                        type="password"
                        class="block w-full py-4 text-sm"
                        v-model="form.password_confirmation"
                        required
                        autocomplete="new-password"
                        placeholder="••••••••"
                    />
                    <InputError
                        class="mt-1"
                        :message="form.errors.password_confirmation"
                    />
                </div>
            </div>

            <div class="pt-4">
                <div class="btn-premium-container w-full rounded-2xl">
                    <button
                        type="submit"
                        class="btn-premium-inner w-full py-5 flex justify-center text-sm tracking-[0.2em] font-bold uppercase rounded-2xl"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                    >
                        {{ $t('register_now') }}
                    </button>
                </div>
            </div>

            <div class="text-center pt-2">
                <p class="text-xs font-medium text-slate-500">
                    {{ $t('already_registered') }} 
                    <Link :href="route('login')" class="text-navy-900 font-bold hover:text-gold-600 transition-colors">
                        {{ $t('login_now') }}
                    </Link>
                </p>
            </div>
        </form>
    </GuestLayout>
</template>
