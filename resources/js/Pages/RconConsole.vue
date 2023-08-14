<script>
import { ref, onMounted, onUnmounted } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import axios from 'axios';

export default {
    components: {
        AppLayout,
    },
    setup() {
        const command = ref('');
        const messages = ref([]);

        const connectRcon = async () => {
            try {
                await axios.post('/rcon/access-terminal', {
                    command: 'connect',
                });
            } catch (error) {
                console.error(`AxiosError: ${error.message}`);
            }
        };

        const disconnectRcon = async () => {
            try {
                await axios.post('/rcon/close-connection', {
                    command: 'disconnect',
                });
            } catch (error) {
                console.error(`AxiosError: ${error.message}`);
            }
        };

        const sendCommand = async () => {
            try {
                const response = await axios.post('/rcon/execute-command', {
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
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Console
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-5">
                    <div class="container mx-auto" id="content">
                        <div id="consoleRow">
                            <div class="bg-white rounded shadow-sm p-4 mb-4" id="consoleContent">
                                <div class="flex justify-between items-center">
                                    <h3 class="text-lg font-bold">
                                        <i class="fas fa-terminal"></i> Console
                                    </h3>
                                    <div class="space-x-2">
                                        <a class="text-sm text-gray-500 hover:text-gray-700"
                                            href="http://minecraft.gamepedia.com/Commands" target="_blank" title="Commands">
                                            <i class="fas fa-question-circle"></i>
                                            <span class="hidden sm:inline"> Commands</span>
                                        </a>
                                        <a class="text-sm text-gray-500 hover:text-gray-700"
                                            href="http://www.minecraftinfo.com/idlist.htm" target="_blank"
                                            title="Items IDs">
                                            <i class="fas fa-info-circle"></i>
                                            <span class="hidden sm:inline"> Items IDs</span>
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
                                        title="Auto Scroll" />
                                    <i class="fas fa-arrow-down"></i>
                                </div>
                                <div id="txtCommandResults"></div>
                                <input type="text" class="form-input flex-grow" id="txtCommand" v-model.trim="command"
                                    @keyup.enter="sendCommand" placeholder="Enter command here..." />
                                <div class="space-x-2">
                                    <button type="button" class="btn btn-primary" id="btnSend" @click="sendCommand"
                                        title="Send Command">
                                        <i class="fas fa-paper-plane"></i>
                                        <span class="hidden sm:inline"> Send</span>
                                    </button>
                                    <button type="button" class="btn btn-warning" id="btnClearLog" @click="clearLog"
                                        title="Clear Log">
                                        <i class="fas fa-eraser"></i>
                                        <span class="hidden sm:inline"> Clear</span>
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

<style scoped></style>

