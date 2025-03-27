<script setup>
import { ref, onMounted, onUnmounted } from "vue";
import axios from "axios";

const props = defineProps({
    username: {
        type: String,
        required: true,
    },
});

const command = ref("");
const messages = ref([]);

const connectRcon = async () => {
    try {
        const response = await axios.post("/execute-command", {
            command: `execute as @a[distance=..-1] run say Usuário ${props.username} se conectou ao servidor RCON no console do site.`,
        });
        if (response.status !== 200) {
            throw new Error(
                `Server responded with status code: ${response.status}`
            );
        }
    } catch (error) {
        console.error(`AxiosError: ${error.message}`);
    }
};

const disconnectRcon = async () => {
    try {
        await axios.post("/close-connection");
    } catch (error) {
        console.error(`AxiosError: ${error.message}`);
    }
};

const sendCommand = async () => {
    try {
        const response = await axios.post("/execute-command", {
            command: command.value.trim(),
        });
        if (response.data) {
            const message =
                response.data.response || "No response from the server";
            const sanitizedMessage = message.replace(
                /\u00A7[0-9A-FK-ORa-fk-or]/g,
                ""
            );
            messages.value.push(sanitizedMessage);
            console.log("Server response:", sanitizedMessage);
        } else {
            console.error("Invalid response from the server");
        }
    } catch (error) {
        if (error.response && error.response.status === 500) {
            console.error(
                "Server error: " + error.response.data.message
            );
        } else {
            console.error(`AxiosError: ${error.message}`);
        }
    }
    command.value = "";
};

const clearLog = () => {
    command.value = "";
    messages.value = [];
};

onMounted(connectRcon);
onUnmounted(disconnectRcon);

defineExpose({
    command,
    messages,
    sendCommand,
    clearLog,
});
</script>

<template>
    <div>
        <div class="max-w-7xl mx-auto">
            <div
                class="bg-white/90 dark:bg-gray-800/90 backdrop-blur-sm overflow-hidden shadow-xl sm:rounded-lg transition-all duration-300 ease-in-out hover:shadow-2xl">
                <div class="flex justify-between items-center px-4 pt-4">
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100 transition-colors">
                        <i class="fas fa-terminal mr-2"></i>Shell de Comandos RCON
                    </h3>
                    <div class="flex space-x-3">
                        <a class="text-sm text-gray-500 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors duration-300"
                            href="http://minecraft.gamepedia.com/Commands" target="_blank"
                            title="Lista de Comandos">
                            <i class="fas fa-question-circle"></i>
                            <span class="hidden sm:inline ml-1">Comandos</span>
                        </a>
                        <a class="text-sm text-gray-500 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors duration-300"
                            href="http://www.minecraftinfo.com/idlist.htm" target="_blank"
                            title="IDs de Itens">
                            <i class="fas fa-info-circle"></i>
                            <span class="hidden sm:inline ml-1">IDs de Itens</span>
                        </a>
                    </div>
                </div>
                <div class="container mx-auto">
                    <div class="space-y-4">
                        <div class="rounded-lg shadow-sm px-4 bg-white/80 dark:bg-gray-800/60 backdrop-blur-sm">
                            <div class="mt-4 bg-white/50 dark:bg-gray-700/50 p-4 rounded-lg">
                                <ul class="list-none overflow-y-auto h-64 space-y-2 pr-2">
                                    <li v-for="(message, index) in messages" :key="index"
                                        class="p-3 bg-white/80 dark:bg-gray-800/80 rounded-lg shadow-sm text-gray-900 dark:text-gray-200 backdrop-blur-sm transition-colors duration-300">
                                        {{ message }}
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="flex items-center space-x-3 px-4 pb-4">
                            <input type="text"
                                class="flex-grow rounded-lg p-3 bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm border border-gray-200/50 dark:border-gray-700/50 focus:ring-2 focus:ring-blue-600 focus:border-blue-600 dark:focus:ring-blue-400 dark:focus:border-blue-400 transition-all duration-300"
                                v-model.trim="command" @keyup.enter="sendCommand"
                                placeholder="Digite o comando aqui..." />
                            <button type="button"
                                class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-all duration-300 hover:scale-105 active:scale-95"
                                @click="sendCommand" title="Enviar Comando">
                                <i class="fas fa-paper-plane"></i>
                                <span class="hidden sm:inline ml-2">Enviar</span>
                            </button>
                            <button type="button"
                                class="px-4 py-2.5 bg-yellow-500 hover:bg-yellow-600 text-white font-semibold rounded-lg transition-all duration-300 hover:scale-105 active:scale-95"
                                @click="clearLog" title="Limpar Log">
                                <i class="fas fa-eraser"></i>
                                <span class="hidden sm:inline ml-2">Limpar</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Smooth transitions for theme changes */
.theme-transition {
    transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease, opacity 0.3s ease;
}

/* Custom scrollbar */
::-webkit-scrollbar {
    width: 8px;
}

::-webkit-scrollbar-track {
    @apply bg-gray-100/50 dark:bg-gray-700/50 rounded-full;
}

::-webkit-scrollbar-thumb {
    @apply bg-gray-300 dark:bg-gray-600 rounded-full hover:bg-gray-400 dark:hover:bg-gray-500 transition-colors duration-300;
}
</style>
