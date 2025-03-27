<script setup>
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import { ref, reactive, computed } from "vue";
import { usePage } from "@inertiajs/vue3";

const page = usePage();
const darkMode = computed(() => page.props.user?.preferredTheme === 'dark');

const serverStatus = reactive({
    serverVersion: "",
    isServerOnline: false,
    jogadores: "",
    maxJogadores: 20,
    online: [],
    isLoading: true,
    getJavaMemoryUsage: "",
});

const fetchData = async (endpoint) => {
    try {
        const response = await fetch(endpoint);
        if (!response.ok) throw new Error('Network response was not ok');
        return await response.json();
    } catch (error) {
        console.error(`Error fetching ${endpoint}:`, error);
        return null;
    }
};

const fetchServerStatus = async () => {
    serverStatus.isLoading = true;
    try {
        const [connectionData, playerCountData, serverVersionData, memoryUsageData] = await Promise.all([
            fetchData("/status/check-connection"),
            fetchData("/status/player-count"),
            fetchData("/status/server-version"),
            fetchData("/status/get-java-memory-usage")
        ]);

        serverStatus.isServerOnline = connectionData?.is_connected || false;
        serverStatus.jogadores = playerCountData?.[0]?.success || '0';
        serverStatus.serverVersion = serverVersionData?.[0]?.success || 'Indisponível';
        serverStatus.getJavaMemoryUsage = memoryUsageData?.success?.[0]?.success
            ? `${Math.round(memoryUsageData.success[0].success)} MB`
            : 'Indisponível';
    } catch (error) {
        console.error("Erro ao buscar status do servidor:", error);
    } finally {
        serverStatus.isLoading = false;
    }
};

fetchServerStatus();
</script>

