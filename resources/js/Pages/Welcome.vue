<script setup>
import { Head, Link } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import ApplicationMark from '@/Components/ApplicationMark.vue';

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
    laravelVersion: String,
    phpVersion: String,
});
</script>

<template>
    <Head title="Bem-vindo" />

    <div
        class="relative sm:flex sm:justify-center sm:items-center min-h-screen bg-cover bg-center bg-no-repeat bg-fixed" 
        style="background-image: url('storage/background.png');">
        <div v-if="canLogin" class="sm:fixed sm:top-0 sm:right-0 p-6 text-right z-10">
            <Link v-if="$page.props.auth.user" :href="route('dashboard')"
                class="font-semibold text-green-300 hover:text-green-900 dark:text-green-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">
            Painel
            </Link>

            <template v-else>
                <Link :href="route('login')"
                    class="font-semibold text-green-300 hover:text-green-900 dark:text-green-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">
                Entrar
                </Link>

                <Link v-if="canRegister" :href="route('register')"
                    class="ml-4 font-semibold text-green-300 hover:text-green-900 dark:text-green-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">
                Registrar
                </Link>
            </template>
        </div>

        <div class="max-w-7xl mx-auto p-6 lg:p-8">
            <div class="flex justify-center">
                <ApplicationLogo class="h-16 w-auto" />
            </div>

            <div class="mt-16">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 lg:gap-8">
                    <a href="http://minecraft.udianix.com.br/rules"
                        class="scale-100 p-6 bg-white dark:bg-gray-800/50 dark:bg-gradient-to-bl from-gray-700/50 via-transparent dark:ring-1 dark:ring-inset dark:ring-white/5 rounded-lg shadow-2xl shadow-gray-500/20 dark:shadow-none flex motion-safe:hover:scale-[1.01] transition-all duration-250 focus:outline focus:outline-2 focus:outline-red-500">
                        <div>
                            <div class="flex items-center">
                                <div
                                    class="h-12 w-12 bg-red-50 dark:bg-red-800/20 flex items-center justify-center rounded-full">
                                    <text x="50%" y="50%" font-size="20" text-anchor="middle" dy=".3em">📖</text>
                                </div>
                                <h2 class="ml-3 text-xl font-semibold text-gray-900 dark:text-white">Regras do Servidor</h2>
                            </div>
                            <p class="mt-4 text-gray-500 dark:text-gray-400 text-sm leading-relaxed">
                                Leia atentamente as regras do servidor antes de jogar para garantir uma experiência
                                divertida e respeitosa para todos os jogadores.
                            </p>
                        </div>
                    </a>

                    <a href="http://minecraft.udianix.com.br/factionCommands"
                        class="scale-100 p-6 bg-white dark:bg-gray-800/50 dark:bg-gradient-to-bl from-gray-700/50 via-transparent dark:ring-1 dark:ring-inset dark:ring-white/5 rounded-lg shadow-2xl shadow-gray-500/20 dark:shadow-none flex motion-safe:hover:scale-[1.01] transition-all duration-250 focus:outline focus:outline-2 focus:outline-red-500">
                        <div>
                            <div class="flex items-center">
                                <div
                                    class="h-12 w-12 bg-red-50 dark:bg-red-800/20 flex items-center justify-center rounded-full">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                        <text x="50%" y="50%" font-size="20" text-anchor="middle" dy=".3em">⚔</text>
                                    </svg>
                                </div>
                                <h2 class="ml-3 text-xl font-semibold text-gray-900 dark:text-white">Facções Medievais</h2>
                            </div>
                            <p class="mt-4 text-gray-500 dark:text-gray-400 text-sm leading-relaxed">
                                Descubra o mundo emocionante das nossas facções medievais e mergulhe em uma experiência de
                                jogo única.
                                Acesse a página de comandos das facções para aprender mais.
                            </p>
                        </div>
                    </a>

                    <a href="https://overviewer.udianix.com.br/#/-438/64/167/-2/survival/daytime" target="_blank"
                        class="scale-100 p-6 bg-white dark:bg-gray-800/50 dark:bg-gradient-to-bl from-gray-700/50 via-transparent dark:ring-1 dark:ring-inset dark:ring-white/5 rounded-lg shadow-2xl shadow-gray-500/20 dark:shadow-none flex motion-safe:hover:scale-[1.01] transition-all duration-250 focus:outline focus:outline-2 focus:outline-red-500">
                        <div>
                            <div class="flex items-center">
                                <div
                                    class="h-12 w-12 bg-red-50 dark:bg-red-800/20 flex items-center justify-center rounded-full">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                        <text x="50%" y="50%" font-size="20" text-anchor="middle" dy=".3em">🗺</text>
                                    </svg>
                                </div>
                                <h2 class="ml-3 text-xl font-semibold text-gray-900 dark:text-white">Mapa do Servidor</h2>
                            </div>
                            <p class="mt-4 text-gray-500 dark:text-gray-400 text-sm leading-relaxed">
                                Embarque em uma aventura épica com nosso mapa interativo em tempo real! Rastreie a
                                localização dos jogadores, descubra construções majestosas e explore territórios
                                desconhecidos. O mapa é o seu guia para o vasto mundo do nosso servidor Minecraft.
                            </p>
                        </div>
                    </a>

                    <a href="http://minecraft.udianix.com.br/donations"
                        class="scale-100 p-6 bg-white dark:bg-gray-800/50 dark:bg-gradient-to-bl from-gray-700/50 via-transparent dark:ring-1 dark:ring-inset dark:ring-white/5 rounded-lg shadow-2xl shadow-gray-500/20 dark:shadow-none flex motion-safe:hover:scale-[1.01] transition-all duration-250 focus:outline focus:outline-2 focus:outline-red-500">
                        <div>
                            <div class="flex items-center">
                                <div
                                    class="h-12 w-12 bg-red-50 dark:bg-red-800/20 flex items-center justify-center rounded-full">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                        <text x="50%" y="50%" font-size="20" text-anchor="middle" dy=".3em">💸</text>
                                    </svg>
                                </div>
                                <h2 class="ml-3 text-xl font-semibold text-gray-900 dark:text-white">Doações</h2>
                            </div>
                            <p class="mt-4 text-gray-500 dark:text-gray-400 text-sm leading-relaxed">
                                O servidor de Minecraft depende das doações para se manter online. Considere apoiar o
                                servidor para garantir uma jogabilidade estável e atualizações frequentes.
                            </p>
                        </div>
                    </a>


                    <a href="http://minecraft.udianix.com.br"
                        class="scale-100 p-6 bg-white dark:bg-gray-800/50 dark:bg-gradient-to-bl from-gray-700/50 via-transparent dark:ring-1 dark:ring-inset dark:ring-white/5 rounded-lg shadow-2xl shadow-gray-500/20 dark:shadow-none flex motion-safe:hover:scale-[1.01] transition-all duration-250 focus:outline focus:outline-2 focus:outline-red-500">
                        <div>
                            <div class="flex items-center">
                                <div
                                    class="h-12 w-12 bg-red-50 dark:bg-red-800/20 flex items-center justify-center rounded-full">
                                    <text x="50%" y="50%" font-size="20" text-anchor="middle" dy=".3em">🧱</text>
                                </div>
                                <h2 class="ml-3 text-xl font-semibold text-gray-900 dark:text-white">Servidor em construção
                                </h2>
                            </div>
                            <p class="mt-4 text-gray-500 dark:text-gray-400 text-sm leading-relaxed">
                                O site está atualmente em construção. Pedimos desculpas por qualquer inconveniente e
                                agradecemos a sua paciência.
                            </p>
                        </div>
                    </a>

                    <a href="https://github.com/Unix-User/PhPMineServerManager"
                        class="scale-100 p-6 bg-white dark:bg-gray-800/50 dark:bg-gradient-to-bl from-gray-700/50 via-transparent dark:ring-1 dark:ring-inset dark:ring-white/5 rounded-lg shadow-2xl shadow-gray-500/20 dark:shadow-none flex motion-safe:hover:scale-[1.01] transition-all duration-250 focus:outline focus:outline-2 focus:outline-red-500">
                        <div>
                            <div class="flex items-center">
                                <div
                                    class="h-12 w-12 bg-red-50 dark:bg-red-800/20 flex items-center justify-center rounded-full">
                                    <text x="50%" y="50%" font-size="20" text-anchor="middle" dy=".3em">💻</text>
                                </div>
                                <h2 class="ml-3 text-xl font-semibold text-gray-900 dark:text-white">GitHub do Projeto</h2>
                            </div>
                            <p class="mt-4 text-gray-500 dark:text-gray-400 text-sm leading-relaxed">
                                A sua ajuda no nosso projeto GitHub é essencial! Contribua de diversas formas:
                                codificando, reportando erros, sugerindo melhorias ou novas funcionalidades.
                                Cada contribuição é um passo para a evolução do projeto. Visite nosso repositório no
                                GitHub e junte-se a nós nessa jornada!
                            </p>
                        </div>
                    </a>

                </div>
            </div>


            <div class="flex flex-col justify-center mt-16 px-6 sm:items-center sm:justify-between sm:flex-row">
                <div class="text-center text-sm text-green-300 dark:text-green-400 sm:text-left">
                    <span aria-hidden="true" class="text-green-400 dark:text-green-600">
                        🚀 GitHub de
                        <a href="https://github.com/Unix-User" target="_blank"
                            class="group inline-flex items-center hover:text-green-700 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">
                            Unix-User
                        </a>
                    </span>
                </div>

                <div class="mt-4 text-center text-sm text-green-300 dark:text-green-400 sm:mt-0 sm:text-left">
                    <span>Desenvolvido com</span>
                    <a href="https://laravel.com" target="_blank"
                        class="ml-1 text-blue-500 hover:text-blue-600 dark:hover:text-blue-400 focus:outline focus:outline-2 focus:outline-blue-500">Laravel
                    </a>
                    <span>e</span>
                    <a href="https://vuejs.org" target="_blank"
                        class="ml-1 text-green-500 hover:text-green-600 dark:hover:text-green-400 focus:outline focus:outline-2 focus:outline-green-500">Vue.js
                    </a>
                </div>
            </div>
        </div>
    </div>
</template>