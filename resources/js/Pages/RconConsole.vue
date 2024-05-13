<script>
import { ref, onMounted, onUnmounted } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import axios from 'axios';

export default {
    
    components: {
        AppLayout,
    },
    props: {
        username: {
            type: String,
            required: true
        }
    },
    setup(props) {
        const command = ref('');
        const messages = ref([]);

        const connectRcon = async () => {
            try {
                const response = await axios.post('/execute-command', {
                    command: `execute as @a[distance=..-1] run say Usuário ${ props.username } se conectou ao servidor RCON no console do site.`,
                });
            if (response.status !== 200) {
                throw new Error(`Server responded with status code: ${response.status}`);
            }
        } catch (error) {
            console.error(`AxiosError: ${error.message}`);
        }
    };

    const disconnectRcon = async () => {
        try {
            await axios.post('/close-connection');
        } catch (error) {
            console.error(`AxiosError: ${error.message}`);
        }
    };

    const sendCommand = async () => {
        try {
            const response = await axios.post('/execute-command', {
                command: command.value.trim(), // Clean input on send command
            });
            if (response.data) {
                const message = response.data.response || 'No response from the server';
                const sanitizedMessage = message.replace(/\u00A7[0-9A-FK-ORa-fk-or]/g, ''); // Convert Minecraft color codes but keep line breaks
                messages.value.push(sanitizedMessage)
                console.log('Server response:', sanitizedMessage);

            } else {

                console.error('Invalid response from the server');
            }



        } catch (error) {
            if (error.response && error.response.status === 500) {
                console.error('Server error: ' + error.response.data.message);
            } else {
                console.error(`AxiosError: ${error.message}`);
            }
        }
        command.value = ''; // Clear the command input after sending
    };

    const clearLog = () => {
        command.value = '';
        messages.value = [];
    };

    onMounted(connectRcon);
        onUnmounted(disconnectRcon);

        return {
        command,
        messages,
        sendCommand,
        clearLog,
    };
},
};
</script>

<template>
    <AppLayout title="Console">
<template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">                Console RCON
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-5">
                    <div class="container mx-auto" id="content">
                        <div id="consoleRow">
                            <div class="bg-white dark:bg-gray-700 rounded shadow-sm p-4 mb-4" id="consoleContent">
                                <div class="flex justify-between items-center">
                                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                                        <i class="fas fa-terminal"></i> Painel de Controle
                                    </h3>
                                    <div class="space-x-2">
                                        <a class="text-sm text-gray-500 dark:text-gray-300 hover:text-gray-700 dark:hover:text-white"
                                            href="http://minecraft.gamepedia.com/Commands" target="_blank" title="Lista de Comandos">
                                            <i class="fas fa-question-circle"></i>
                                            <span class="hidden sm:inline"> Comandos</span>
                                        </a>
                                        <a class="text-sm text-gray-500 dark:text-gray-300 hover:text-gray-700 dark:hover:text-white"
                                            href="http://www.minecraftinfo.com/idlist.htm" target="_blank"
                                            title="IDs de Itens">
                                            <i class="fas fa-info-circle"></i>
                                            <span class="hidden sm:inline"> IDs de Itens</span>
                                        </a>
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <ul class="list-none" id="groupConsole">
                                        <li v-for="(message, index) in messages" :key="index">
                                            {{ message }}
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2" id="consoleCommand">
                                <div class="flex items-center space-x-2">
                                    <input id="chkAutoScroll" type="checkbox" checked="true" autocomplete="off"
                                        title="Rolagem Automática" />
                                    <i class="fas fa-arrow-down"></i>
                                </div>
                                <div id="txtCommandResults"></div>
                                <input type="text" class="form-input flex-grow" id="txtCommand" v-model.trim="command"
                                    @keyup.enter="sendCommand" placeholder="Digite o comando aqui..." />
                                <div class="space-x-2">
                                    <button type="button" class="btn btn-primary" id="btnSend" @click="sendCommand"
                                        title="Enviar Comando">
                                        <i class="fas fa-paper-plane"></i>
                                        <span class="hidden sm:inline"> Enviar</span>
                                    </button>
                                    <button type="button" class="btn btn-warning" id="btnClearLog" @click="clearLog"
                                        title="Limpar Log">
                                        <i class="fas fa-eraser"></i>
                                        <span class="hidden sm:inline"> Limpar</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Adiciona suporte para tema escuro e claro */
body {
    color: #333;
    background-color: #fff;
    transition: background-color 0.3s, color 0.3s;
}

@media (prefers-color-scheme: dark) {
    body {
        color: #ccc;
        background-color: #222;
    }
}
</style>
