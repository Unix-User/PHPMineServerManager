<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
</script>

<template>
    <AppLayout title="Tutoriais e Guias">
        <template #header>
            <h2
                class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight transition-colors duration-300">
                Tutoriais e Guias
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white/90 dark:bg-gray-800/90 backdrop-blur-sm overflow-hidden shadow-xl sm:rounded-lg transition-all duration-300 ease-in-out hover:shadow-2xl">
                    <div class="p-6 sm:p-8">
                        <div class="p-6 lg:p-8 bg-white/95 dark:bg-gray-800/95 backdrop-blur-sm border-b border-gray-200 dark:border-gray-700 transition-all duration-300 rounded-lg">
                            <h1
                                class="mt-8 text-2xl font-medium text-gray-900 dark:text-gray-100 text-center transition-colors">
                                Tutoriais e Guias
                            </h1>
                            <p
                                class="mt-6 text-gray-600 dark:text-gray-300 leading-relaxed text-center transition-colors">
                                Bem-vindo à página de ajuda. Aqui, você encontrará
                                uma variedade de tutoriais úteis para ajudá-lo a
                                navegar e aproveitar ao máximo o servidor.
                            </p>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 lg:gap-8 mt-10">
                                <a :href="route('factionCommands')"
                                    class="scale-100 p-6 bg-white/80 dark:bg-gray-800/60 dark:bg-gradient-to-bl from-gray-700/50 via-transparent dark:ring-1 dark:ring-inset dark:ring-white/5 rounded-lg shadow-lg hover:shadow-xl shadow-gray-500/20 dark:shadow-none flex motion-safe:hover:scale-[1.01] transition-all duration-300 focus:outline focus:outline-2 focus:outline-blue-500">
                                    <div>
                                        <div class="flex items-center">
                                            <div
                                                class="h-12 w-12 bg-indigo-50 dark:bg-indigo-800/30 flex items-center justify-center rounded-full transition-colors">
                                                <svg xmlns="http://www.w3.org/2000/svg" height="1em"
                                                    viewBox="0 0 512 512"
                                                    class="w-6 h-6 text-indigo-600 dark:text-indigo-400 transition-colors"
                                                    fill="currentColor">
                                                    <path
                                                        d="M489.2 287.9h-27.4c-2.6 0-4.6 2-4.6 4.6v32h-36.6V146.2c0-2.6-2-4.6-4.6-4.6h-27.4c-2.6 0-4.6 2-4.6 4.6v32h-36.6v-32c0-2.6-2-4.6-4.6-4.6h-27.4c-2.6 0-4.6 2-4.6 4.6v32h-36.6v-32c0-6-8-4.6-11.7-4.6v-38c8.3-2 17.1-3.4 25.7-3.4 10.9 0 20.9 4.3 31.4 4.3 4.6 0 27.7-1.1 27.7-8v-60c0-2.6-2-4.6-4.6-4.6-5.1 0-15.1 4.3-24 4.3-9.7 0-20.9-4.3-32.6-4.3-8 0-16 1.1-23.7 2.9v-4.9c5.4-2.6 9.1-8.3 9.1-14.3 0-20.7-31.4-20.8-31.4 0 0 6 3.7 11.7 9.1 14.3v111.7c-3.7 0-11.7-1.4-11.7 4.6v32h-36.6v-32c0-2.6-2-4.6-4.6-4.6h-27.4c-2.6 0-4.6 2-4.6 4.6v32H128v-32c0-2.6-2-4.6-4.6-4.6H96c-2.6 0-4.6 2-4.6 4.6v178.3H54.8v-32c0-2.6-2-4.6-4.6-4.6H22.8c-2.6 0-4.6 2-4.6 4.6V512h182.9v-96c0-72.6 109.7-72.6 109.7 0v96h182.9V292.5c.1-2.6-1.9-4.6-4.5-4.6zm-288.1-4.5c0 2.6-2 4.6-4.6 4.6h-27.4c-2.6 0-4.6-2-4.6-4.6v-64c0-2.6 2-4.6 4.6-4.6h27.4c2.6 0 4.6 2 4.6 4.6v64zm146.4 0c0 2.6-2 4.6-4.6 4.6h-27.4c-2.6 0-4.6-2-4.6-4.6v-64c0-2.6 2-4.6 4.6-4.6h27.4c2.6 0 4.6 2 4.6 4.6v64z" />
                                                </svg>
                                            </div>
                                            <h2
                                                class="ml-3 text-xl font-semibold text-gray-900 dark:text-white transition-colors">
                                                Comandos de Facção
                                            </h2>
                                        </div>
                                        <p
                                            class="mt-4 text-gray-600 dark:text-gray-300 text-sm leading-relaxed transition-colors">
                                            Aqui você encontrará uma lista detalhada
                                            de todos os comandos de facção
                                            disponíveis no servidor. Cada comando é
                                            acompanhado por uma descrição de sua
                                            função e uso.
                                        </p>
                                    </div>
                                </a>

                                <a :href="route('currencies')"
                                    class="scale-100 p-6 bg-white/80 dark:bg-gray-800/60 dark:bg-gradient-to-bl from-gray-700/50 via-transparent dark:ring-1 dark:ring-inset dark:ring-white/5 rounded-lg shadow-lg hover:shadow-xl shadow-gray-500/20 dark:shadow-none flex motion-safe:hover:scale-[1.01] transition-all duration-300 focus:outline focus:outline-2 focus:outline-blue-500">
                                    <div>
                                        <div class="flex items-center">
                                            <div
                                                class="h-12 w-12 bg-emerald-50 dark:bg-emerald-800/30 flex items-center justify-center rounded-full transition-colors">
                                                <svg xmlns="http://www.w3.org/2000/svg" height="1em"
                                                    viewBox="0 0 512 512"
                                                    class="w-6 h-6 text-emerald-600 dark:text-emerald-400 transition-colors"
                                                    fill="currentColor">
                                                    <path
                                                        d="M320 96H192L144.6 24.9C137.5 14.2 145.1 0 157.9 0H354.1c12.8 0 20.4 14.2 13.3 24.9L320 96zM192 128H320c3.8 2.5 8.1 5.3 13 8.4C389.7 172.7 512 250.9 512 416c0 53-43 96-96 96H96c-53 0-96-43-96-96C0 250.9 122.3 172.7 179 136.4l0 0 0 0c4.8-3.1 9.2-5.9 13-8.4zm84 88c0-11-9-20-20-20s-20 9-20 20v14c-7.6 1.7-15.2 4.4-22.2 8.5c-13.9 8.3-25.9 22.8-25.8 43.9c.1 20.3 12 33.1 24.7 40.7c11 6.6 24.7 10.8 35.6 14l1.7 .5c12.6 3.8 21.8 6.8 28 10.7c5.1 3.2 5.8 5.4 5.9 8.2c.1 5-1.8 8-5.9 10.5c-5 3.1-12.9 5-21.4 4.7c-11.1-.4-21.5-3.9-35.1-8.5c-2.3-.8-4.7-1.6-7.2-2.4c-10.5-3.5-21.8 2.2-25.3 12.6s2.2 21.8 12.6 25.3c1.9 .6 4 1.3 6.1 2.1l0 0 0 0c8.3 2.9 17.9 6.2 28.2 8.4V424c0 11 9 20 20 20s20-9 20-20V410.2c8-1.7 16-4.5 23.2-9c14.3-8.9 25.1-24.1 24.8-45c-.3-20.3-11.7-33.4-24.6-41.6c-11.5-7.2-25.9-11.6-37.1-15l0 0-.7-.2c-12.8-3.9-21.9-6.7-28.3-10.5c-5.2-3.1-5.3-4.9-5.3-6.7c0-3.7 1.4-6.5 6.2-9.3c5.4-3.2 13.6-5.1 21.5-5c9.6 .1 20.2 2.2 31.2 5.2c10.7 2.8 21.6-3.5 24.5-14.2s-3.5-21.6-14.2-24.5c-6.5-1.7-13.7-3.4-21.1-4.7V216z" />
                                                </svg>
                                            </div>
                                            <h2
                                                class="ml-3 text-xl font-semibold text-gray-900 dark:text-white transition-colors">
                                                Moedas
                                            </h2>
                                        </div>
                                        <p
                                            class="mt-4 text-gray-600 dark:text-gray-300 text-sm leading-relaxed transition-colors">
                                            Aqui você encontrará uma descrição
                                            detalhada de todas as moedas utilizadas
                                            no servidor, bem como informações sobre
                                            como ganhá-las e gastá-las.
                                        </p>
                                    </div>
                                </a>

                                <!-- Adicione mais links de tutorial conforme necessário -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
