<script setup>
defineProps({
    message: String,
});
</script>

<template>
    <div v-show="message">
        <p :class="`text-sm ${useDarkMode() ? 'text-red-300' : 'text-red-600'}`">
            {{ message }}
        </p>
    </div>
</template>

<script>
import { ref, watchEffect } from 'vue';

function useDarkMode() {
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    const darkMode = ref(prefersDark);

    watchEffect(() => {
        darkMode.value = window.matchMedia('(prefers-color-scheme: dark)').matches;
    });

    return darkMode;
}
</script>