<script setup>
import { computed, ref, onMounted } from 'vue';

const emit = defineEmits(['update:checked']);

const props = defineProps({
    checked: {
        type: [Array, Boolean],
        default: false,
    },
    value: {
        type: String,
        default: null,
    },
});

const proxyChecked = computed({
    get() {
        return props.checked;
    },

    set(val) {
        emit('update:checked', val);
    },
});

const isDarkMode = ref(false);

onMounted(() => {
    isDarkMode.value = window.matchMedia('(prefers-color-scheme: dark)').matches;
});
</script>


<template>
    <input
        v-model="proxyChecked"
        type="checkbox"
        :value="value"
        :class="[
            'rounded shadow-sm',
            {'border-gray-300 text-indigo-600 focus:ring-indigo-500': !isDarkMode.value},
            {'border-gray-600 text-indigo-300 focus:ring-indigo-700': isDarkMode.value}
        ]"
    >
</template>