<template>
    <div
        class="p-6 lg:p-8 bg-white/95 dark:bg-gray-800/95 backdrop-blur-sm border-b border-gray-200 dark:border-gray-700 transition-all duration-300 rounded-lg">
        <h1 class="mt-8 text-2xl font-medium text-gray-900 dark:text-gray-100 text-center transition-colors">
            Bem-vindo ao servidor UdiaNIX!
        </h1>
        <p class="mt-6 text-gray-600 dark:text-gray-300 leading-relaxed transition-colors">
            Esta é sua dashboard personalizada. Aqui, você pode monitorar o
            status do servidor, a versão do servidor, o estado do serviço e a
            quantidade de jogadores online. Explore e utilize todas as
            funcionalidades disponíveis.
        </p>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 mt-6 leading-relaxed">
            <div
                class="badge bg-indigo-500/90 dark:bg-indigo-600/90 hover:bg-indigo-600/90 dark:hover:bg-indigo-700/90 text-white px-4 py-2 rounded-lg shadow-md transition-all duration-200 backdrop-blur-sm flex items-center justify-center">
                <span class="font-semibold mr-2">Ver.:</span>
                <span class="truncate">
                    {{
                        serverStatus.isLoading
                            ? "Carregando..."
                            : serverStatus.serverVersion || "Indisponível"
                    }}
                </span>
            </div>
            <div
                class="badge bg-emerald-500/90 dark:bg-emerald-600/90 hover:bg-emerald-600/90 dark:hover:bg-emerald-700/90 text-white px-4 py-2 rounded-lg shadow-md transition-all duration-200 backdrop-blur-sm flex items-center justify-center">
                <span class="font-semibold mr-2">Status:</span>
                <span>
                    {{
                        serverStatus.isLoading
                            ? "Carregando..."
                            : serverStatus.isServerOnline
                                ? "Ativo"
                                : "Inativo"
                    }}
                </span>
            </div>
            <div
                class="badge bg-amber-500/90 dark:bg-amber-600/90 hover:bg-amber-600/90 dark:hover:bg-amber-700/90 text-white px-4 py-2 rounded-lg shadow-md transition-all duration-200 backdrop-blur-sm flex items-center justify-center">
                <span class="font-semibold mr-2">Memória:</span>
                <span>
                    {{
                        serverStatus.isLoading
                            ? "Carregando..."
                            : serverStatus.getJavaMemoryUsage || "Indisponível"
                    }}
                </span>
            </div>
            <div
                class="badge bg-rose-500/90 dark:bg-rose-600/90 hover:bg-rose-600/90 dark:hover:bg-rose-700/90 text-white px-4 py-2 rounded-lg shadow-md transition-all duration-200 backdrop-blur-sm flex items-center justify-center tooltip">
                <span class="font-semibold mr-2">Jogadores:</span>
                <span>
                    {{
                        serverStatus.isLoading
                            ? "Carregando..."
                            : `${serverStatus.jogadores} / ${serverStatus.maxJogadores}`
                    }}
                </span>
                <span class="tooltiptext">{{
                    serverStatus.online.join(", ") || "Nenhum jogador online"
                    }}</span>
            </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 lg:gap-8 mt-8">
            <a href="/user/profile"
                class="scale-100 p-6 bg-white/90 dark:bg-gray-800/80 dark:bg-gradient-to-bl from-gray-700/50 via-transparent dark:ring-1 dark:ring-inset dark:ring-white/5 rounded-lg shadow-lg hover:shadow-xl dark:shadow-none flex motion-safe:hover:scale-[1.01] transition-all duration-300 focus:outline focus:outline-2 focus:outline-green-500 backdrop-blur-sm">
                <div>
                    <div class="flex items-center">
                        <div
                            class="h-12 w-12 bg-green-50 dark:bg-green-800/30 flex items-center justify-center rounded-full shadow-md transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" height="1.5em" viewBox="0 0 448 512"
                                class="text-green-600 dark:text-green-400 transition-colors" fill="currentColor">
                                <path
                                    d="M224 256A128 128 0 1 0 224 0a128 128 0 1 0 0 256zm-45.7 48C79.8 304 0 383.8 0 482.3C0 498.7 13.3 512 29.7 512H418.3c16.4 0 29.7-13.3 29.7-29.7C448 383.8 368.2 304 269.7 304H178.3z" />
                            </svg>
                        </div>
                        <h2 class="ml-3 text-xl font-semibold text-gray-900 dark:text-white transition-colors">
                            Perfil do Usuário
                        </h2>
                    </div>
                    <p class="mt-4 text-gray-600 dark:text-gray-300 text-sm leading-relaxed transition-colors">
                        Gerencie sua conta, altere suas informações pessoais
                        e atualize sua senha de acesso ao servidor.
                    </p>
                </div>
                <div class="absolute bottom-4 right-4 text-xs text-gray-400 dark:text-gray-500 transition-colors">
                    Gerenciar conta
                </div>
            </a>

            <a href="/map"
                class="scale-100 p-6 bg-white/90 dark:bg-gray-800/80 dark:bg-gradient-to-bl from-gray-700/50 via-transparent dark:ring-1 dark:ring-inset dark:ring-white/5 rounded-lg shadow-lg hover:shadow-xl dark:shadow-none flex motion-safe:hover:scale-[1.01] transition-all duration-300 focus:outline focus:outline-2 focus:outline-yellow-500 backdrop-blur-sm relative">
                <div>
                    <div class="flex items-center">
                        <div
                            class="h-12 w-12 bg-yellow-50 dark:bg-yellow-800/30 flex items-center justify-center rounded-full shadow-md transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" height="1.5em" viewBox="0 0 576 512"
                                class="text-yellow-600 dark:text-yellow-400 transition-colors" fill="currentColor">
                                <path
                                    d="M384 476.1L192 421.2V35.9L384 90.8V476.1zm32-1.2V88.4L543.1 37.5c15.8-6.3 32.9 5.3 32.9 22.3V394.6c0 9.8-6 18.6-15.1 22.3L416 474.8zM15.1 95.1L160 37.2V423.6L32.9 474.5C17.1 480.8 0 469.2 0 452.2V117.4c0-9.8 6-18.6 15.1-22.3z" />
                            </svg>
                        </div>
                        <h2 class="ml-3 text-xl font-semibold text-gray-9 00 dark:text-white transition-colors">
                            Mapa Dinâmico
                        </h2>
                    </div>
                    <p class="mt-4 text-gray-600 dark:text-gray-300 text-sm leading-relaxed transition-colors">
                        Acompanhe em tempo real o mapa do servidor,
                        visualize locais importantes e movimentações.
                    </p>
                </div>
                <div class="absolute bottom-4 right-4 text-xs text-gray-400 dark:text-gray-500 transition-colors">
                    Ver mapa
                </div>
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 lg:gap-8 mt-8">
            <a href="/invite"
                class="scale-100 p-6 bg-white/90 dark:bg-gray-800/80 dark:bg-gradient-to-bl from-gray-700/50 via-transparent dark:ring-1 dark:ring-inset dark:ring-white/5 rounded-lg shadow-lg hover:shadow-xl dark:shadow-none flex motion-safe:hover:scale-[1.01] transition-all duration-300 focus:outline focus:outline-2 focus:outline-indigo-500 backdrop-blur-sm">
                <div>
                    <div class="flex items-center">
                        <div
                            class="h-12 w-12 bg-indigo-50 dark:bg-indigo-800/30 flex items-center justify-center rounded-full shadow-md transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" height="1.5em" viewBox="0 0 640 512"
                                class="text-indigo-600 dark:text-indigo-400 transition-colors" fill="currentColor">
                                <path
                                    d="M524.5 69.8a1.5 1.5 0 0 0 -.8-.7A485.1 485.1 0 0 0 404.1 32a1.8 1.8 0 0 0 -1.9 .9 337.5 337.5 0 0 0 -14.9 30.6 447.8 447.8 0 0 0 -134.4 0 309.5 309.5 0 0 0 -15.1-30.6 1.9 1.9 0 0 0 -1.9-.9A483.7 483.7 0 0 0 116.1 69.1a1.7 1.7 0 0 0 -.8 .7C39.1 183.7 18.2 294.7 28.4 404.4a2 2 0 0 0 .8 1.4A487.7 487.7 0 0 0 176 479.9a1.9 1.9 0 0 0 2.1-.7A348.2 348.2 0 0 0 208.1 430.4a1.9 1.9 0 0 0 -1-2.6 321.2 321.2 0 0 1 -45.9-21.9 1.9 1.9 0 0 1 -.2-3.1c3.1-2.3 6.2-4.7 9.1-7.1a1.8 1.8 0 0 1 1.9-.3c96.2 43.9 200.4 43.9 295.5 0a1.8 1.8 0 0 1 1.9 .2c2.9 2.4 6 4.9 9.1 7.2a1.9 1.9 0 0 1 -.2 3.1 301.4 301.4 0 0 1 -45.9 21.8 1.9 1.9 0 0 0 -1 2.6 391.1 391.1 0 0 0 30 48.8 1.9 1.9 0 0 0 2.1 .7A486 486 0 0 0 610.7 405.7a1.9 1.9 0 0 0 .8-1.4C623.7 277.6 590.9 167.5 524.5 69.8zM222.5 337.6c-29 0-52.8-26.6-52.8-59.2S193.1 219.1 222.5 219.1c29.7 0 53.3 26.8 52.8 59.2C275.3 311 251.9 337.6 222.5 337.6zm195.4 0c-29 0-52.8-26.6-52.8-59.2S388.4 219.1 417.9 219.1c29.7 0 53.3 26.8 52.8 59.2C470.7 311 447.5 337.6 417.9 337.6z" />
                            </svg>
                        </div>
                        <h2 class="ml-3 text-xl font-semibold text-gray-900 dark:text-white transition-colors">
                            Comunidade
                        </h2>
                    </div>
                    <p class="mt-4 text-gray-600 dark:text-gray-300 text-sm leading-relaxed transition-colors">
                        Participe do nosso Discord, interaja com a comunidade,
                        faça amigos e mantenha-se atualizado sobre o servidor.
                    </p>
                </div>
                <div class="absolute bottom-4 right-4 text-xs text-gray-400 dark:text-gray-500 transition-colors">
                    Clique para entrar
                </div>
            </a>

            <a href="/updates"
                class="scale-100 p-6 bg-white/90 dark:bg-gray-800/80 dark:bg-gradient-to-bl from-gray-700/50 via-transparent dark:ring-1 dark:ring-inset dark:ring-white/5 rounded-lg shadow-lg hover:shadow-xl dark:shadow-none flex motion-safe:hover:scale-[1.01] transition-all duration-300 focus:outline focus:outline-2 focus:outline-rose-500 backdrop-blur-sm relative">
                <div>
                    <div class="flex items-center">
                        <div
                            class="h-12 w-12 bg-rose-50 dark:bg-rose-800/30 flex items-center justify-center rounded-full shadow-md transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" height="1.5em" viewBox="0 0 576 512"
                                class="text-rose-600 dark:text-rose-400 transition-colors" fill="currentColor">
                                <path
                                    d="M64 0C28.7 0 0 28.7 0 64V448c0 35.3 28.7 64 64 64H320c35.3 0 64-28.7 64-64V428.7c-2.7 1.1-5.4 2-8.2 2.7l-60.1 15c-3 .7-6 1.2-9 1.4c-.9 .1-1.8 .2-2.7 .2H240c-6.1 0-11.6-3.4-14.3-8.8l-8.8-17.7c-1.7-3.4-5.1-5.5-8.8-5.5s-7.2 2.1-8.8 5.5l-8.8 17.7c-2.9 5.9-9.2 9.4-15.7 8.8s-12.1-5.1-13.9-11.3L144 381l-9.8 32.8c-6.1 20.3-24.8 34.2-46 34.2H80c-8.8 0-16-7.2-16-16s7.2-16 16-16h8.2c7.1 0 13.3-4.6 15.3-11.4l14.9-49.5c3.4-11.3 13.8-19.1 25.6-19.1s22.2 7.8 25.6 19.1l11.6 38.6c7.4-6.2 16.8-9.7 26.8-9.7c15.9 0 30.4 9 37.5 23.2l4.4 8.8h8.9c-3.1-8.8-3.7-18.4-1.4-27.8l15-60.1c2.8-11.3 8.6-21.5 16.8-29.7L384 203.6V160H256c-17.7 0-32-14.3-32-32V0H64zM256 0V128H384L256 0zM549.8 139.7c-15.6-15.6-40.9-15.6-56.6 0l-29.4 29.4 71 71 29.4-29.4c15.6-15.6 15.6-40.9 0-56.6l-14.4-14.4zM311.9 321c-4.1 4.1-7 9.2-8.4 14.9l-15 60.1c-1.4 5.5 .2 11.2 4.2 15.2s9.7 5.6 15.2 4.2l60.1-15c5.6-1.4 10.8-4.3 14.9-8.4L512.1 262.7l-71-71L311.9 321z" />
                            </svg>
                        </div>
                        <h2 class="ml-3 text-xl font-semibold text-gray-900 dark:text-white transition-colors">
                            Atualizações
                        </h2>
                    </div>
                    <p class="mt-4 text-gray-600 dark:text-gray-300 text-sm leading-relaxed transition-colors">
                        Acesse as últimas atualizações e novidades do servidor
                        para melhorar sua experiência de jogo.
                    </p>
                </div>
                <div class="absolute bottom-4 right-4 text-xs text-gray-400 dark:text-gray-500 transition-colors">
                    Ver novidades
                </div>
            </a>
        </div>
    </div>
