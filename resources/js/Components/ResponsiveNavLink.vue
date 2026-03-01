<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    href: {
        type: String,
        required: true,
    },
    active: {
        type: Boolean,
    },
});

const classes = computed(() =>
    props.active
        ? 'block w-full ps-3 pe-4 py-2 border-l-4 border-navy-600 dark:border-navy-400 text-start text-base font-bold text-navy-900 dark:text-navy-100 bg-navy-50 dark:bg-navy-900/50 focus:outline-none transition duration-150 ease-in-out font-sans'
        : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-slate-600 dark:text-slate-400 hover:text-navy-900 dark:hover:text-navy-100 hover:bg-slate-50 dark:hover:bg-slate-800 hover:border-slate-300 transition duration-150 ease-in-out font-sans',
);
</script>

<template>
    <Link :href="href" :class="classes">
        <slot />
    </Link>
</template>
