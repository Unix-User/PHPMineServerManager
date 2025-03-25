<script setup>
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ApplicationMark from '@/Components/ApplicationMark.vue';

defineProps({
    currencies: String,
});
</script>

<template>
    <AppLayout title="Guia de Moedas">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight transition-colors duration-300">
                Guia de Moedas
            </h2>
        </template>

        <div class="py-12 bg-gradient-to-b from-white/50 to-transparent dark:from-gray-900/50 dark:to-transparent transition-all duration-300">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="flex flex-col items-center space-y-8">
                    <div class="mb-6 transform transition-all duration-300 hover:scale-105 hover:rotate-2">
                        <ApplicationMark class="block h-12 w-auto" />
                    </div>
                    
                    <div class="w-full sm:max-w-3xl bg-white/95 dark:bg-gray-800/95 backdrop-blur-sm shadow-lg rounded-xl overflow-hidden transition-all duration-300 hover:shadow-2xl hover:-translate-y-1 border border-gray-200/50 dark:border-gray-700/50">
                        <div class="p-6 sm:p-8">
                            <div v-html="currencies" class="prose max-w-none dark:prose-invert text-gray-800 dark:text-gray-200 selection:bg-blue-500/30 dark:selection:bg-blue-600/50 transition-colors duration-300">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
