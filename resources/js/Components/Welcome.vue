<script setup>
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import { ref, reactive } from "vue";
import axios from "axios";

const serverStatus = reactive({
    serverVersion: "",
    isServerOnline: false,
    jogadores: "",
    maxJogadores: 20,
    online: [],
    isLoading: true,
});
const fetchServerStatus = async () => {
    try {
        const [checkConnectionResponse, playerCountResponse, serverVersionResponse, getJavaMemoryUsageResponse] = await Promise.all([
            axios.get("/api/check-connection"),
            axios.get("/api/player-count"),
            axios.get("/api/server-version"),
            axios.get("/api/get-java-memory-usage")
        ]);

        console.log("Server Online Status:", checkConnectionResponse.data.is_connected);
        console.log("Server Version:", serverVersionResponse.data[0].success);
        console.log("Max Jogadores:", playerCountResponse.data[0].success);
        console.log("Memory Usage:", getJavaMemoryUsageResponse.data.success);
        serverStatus.getJavaMemoryUsage = Math.round(getJavaMemoryUsageResponse.data.success[0].success) + " MB";
        serverStatus.serverVersion = serverVersionResponse.data[0].success;
        serverStatus.isServerOnline = checkConnectionResponse.data.is_connected;
        serverStatus.jogadores = playerCountResponse.data[0].success;
        serverStatus.isLoading = false;
    } catch (error) {
        console.error(error);
        serverStatus.isLoading = false;
    }
};

fetchServerStatus();
</script>

