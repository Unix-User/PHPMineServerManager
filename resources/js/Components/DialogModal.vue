<script setup>
import Modal from './Modal.vue';

const emit = defineEmits(['close']);

defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    maxWidth: {
        type: String,
        default: '2xl',
    },
    closeable: {
        type: Boolean,
        default: true,
    },
});

const close = () => {
    emit('close');
};
</script>

<template>
    <Modal
        :show="show"
        :max-width="maxWidth"
        :closeable="closeable"
        @close="close"
    >
        <div class="px-6 py-4">
            <div :class="{'text-lg font-medium': true, 'text-gray-900': !darkMode, 'text-gray-100': darkMode}">
                <slot name="title" />
            </div>

            <div :class="{'mt-4 text-sm': true, 'text-gray-600': !darkMode, 'text-gray-300': darkMode}">
                <slot name="content" />
            </div>
        </div>

        <div :class="{'flex flex-row justify-end px-6 py-4 text-right': true, 'bg-gray-100': !darkMode, 'bg-gray-800': darkMode}">
            <slot name="footer" />
        </div>
    </Modal>
</template>

<script>
import { ref, watchEffect } from 'vue';

const darkMode = ref(window.matchMedia('(prefers-color-scheme: dark)').matches);

watchEffect(() => {
    darkMode.value = window.matchMedia('(prefers-color-scheme: dark)').matches;
});

window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
    darkMode.value = e.matches;
});
</script>
