<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';

const props = defineProps({
    href: String,
    active: Boolean,
});

const pageProps = usePage().props.value;
// Ensure `pageProps` and `user` are defined before accessing `preferredTheme`
const preferredTheme = (pageProps && pageProps.user && pageProps.user.preferredTheme) ? pageProps.user.preferredTheme : 'light';

const classes = computed(() => {
    const baseClasses = 'inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium leading-5 focus:outline-none transition duration-150 ease-in-out';
    const activeClasses = preferredTheme === 'dark'
        ? 'border-indigo-500 text-white focus:border-indigo-700'
        : 'border-indigo-400 text-gray-900 focus:border-indigo-700';
    const inactiveClasses = preferredTheme === 'dark'
        ? 'border-transparent text-gray-400 hover:text-gray-300 hover:border-gray-600 focus:text-gray-300 focus:border-gray-600'
        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:text-gray-700 focus:border-gray-300';

    return props.active ? `${baseClasses} ${activeClasses}` : `${baseClasses} ${inactiveClasses}`;
});
</script>

<template>
    <Link :href="href" :class="classes">
        <slot />
    </Link>
</template>
