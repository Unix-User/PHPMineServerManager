<template>
    <AppLayout title="Console">
        <div class="container mx-auto" id="content">
            <div class="bg-blue-200 text-blue-700 p-2 mb-4" id="alertMessage">
                Minecraft RCON
            </div>
            <div id="consoleRow">
                <div class="bg-white rounded shadow-sm p-4 mb-4" id="consoleContent">
                    <div class="flex justify-between items-center">
                        <h3 class="text-lg font-bold">
                            <i class="fas fa-terminal"></i> Console
                        </h3>
                        <div class="space-x-2">
                            <a
                                class="text-sm text-gray-500 hover:text-gray-700"
                                href="http://minecraft.gamepedia.com/Commands"
                                target="_blank"
                                title="Commands"
                            >
                                <i class="fas fa-question-circle"></i>
                                <span class="hidden sm:inline"> Commands</span>
                            </a>
                            <a
                                class="text-sm text-gray-500 hover:text-gray-700"
                                href="http://www.minecraftinfo.com/idlist.htm"
                                target="_blank"
                                title="Items IDs"
                            >
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
                        <input
                            id="chkAutoScroll"
                            type="checkbox"
                            checked="true"
                            autocomplete="off"
                            title="Auto Scroll"
                        />
                        <i class="fas fa-arrow-down"></i>
                    </div>
                    <div id="txtCommandResults"></div>
                    <input
                        type="text"
                        class="form-input flex-grow"
                        id="txtCommand"
                        v-model="command"
                        @keyup.enter="sendCommand"
                        placeholder="Enter command here..."
                    />
                    <div class="space-x-2">
                        <button
                            type="button"
                            class="btn btn-primary"
                            id="btnSend"
                            @click="sendCommand"
                            title="Send Command"
                        >
                            <i class="fas fa-paper-plane"></i>
                            <span class="hidden sm:inline"> Send</span>
                        </button>
                        <button
                            type="button"
                            class="btn btn-warning"
                            id="btnClearLog"
                            @click="clearLog"
                            title="Clear Log"
                        >
                            <i class="fas fa-eraser"></i>
                            <span class="hidden sm:inline"> Clear</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script>
import axios from 'axios';
import AppLayout from '@/Layouts/AppLayout.vue';

export default {
    components: {
        AppLayout,
    },
    data() {
        return {
            command: '',
            messages: [], // Array to store server responses
        };
    },
    methods: {
        async sendCommand() {
            try {
                const response = await axios.post('/api/execute-command', {
                    host: '127.0.0.1',
                    port: 25575,
                    password: 'Dracar2s',
                    timeout: '10',
                    command: this.command,
                });

                if (response.data) {
                    const message = response.data.response || 'No response from the server';
                    this.messages.push(message); // Add the message to the messages array
                    console.log('Server response:', message); // Log the message in the console
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
        },
        clearLog() {
            this.command = '';
            this.messages = []; // Clear the messages array when the "Clear" button is clicked
        },
    },
};
</script>

<style scoped></style>