<template>
    <div
        class="p-6 lg:p-8 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700"
    >
        <h1
            class="mt-8 text-2xl font-medium text-gray-900 dark:text-white text-center"
        >
            Bem-vindo ao servidor UdiaNIX!
        </h1>
        <p class="mt-6 text-gray-500 dark:text-gray-300 leading-relaxed">
            Esta é sua dashboard personalizada. Aqui, você pode monitorar o
            status do servidor, a versão do servidor, o estado do serviço e a
            quantidade de jogadores online. Explore e utilize todas as
            funcionalidades disponíveis.
        </p>
        <div
            class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 mt-6 text-gray-500 dark:text-gray-300 leading-relaxed"
        >
            <div class="badge bg-blue-500 text-white px-3 py-1 rounded-full">
                Ver.:
                {{
                    serverStatus.isLoading
                        ? "Carregando..."
                        : serverStatus.serverVersion || "Indisponível"
                }}
            </div>
            <div class="badge bg-green-500 text-white px-3 py-1 rounded-full">
                Status do servidor:
                {{
                    serverStatus.isLoading
                        ? "Carregando..."
                        : serverStatus.isServerOnline
                        ? "Ativo"
                        : "Inativo"
                }}
            </div>
            <div class="badge bg-yellow-500 text-white px-3 py-1 rounded-full">
                Uso de Memoria:
                {{
                    serverStatus.isLoading
                        ? "Carregando..."
                        : serverStatus.getJavaMemoryUsage || "Indisponível"
                }}
            </div>
            <div
                class="badge bg-red-500 text-white px-3 py-1 rounded-full tooltip"
            >
                Jogadores Online:
                {{
                    serverStatus.isLoading
                        ? "Carregando..."
                        : `${serverStatus.jogadores} / ${serverStatus.maxJogadores}`
                }}
                <span class="tooltiptext">{{
                    serverStatus.online.join(", ") || "Nenhum jogador online"
                }}</span>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 lg:gap-8 mt-6">
            <a
                href="/invite"
                class="scale-100 p-6 bg-white dark:bg-gray-800/50 dark:bg-gradient-to-bl from-gray-700/50 via-transparent dark:ring-1 dark:ring-inset dark:ring-white/5 rounded-lg shadow-2xl shadow-gray-500/20 dark:shadow-none flex motion-safe:hover:scale-[1.01] transition-all duration-250 focus:outline focus:outline-2 focus:outline-red-500"
            >
                <div>
                    <div class="flex items-center">
                        <div
                            class="h-12 w-12 bg-red-50 dark:bg-red-800/20 flex items-center justify-center rounded-full"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                height="1em"
                                viewBox="0 0 640 512"
                                :fill="darkMode ? '#000000' : '#FFFFFF'"
                            >
                                <path
                                    d="M524.5 69.8a1.5 1.5 0 0 0 -.8-.7A485.1 485.1 0 0 0 404.1 32a1.8 1.8 0 0 0 -1.9 .9 337.5 337.5 0 0 0 -14.9 30.6 447.8 447.8 0 0 0 -134.4 0 309.5 309.5 0 0 0 -15.1-30.6 1.9 1.9 0 0 0 -1.9-.9A483.7 483.7 0 0 0 116.1 69.1a1.7 1.7 0 0 0 -.8 .7C39.1 183.7 18.2 294.7 28.4 404.4a2 2 0 0 0 .8 1.4A487.7 487.7 0 0 0 176 479.9a1.9 1.9 0 0 0 2.1-.7A348.2 348.2 0 0 0 208.1 430.4a1.9 1.9 0 0 0 -1-2.6 321.2 321.2 0 0 1 -45.9-21.9 1.9 1.9 0 0 1 -.2-3.1c3.1-2.3 6.2-4.7 9.1-7.1a1.8 1.8 0 0 1 1.9-.3c96.2 43.9 200.4 43.9 295.5 0a1.8 1.8 0 0 1 1.9 .2c2.9 2.4 6 4.9 9.1 7.2a1.9 1.9 0 0 1 -.2 3.1 301.4 301.4 0 0 1 -45.9 21.8 1.9 1.9 0 0 0 -1 2.6 391.1 391.1 0 0 0 30 48.8 1.9 1.9 0 0 0 2.1 .7A486 486 0 0 0 610.7 405.7a1.9 1.9 0 0 0 .8-1.4C623.7 277.6 590.9 167.5 524.5 69.8zM222.5 337.6c-29 0-52.8-26.6-52.8-59.2S193.1 219.1 222.5 219.1c29.7 0 53.3 26.8 52.8 59.2C275.3 311 251.9 337.6 222.5 337.6zm195.4 0c-29 0-52.8-26.6-52.8-59.2S388.4 219.1 417.9 219.1c29.7 0 53.3 26.8 52.8 59.2C470.7 311 447.5 337.6 417.9 337.6z"
                                />
                            </svg>
                        </div>
                        <h2
                            class="ml-3 text-xl font-semibold text-gray-900 dark:text-white"
                        >
                            Comunidade
                        </h2>
                    </div>
                    <p
                        class="mt-4 text-gray-500 dark:text-gray-300 text-sm leading-relaxed"
                    >
                        Participe do nosso Discord, interaja com a comunidade,
                        faça amigos e mantenha-se atualizado sobre o servidor.
                    </p>
                </div>
            </a>

            <a
                href="/update/posts"
                class="scale-100 p-6 bg-white dark:bg-gray-800/50 dark:bg-gradient-to-bl from-gray-700/50 via-transparent dark:ring-1 dark:ring-inset dark:ring-white/5 rounded-lg shadow-2xl shadow-gray-500/20 dark:shadow-none flex motion-safe:hover:scale-[1.01] transition-all duration-250 focus:outline focus:outline-2 focus:outline-red-500"
            >
                <div>
                    <div class="flex items-center">
                        <div
                            class="h-12 w-12 bg-red-50 dark:bg-red-800/20 flex items-center justify-center rounded-full"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                height="1em"
                                viewBox="0 0 576 512"
                                :fill="darkMode ? '#000000' : '#FFFFFF'"
                            >
                                <!--! Font Awesome Free 6.4.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. -->
                                <path
                                    d="M64 0C28.7 0 0 28.7 0 64V448c0 35.3 28.7 64 64 64H320c35.3 0 64-28.7 64-64V428.7c-2.7 1.1-5.4 2-8.2 2.7l-60.1 15c-3 .7-6 1.2-9 1.4c-.9 .1-1.8 .2-2.7 .2H240c-6.1 0-11.6-3.4-14.3-8.8l-8.8-17.7c-1.7-3.4-5.1-5.5-8.8-5.5s-7.2 2.1-8.8 5.5l-8.8 17.7c-2.9 5.9-9.2 9.4-15.7 8.8s-12.1-5.1-13.9-11.3L144 381l-9.8 32.8c-6.1 20.3-24.8 34.2-46 34.2H80c-8.8 0-16-7.2-16-16s7.2-16 16-16h8.2c7.1 0 13.3-4.6 15.3-11.4l14.9-49.5c3.4-11.3 13.8-19.1 25.6-19.1s22.2 7.8 25.6 19.1l11.6 38.6c7.4-6.2 16.8-9.7 26.8-9.7c15.9 0 30.4 9 37.5 23.2l4.4 8.8h8.9c-3.1-8.8-3.7-18.4-1.4-27.8l15-60.1c2.8-11.3 8.6-21.5 16.8-29.7L384 203.6V160H256c-17.7 0-32-14.3-32-32V0H64zM256 0V128H384L256 0zM549.8 139.7c-15.6-15.6-40.9-15.6-56.6 0l-29.4 29.4 71 71 29.4-29.4c15.6-15.6 15.6-40.9 0-56.6l-14.4-14.4zM311.9 321c-4.1 4.1-7 9.2-8.4 14.9l-15 60.1c-1.4 5.5 .2 11.2 4.2 15.2s9.7 5.6 15.2 4.2l60.1-15c5.6-1.4 10.8-4.3 14.9-8.4L512.1 262.7l-71-71L311.9 321z"
                                />
                            </svg>
                        </div>
                        <h2
                            class="ml-3 text-xl font-semibold text-gray-900 dark:text-white"
                        >
                            Atualizações
                        </h2>
                    </div>
                    <p
                        class="mt-4 text-gray-500 dark:text-gray-300 text-sm leading-relaxed"
                    >
                        Acesse as últimas atualizações e novidades do servidor
                        para melhorar sua experiência de jogo.
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
}
</style>
