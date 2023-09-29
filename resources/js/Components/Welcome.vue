<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { ref, reactive } from 'vue';
import axios from 'axios';

const serverStatus = reactive({
    javaVersion: '',
    isProgramRunning: false,
    serverVersion: '',
    jogadores: '',
    maxJogadores: '',
    online: [],
    isLoading: true
});

axios.get('/status')
    .then(response => {
        if (response.data) {
            Object.assign(serverStatus, response.data);
            serverStatus.isLoading = false;
        }
    })
    .catch(error => {
        console.error(error);
    });
</script>

<template>
    <div class="p-6 lg:p-8 bg-white border-b border-gray-200">
        <h1 class="mt-8 text-2xl font-medium text-gray-900 text-center">
            Bem-vindo ao servidor UdiaNIX!
        </h1>
        <p class="mt-6 text-gray-500 leading-relaxed">
            Bem-vindo à sua dashboard personalizada. Aqui, você tem acesso a uma variedade de informações e funcionalidades.
            Você pode monitorar o status atual do servidor, verificar a versão do Java em uso, verificar se o serviço está
            ativo e a versão atual do servidor. Além disso, você pode ver a quantidade de jogadores online em tempo real.
            Explore e aproveite todas as funcionalidades disponíveis para você.
        </p>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 mt-6 text-gray-500 leading-relaxed">
            <div class="badge bg-blue-500 text-white px-3 py-1 rounded-full">
                Versão do Java: {{ serverStatus.isLoading ? 'Carregando...' : serverStatus.javaVersion }}
            </div>
            <div class="badge bg-green-500 text-white px-3 py-1 rounded-full">
                Status do Serviço: {{ serverStatus.isLoading ? 'Carregando...' : (serverStatus.isProgramRunning ? 'Rodando'
                    : 'Parado') }}
            </div>
            <div class="badge bg-yellow-500 text-white px-3 py-1 rounded-full">
                Versão do Servidor: {{ serverStatus.isLoading ? 'Carregando...' : serverStatus.serverVersion }}
            </div>
            <div class="badge bg-red-500 text-white px-3 py-1 rounded-full tooltip">
                Jogadores Online: {{ serverStatus.isLoading ? 'Carregando...' : (serverStatus.jogadores + ' / ' +
                    serverStatus.maxJogadores) }}
                <span class="tooltiptext">{{ serverStatus.online.join(', ') }}</span>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 lg:gap-8 mt-6">
            <a href="https://discord.gg/NcY9UvujFY"
                class="scale-100 p-6 bg-white dark:bg-gray-800/50 dark:bg-gradient-to-bl from-gray-700/50 via-transparent dark:ring-1 dark:ring-inset dark:ring-white/5 rounded-lg shadow-2xl shadow-gray-500/20 dark:shadow-none flex motion-safe:hover:scale-[1.01] transition-all duration-250 focus:outline focus:outline-2 focus:outline-red-500">
                <div>
                    <div class="flex items-center">
                        <div class="h-12 w-12 bg-red-50 dark:bg-red-800/20 flex items-center justify-center rounded-full">
                            <text x="50%" y="50%" font-size="20" text-anchor="middle" dy=".3em">📌</text>
                        </div>
                        <h2 class="ml-3 text-xl font-semibold text-gray-900">
                            Comunidade
                        </h2>
                    </div>
                    <p class="mt-4 text-gray-500 text-sm leading-relaxed">
                        Visite nosso Discord e junte-se à comunidade! Faça amigos e fique por dentro de tudo que acontece no
                        servidor.
                    </p>
                </div>
            </a>

            <a href="/update/posts"
                class="scale-100 p-6 bg-white dark:bg-gray-800/50 dark:bg-gradient-to-bl from-gray-700/50 via-transparent dark:ring-1 dark:ring-inset dark:ring-white/5 rounded-lg shadow-2xl shadow-gray-500/20 dark:shadow-none flex motion-safe:hover:scale-[1.01] transition-all duration-250 focus:outline focus:outline-2 focus:outline-red-500">
                <div>
                    <div class="flex items-center">
                        <div class="h-12 w-12 bg-red-50 dark:bg-red-800/20 flex items-center justify-center rounded-full">
                            <text x="50%" y="50%" font-size="20" text-anchor="middle" dy=".3em">📣</text>
                        </div>
                        <h2 class="ml-3 text-xl font-semibold text-gray-900">
                            Atualizações
                        </h2>
                    </div>
                    <p class="mt-4 text-gray-500 text-sm leading-relaxed">
                        Acesse as últimas atualizações do nosso servidor. Fique por dentro de todas as novidades e melhorias
                        implementadas para melhorar sua experiência de jogo.
                    </p>
                </div>
            </a>
        </div>
    </div>
</template>

<style>
.tooltip {
    position: relative;
    display: inline-block;
}

.tooltip .tooltiptext {
    white-space: pre-wrap;
    visibility: hidden;
    width: auto;
    min-width: 120px;
    background-color: #333;
    color: #fff;
    text-align: center;
    border-radius: 5px;
    padding: 5px;
    position: absolute;
    z-index: 1;
    top: -30px;
    left: 50%;
    transform: translateX(-50%);
    opacity: 0;
    transition: opacity 0.3s;
}

.tooltip:hover .tooltiptext {
    visibility: visible;
    opacity: 1;
}</style>