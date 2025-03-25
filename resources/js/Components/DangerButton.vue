<script setup>
defineProps({
    type: {
        type: String,
        default: 'button',
    },
});
</script>

<template>
    <button :type="type" :class="`inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-md font-semibold text-xs uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-offset-2 transition ease-in-out duration-150 ${darkMode ? 'bg-red-700 hover:bg-red-600 active:bg-red-800 focus:ring-red-600 text-white' : 'bg-red-500 hover:bg-red-400 active:bg-red-600 focus:ring-red-400 text-white'}`">
        <slot />
    </button>
</template>

<script>
import { ref, computed } from 'vue';

const darkMode = ref(window.matchMedia('(prefers-color-scheme: dark)').matches);
window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', event => {
  darkMode.value = event.matches;
});

</script>
