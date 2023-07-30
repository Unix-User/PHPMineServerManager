<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
</script>

<template>
        <div class="p-6 lg:p-8 bg-white border-b border-gray-200">
            <ApplicationLogo class="block h-12 w-auto" />

            <h1 class="mt-8 text-2xl font-medium text-gray-900 text-center">
                Bem-vindo ao nosso servidor de Minecraft!
            </h1>

            <p class="mt-6 text-gray-500 leading-relaxed">
                Nosso servidor de Minecraft oferece uma experiência de jogo emocionante e imersiva. Junte-se à nossa vibrante comunidade de
                jogadores e explore o vasto e único mundo que criamos para você.
                Enfrente monstros ferozes, construa estruturas impressionantes e embarque em aventuras emocionantes. As possibilidades
                são infinitas!
            </p>

            <!-- Recursos e destaques do servidor -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 lg:gap-8 mt-6 p-6 lg:p-8">
                <div>
                    <div class="flex items-center">
                        <!-- Ícone para um recurso do servidor -->
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            class="w-6 h-6 stroke-gray-400">
                            <path stroke-linecap="round"
                                d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                        </svg>
                        <h2 class="ml-3 text-xl font-semibold text-gray-900">
                            <a href="/features">Jogabilidade Única</a>
                        </h2>
                    </div>

                    <p class="mt-4 text-gray-500 text-sm leading-relaxed">
                        Experimente uma jogabilidade personalizada projetada para oferecer uma aventura única no Minecraft. Nós temos
                        criado novos desafios e mecânicas emocionantes para mantê-lo engajado.
                    </p>
                </div>

                <div>
                    <div class="flex items-center">
                        <!-- Ícone para outro recurso do servidor -->
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            class="w-6 h-6 stroke-gray-400">
                            <path stroke-linecap="round"
                                d="M15.75 10.5l4.72-4.72a.75.75 0 011.28.53v11.38a.75.75 0 01-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 002.25-2.25v-9a2.25 2.25 0 00-2.25-2.25h-9A2.25 2.25 0 002.25 7.5v9a2.25 2.25 0 002.25 2.25z" />
                        </svg>
                        <h2 class="ml-3 text-xl font-semibold text-gray-900">
                            <a href="/community">Comunidade Ativa</a>
                        </h2>
                    </div>

                    <p class="mt-4 text-gray-500 text-sm leading-relaxed">
                        Junte-se à nossa comunidade amigável e ativa de entusiastas do Minecraft. Faça novos amigos, colabore em
                        construções épicas e participe de eventos emocionantes organizados pela nossa dedicada equipe de staff.
                    </p>
                </div>

                <div>
                    <div class="flex items-center">
                        <!-- Ícone para outro recurso do servidor -->
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            class="w-6 h-6 stroke-gray-400">
                            <path stroke-linecap="round"
                                d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                        </svg>
                        <h2 class="ml-3 text-xl font-semibold text-gray-900">
                            <a href="/store">Loja no Jogo</a>
                        </h2>
                    </div>

                    <p class="mt-4 text-gray-500 text-sm leading-relaxed">
                        Descubra nossa loja no jogo onde você pode adquirir itens exclusivos, ranks e outros benefícios emocionantes para
                        melhorar sua jogabilidade e se destacar na comunidade.
                    </p>
                </div>

                <div>
                    <div class="flex items-center">
                        <!-- Ícone para outro recurso do servidor -->
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            class="w-6 h-6 stroke-gray-400">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                        </svg>
                    <h2 class="ml-3 text-xl font-semibold text-gray-900">
                        <a href="/how-to-join">Como Participar</a>
                    </h2>
                </div>

                <p class="mt-4 text-gray-500 text-sm leading-relaxed">
                    Novo em nosso servidor? Sem problemas! Siga nosso guia passo a passo sobre como participar de nossa comunidade Minecraft
                    e comece sua jornada épica hoje.
                </p>
            </div>
        </div>
    </div></template>
