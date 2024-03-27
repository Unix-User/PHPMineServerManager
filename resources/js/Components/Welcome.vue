<script setup>
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import { ref, reactive } from "vue";
import axios from "axios";

const serverStatus = reactive({
    javaVersion: "",
    isProgramRunning: false,
    serverVersion: "",
    jogadores: "",
    maxJogadores: "",
    online: [],
    isLoading: true,
});

axios
    .get("/status")
    .then((response) => {
        if (response.data) {
            Object.assign(serverStatus, response.data);
            serverStatus.isLoading = false;
        }
    })
    .catch((error) => {
        console.error(error);
    });
</script>

<template>
    <div class="p-6 lg:p-8 bg-white border-b border-gray-200">
        <h1 class="mt-8 text-2xl font-medium text-gray-900 text-center">
            Bem-vindo ao servidor UdiaNIX!
        </h1>
        <p class="mt-6 text-gray-500 leading-relaxed">
            Bem-vindo à sua dashboard personalizada. Aqui, você tem acesso a uma
            variedade de informações e funcionalidades. Você pode monitorar o
            status atual do servidor, verificar a versão do Java em uso,
            verificar se o serviço está ativo e a versão atual do servidor. Além
            disso, você pode ver a quantidade de jogadores online em tempo real.
            Explore e aproveite todas as funcionalidades disponíveis para você.
        </p>
        <div
            class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 mt-6 text-gray-500 leading-relaxed"
        >
            <div class="badge bg-blue-500 text-white px-3 py-1 rounded-full">
                Versão do Java:
                {{
                    serverStatus.isLoading
                        ? "Carregando..."
                        : serverStatus.javaVersion
                }}
            </div>
            <div class="badge bg-green-500 text-white px-3 py-1 rounded-full">
                Status do Serviço:
                {{
                    serverStatus.isLoading
                        ? "Carregando..."
                        : serverStatus.isProgramRunning
                        ? "Rodando"
                        : "Parado"
                }}
            </div>
            <div class="badge bg-yellow-500 text-white px-3 py-1 rounded-full">
                Versão do Servidor:
                {{
                    serverStatus.isLoading
                        ? "Carregando..."
                        : serverStatus.serverVersion
                }}
            </div>
            <div
                class="badge bg-red-500 text-white px-3 py-1 rounded-full tooltip"
            >
                Jogadores Online:
                {{
                    serverStatus.isLoading
                        ? "Carregando..."
                        : serverStatus.jogadores +
                          " / " +
                          serverStatus.maxJogadores
                }}
                <span class="tooltiptext">{{
                    serverStatus.online.join(", ")
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
                            >
                                <!--! Font Awesome Free 6.4.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. -->
                                <path
                                    d="M524.531,69.836a1.5,1.5,0,0,0-.764-.7A485.065,485.065,0,0,0,404.081,32.03a1.816,1.816,0,0,0-1.923.91,337.461,337.461,0,0,0-14.9,30.6,447.848,447.848,0,0,0-134.426,0,309.541,309.541,0,0,0-15.135-30.6,1.89,1.89,0,0,0-1.924-.91A483.689,483.689,0,0,0,116.085,69.137a1.712,1.712,0,0,0-.788.676C39.068,183.651,18.186,294.69,28.43,404.354a2.016,2.016,0,0,0,.765,1.375A487.666,487.666,0,0,0,176.02,479.918a1.9,1.9,0,0,0,2.063-.676A348.2,348.2,0,0,0,208.12,430.4a1.86,1.86,0,0,0-1.019-2.588,321.173,321.173,0,0,1-45.868-21.853,1.885,1.885,0,0,1-.185-3.126c3.082-2.309,6.166-4.711,9.109-7.137a1.819,1.819,0,0,1,1.9-.256c96.229,43.917,200.41,43.917,295.5,0a1.812,1.812,0,0,1,1.924.233c2.944,2.426,6.027,4.851,9.132,7.16a1.884,1.884,0,0,1-.162,3.126,301.407,301.407,0,0,1-45.89,21.83,1.875,1.875,0,0,0-1,2.611,391.055,391.055,0,0,0,30.014,48.815,1.864,1.864,0,0,0,2.063.7A486.048,486.048,0,0,0,610.7,405.729a1.882,1.882,0,0,0,.765-1.352C623.729,277.594,590.933,167.465,524.531,69.836ZM222.491,337.58c-28.972,0-52.844-26.587-52.844-59.239S193.056,219.1,222.491,219.1c29.665,0,53.306,26.82,52.843,59.239C275.334,310.993,251.924,337.58,222.491,337.58Zm195.38,0c-28.971,0-52.843-26.587-52.843-59.239S388.437,219.1,417.871,219.1c29.667,0,53.307,26.82,52.844,59.239C470.715,310.993,447.538,337.58,417.871,337.58Z"
                                />
                            </svg>
                        </div>
                        <h2 class="ml-3 text-xl font-semibold text-gray-900">
                            Comunidade
                        </h2>
                    </div>
                    <p class="mt-4 text-gray-500 text-sm leading-relaxed">
                        Visite nosso Discord e junte-se à comunidade! Faça
                        amigos e fique por dentro de tudo que acontece no
                        servidor.
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
                                viewBox="0 0 512 512"
                            >
                                <!--! Font Awesome Free 6.4.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. -->
                                <path
                                    d="M352 320c88.4 0 160-71.6 160-160c0-15.3-2.2-30.1-6.2-44.2c-3.1-10.8-16.4-13.2-24.3-5.3l-76.8 76.8c-3 3-7.1 4.7-11.3 4.7H336c-8.8 0-16-7.2-16-16V118.6c0-4.2 1.7-8.3 4.7-11.3l76.8-76.8c7.9-7.9 5.4-21.2-5.3-24.3C382.1 2.2 367.3 0 352 0C263.6 0 192 71.6 192 160c0 19.1 3.4 37.5 9.5 54.5L19.9 396.1C7.2 408.8 0 426.1 0 444.1C0 481.6 30.4 512 67.9 512c18 0 35.3-7.2 48-19.9L297.5 310.5c17 6.2 35.4 9.5 54.5 9.5zM80 408a24 24 0 1 1 0 48 24 24 0 1 1 0-48z"
                                />
                            </svg>
                        </div>
                        <h2 class="ml-3 text-xl font-semibold text-gray-900">
                            Atualizações
                        </h2>
                    </div>
                    <p class="mt-4 text-gray-500 text-sm leading-relaxed">
                        Acesse as últimas atualizações do nosso servidor. Fique
                        por dentro de todas as novidades e melhorias
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
}
</style>
