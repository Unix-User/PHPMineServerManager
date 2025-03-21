<script setup>
defineProps({
    type: {
        type: String,
        default: 'submit',
    },
    theme: {
        type: String,
        default: 'dark', // 'dark' or 'light'
    }
});
</script>

<template>
    <button :type="type" :class="`inline-flex items-center px-4 py-2 border border-transparent rounded-md font-semibold text-xs uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-offset-2 transition ease-in-out duration-150 ${theme === 'dark' ? 'bg-gray-800 text-white hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:ring-indigo-500' : 'bg-white text-gray-800 hover:bg-gray-100 focus:bg-gray-100 active:bg-gray-200 focus:ring-gray-300'}`">
        <slot />
    </button>
</template>
