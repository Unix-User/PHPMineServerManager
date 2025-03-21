<script setup>
import { onMounted, ref, computed } from 'vue';

defineProps({
    modelValue: String,
});

defineEmits(['update:modelValue']);

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });

const isDarkMode = computed(() => window.matchMedia('(prefers-color-scheme: dark)').matches);
</script>

<template>
    <input
        ref="input"
        :class="{
            'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500': !isDarkMode.value,
            'border-gray-600 focus:border-indigo-300 focus:ring-indigo-300': isDarkMode.value
        }"
        class="rounded-md shadow-sm"
        :value="modelValue"
        @input="$emit('update:modelValue', $event.target.value)"
    >
</template>
