<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticationCardLogo from '@/Components/AuthenticationCardLogo.vue';

defineProps({
    factionCommands: String,
});

</script>

<template>
    <Head title="Regras do Servidor" />

    <div :class="{'dark': $page.props.user?.preferences?.darkMode, 'light': !$page.props.user?.preferences?.darkMode}" class="font-sans antialiased">
        <div class="pt-4 bg-gray-100 dark:bg-gray-900">
            <div class="min-h-screen flex flex-col items-center pt-6 sm:pt-0">
                <div>
                    <AuthenticationCardLogo />
                </div>
                <div class="w-full sm:max-w-2xl mt-6 p-6 bg-white dark:bg-gray-800 shadow-md overflow-hidden sm:rounded-lg prose dark:prose-dark">
                    <div v-html="factionCommands" class="text-gray-900 dark:text-gray-100 selection:bg-blue-300 dark:selection:bg-blue-800 selection:text-white"></div>
                </div>
            </div>
        </div>
    </div>
</template>

