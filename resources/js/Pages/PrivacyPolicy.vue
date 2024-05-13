<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticationCardLogo from '@/Components/AuthenticationCardLogo.vue';

defineProps({
    policy: String,
});
</script>

<template>
    <Head title="Privacy Policy" />

    <div :class="{'dark': $page.props.userPreferences.darkMode}" class="font-sans antialiased">
        <div class="pt-4 bg-gray-100 dark:bg-gray-800">
            <div class="min-h-screen flex flex-col items-center pt-6 sm:pt-0">
                <div>
                    <AuthenticationCardLogo />
                </div>

                <div class="w-full sm:max-w-2xl mt-6 p-6 bg-white dark:bg-gray-900 shadow-md overflow-hidden sm:rounded-lg prose dark:prose-dark" v-html="policy">
                    <p class="text-gray-900 dark:text-gray-100">Please review our Privacy Policy to understand our practices.</p>
                </div>
            </div>
        </div>
    </div>
</template>
