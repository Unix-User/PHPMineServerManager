<script setup>
defineProps({
    on: Boolean,
});
</script>

<template>
    <div>
        <transition leave-active-class="transition ease-in duration-1000" leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-show="on" :class="{'text-sm': true, 'text-gray-600': !darkMode, 'text-gray-300': darkMode}">
                <slot />
            </div>
        </transition>
    </div>
</template>

<style>
@media (prefers-color-scheme: dark) {
    :root {
        --text-color: #d1d5db; /* Lighter gray for dark mode */
    }
}

@media (prefers-color-scheme: light) {
    :root {
        --text-color: #4b5563; /* Darker gray for light mode */
    }
}
</style>