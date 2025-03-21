<script setup>
import { Link } from '@inertiajs/vue3';
import { ref, watchEffect } from 'vue';

defineProps({
    href: String,
    as: String,
});

const darkMode = ref(window.matchMedia('(prefers-color-scheme: dark)').matches);

watchEffect(() => {
    darkMode.value = window.matchMedia('(prefers-color-scheme: dark)').matches;
});

const baseClasses = 'block w-full px-4 py-2 text-left text-sm leading-5 transition duration-150 ease-in-out';
const lightModeClasses = 'text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100';
const darkModeClasses = 'text-gray-300 hover:bg-gray-700 focus:outline-none focus:bg-gray-700';
</script>

<template>
    <div>
        <button v-if="as == 'button'" type="submit" :class="[baseClasses, darkMode ? darkModeClasses : lightModeClasses]">
            <slot />
        </button>

        <a v-else-if="as =='a'" :href="href" :class="[baseClasses, darkMode ? darkModeClasses : lightModeClasses]">
            <slot />
        </a>

        <Link v-else :href="href" :class="[baseClasses, darkMode ? darkModeClasses : lightModeClasses]">
            <slot />
        </Link>
    </div>
</template>
