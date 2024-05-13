<script setup>
defineProps({
    value: String,
});
</script>

<template>
    <label :class="`block font-medium text-sm ${useDarkMode ? 'text-white' : 'text-gray-700'}`">
        <span v-if="value">{{ value }}</span>
        <span v-else><slot /></span>
    </label>
</template>

<script>
import { ref, computed } from 'vue';

const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
const useDarkMode = ref(prefersDark);

window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
    useDarkMode.value = e.matches;
});
</script>