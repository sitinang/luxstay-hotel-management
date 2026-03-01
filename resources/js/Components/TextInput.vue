<script setup>
import { onMounted, ref } from 'vue';

const model = defineModel({
    type: String,
    required: true,
});

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <input
        class="rounded-xl border-slate-300 shadow-sm focus:border-navy-500 focus:ring-navy-500 bg-slate-50 text-slate-900 px-4 py-2"
        v-model="model"
        ref="input"
    />
</template>
