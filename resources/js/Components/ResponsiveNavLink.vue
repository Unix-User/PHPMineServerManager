<script setup>
import { computed, ref } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    active: Boolean,
    href: String,
    as: String,
});

const isDarkMode = ref(window.matchMedia('(prefers-color-scheme: dark)').matches);

window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
    isDarkMode.value = e.matches;
});

const classes = computed(() => {
    const baseClasses = 'block w-full pl-3 pr-4 py-2 border-l-4 text-left text-base font-medium focus:outline-none transition duration-150 ease-in-out';
    const activeClasses = 'border-indigo-700 focus:text-indigo-800 focus:bg-indigo-100';
    const inactiveClasses = 'border-transparent hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300 focus:text-gray-800 focus:bg-gray-50 focus:border-gray-300';

    const themeClasses = isDarkMode.value
        ? 'text-white bg-gray-800 hover:bg-gray-700'
        : 'text-gray-600 bg-white hover:bg-gray-100';

    return props.active
        ? `${baseClasses} ${activeClasses} ${themeClasses}`
        : `${baseClasses} ${inactiveClasses} ${themeClasses}`;
});
</script>

<template>
    <div>
        <button v-if="as == 'button'" :class="classes" class="w-full text-left">
            <slot />
        </button>

        <Link v-else :href="href" :class="classes">
            <slot />
        </Link>
    </div>
</template>
