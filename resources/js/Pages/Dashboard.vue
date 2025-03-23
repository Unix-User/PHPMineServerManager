<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Welcome from '@/Components/Welcome.vue';
import RconConsole from '@/Components/RconConsole.vue';
import GoogleDriveBackups from '@/Components/GoogleDriveBackups.vue';
</script>

<template>
    <AppLayout title="Dashboard">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight transition-colors duration-300">
                Dashboard Overview
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white/90 dark:bg-gray-800/90 backdrop-blur-sm overflow-hidden shadow-xl rounded-lg transition-all duration-300 ease-in-out hover:shadow-2xl border border-gray-200/50 dark:border-gray-700/50">
                    <div class="p-6 sm:p-8">
                        <RconConsole class="transition-opacity duration-300 hover:opacity-90 mt-6" />
                        <GoogleDriveBackups class="transition-opacity duration-300 hover:opacity-90 mt-6" />
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>