</template>
<style>
.tooltip {
    position: relative;
    cursor: help;
}

.tooltip .tooltiptext {
    white-space: pre-wrap;
    visibility: hidden;
    width: auto;
    min-width: 150px;
    max-width: 300px;
    background-color: rgba(51, 51, 51, 0.95);
    color: #fff;
    text-align: center;
    border-radius: 8px;
    padding: 8px 12px;
    position: fixed;
    /* Alterado de absolute para fixed */
    z-index: 9999;
    /* Aumentado o z-index para garantir que fique acima de todos os elementos */
    top: -45px;
    left: 50%;
    transform: translateX(-50%);
    opacity: 0;
    transition: opacity 0.3s, transform 0.3s;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    font-size: 0.875rem;
    transform: translateX(-50%) translateY(5px);
    pointer-events: none;
    /* Evita que o tooltip interfira com interações */
}

.tooltip .tooltiptext::after {
    content: "";
    position: absolute;
    top: 100%;
    left: 50%;
    margin-left: -5px;
    border-width: 5px;
    border-style: solid;
    border-color: rgba(51, 51, 51, 0.95) transparent transparent transparent;
}

.tooltip:hover .tooltiptext {
    visibility: visible;
    opacity: 1;
    transform: translateX(-50%) translateY(0);
}

.badge {
    position: relative;
    overflow: visible;
    /* Alterado de hidden para visible para evitar cortes */
}
</style>
