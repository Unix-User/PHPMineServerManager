<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import UpdatesPanel from "@/Components/UpdatesPanel.vue";
</script>

<template>
    <AppLayout title="Atualizações">
        <template #header>
            <h2
                class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight transition-colors duration-300"
            >
                Últimas Atualizações
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div
                    class="bg-white/90 dark:bg-gray-800/90 backdrop-blur-sm overflow-hidden shadow-xl sm:rounded-lg transition-all duration-300 ease-in-out hover:shadow-2xl"
                >
                    <div
                        class="p-6 lg:p-8 border-b border-gray-200 dark:border-gray-700"
                    >
                        <UpdatesPanel class="transition-opacity duration-300 hover:opacity-95" />
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
