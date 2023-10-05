<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
</script>

<template>
    <AppLayout title="Tutoriais">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Tutoriais
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6 lg:p-8 bg-white border-b border-gray-200">
                        <h1 class="mt-8 text-2xl font-medium text-gray-900 text-center">
                            Tutoriais
                        </h1>
                        <p class="mt-6 text-gray-500 leading-relaxed text-center">
                            Bem-vindo à página de ajuda. Aqui, você encontrará uma variedade de tutoriais úteis para
                            ajudá-lo a navegar e aproveitar ao máximo o servidor.
                        </p>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 lg:gap-8">
                            <a :href="route('factionCommands')"
                                class="scale-100 p-6 bg-white dark:bg-gray-800/50 dark:bg-gradient-to-bl from-gray-700/50 via-transparent dark:ring-1 dark:ring-inset dark:ring-white/5 rounded-lg shadow-2xl shadow-gray-500/20 dark:shadow-none flex motion-safe:hover:scale-[1.01] transition-all duration-250 focus:outline focus:outline-2 focus:outline-red-500">
                                <div>
                                    <div class="flex items-center">
                                        <div
                                            class="h-12 w-12 bg-red-50 dark:bg-red-800/20 flex items-center justify-center rounded-full">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24">
                                                <text x="50%" y="50%" font-size="20" text-anchor="middle" dy=".3em">⚔</text>
                                            </svg>
                                        </div>
                                        <h2 class="ml-3 text-xl font-semibold text-gray-900 dark:text-white">Comandos de
                                            Facção
                                        </h2>
                                    </div>
                                    <p class="mt-4 text-gray-500 dark:text-gray-400 text-sm leading-relaxed">
                                        Aqui você encontrará uma lista detalhada de todos os comandos de facção disponíveis
                                        no servidor. Cada comando é acompanhado por uma descrição de sua função e uso.
                                    </p>
                                </div>
                            </a>

                            <a :href="route('currencies')"
                                class="scale-100 p-6 bg-white dark:bg-gray-800/50 dark:bg-gradient-to-bl from-gray-700/50 via-transparent dark:ring-1 dark:ring-inset dark:ring-white/5 rounded-lg shadow-2xl shadow-gray-500/20 dark:shadow-none flex motion-safe:hover:scale-[1.01] transition-all duration-250 focus:outline focus:outline-2 focus:outline-red-500">
                                <div>
                                    <div class="flex items-center">
                                        <div
                                            class="h-12 w-12 bg-red-50 dark:bg-red-800/20 flex items-center justify-center rounded-full">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24">
                                                <text x="50%" y="50%" font-size="20" text-anchor="middle"
                                                    dy=".3em">💰</text>
                                            </svg>
                                        </div>
                                        <h2 class="ml-3 text-xl font-semibold text-gray-900 dark:text-white">Moedas</h2>
                                    </div>
                                    <p class="mt-4 text-gray-500 dark:text-gray-400 text-sm leading-relaxed">
                                        Aqui você encontrará uma descrição detalhada de todas as moedas utilizadas no
                                        servidor, bem como informações sobre como ganhá-las e gastá-las.
                                    </p>
                                </div>
                            </a>

                            <!-- Adicione mais links de tutorial conforme necessário -->
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </AppLayout>
</template>

