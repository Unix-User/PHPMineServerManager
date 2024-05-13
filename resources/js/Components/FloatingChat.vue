<template>
    <div :class="{'dark': isDarkMode}">
        <div v-if="isOpen" :class="chatBoxClasses">
            <div class="flex justify-end pb-2">
                <button @click="minimizeChat" class="bg-yellow-500 text-white rounded-full p-1 cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" height="16" width="16" viewBox="0 0 448 512">
                        <path fill="#fff" d="M432 256c0 17.7-14.3 32-32 32L48 288c-17.7 0-32-14.3-32-32s14.3-32 32-32l352 0c17.7 0 32 14.3 32 32z"/>
                    </svg>
                </button>
                <button @click="removeChat" class="bg-red-500 text-white rounded-full p-1 cursor-pointer ml-2">
                    <svg xmlns="http://www.w3.org/2000/svg" height="16" width="16" viewBox="0 0 384 512">
                        <path fill="#fff" d="M342.6 150.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L192 210.7 86.6 105.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L146.7 256 41.4 361.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L192 301.3 297.4 406.6c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L237.3 256 342.6 150.6z"/>
                    </svg>
                </button>
            </div>
            <div :class="messageContainerClasses" ref="chatContainer">
                <div>
                    <div v-for="message in messages" :key="message.id" :class="messageClasses">
                        <p>
                            <img :src="message.author.avatar
                            ? `https://cdn.discordapp.com/avatars/${message.author.id}/${message.author.avatar}.png`
                            : 'https://cdn.discordapp.com/embed/avatars/0.png'" alt="avatar" class="rounded-full w-8 h-8 inline-block mr-2 mb-2"/>
                            <strong>{{ message.author.username }}:</strong> <span v-html="convertMarkdownToHtml(message.content)" class="ml-2"></span>
                        </p>
                        <div v-if="message.attachments.length" class="mt-3 attachments">
                            <template v-for="attachment in message.attachments" :key="attachment.id">
                                <img v-if="isImageAttachment(attachment.url)" :src="attachment.url" class="rounded-lg mt-2 max-h-40" />
                                <video v-if="isVideoAttachment(attachment.url)" controls :src="attachment.url" class="rounded-lg mt-2 max-h-40"></video>
                                <a v-if="isOtherAttachment(attachment.url)" :href="attachment.url" target="_blank" class="text-blue-500 hover:text-blue-700">
                                    <span class="inline-block bg-blue-100 rounded-full px-3 py-1 text-sm font-semibold text-blue-700 mr-2 mb-2">Download do anexo</span>
                                </a>
                            </template>
                        </div>
                        <p class="text-gray-500 text-sm mt-3">{{ new Date(message.timestamp).toLocaleString("pt-BR") }}</p>
                    </div>
                </div>
                <div :class="inputContainerClasses">
                    <input type="text" v-model="message" placeholder="Digite sua mensagem aqui..." class="flex-grow mr-2 p-2 rounded-lg" @keyup.enter="sendMessage"/>
                    <button @click="sendMessage" class="bg-blue-500 text-white rounded-lg p-2 cursor-pointer">Enviar</button>
                </div>
            </div>
        </div>
        <div class="fixed bottom-2 right-2 rounded-full p-0 shadow-lg bg-white" v-else>
            <button @click="toggleChat" class="bg-green-500 text-white rounded-full p-2 cursor-pointer flex justify-between">
                <svg xmlns="http://www.w3.org/2000/svg" height="21" width="21" viewBox="0 0 640 512">
                    <path fill="#ffffff" d="M524.5 69.8a1.5 1.5 0 0 0 -.8-.7A485.1 485.1 0 0 0 404.1 32a1.8 1.8 0 0 0 -1.9 .9 337.5 337.5 0 0 0 -14.9 30.6 447.8 447.8 0 0 0 -134.4 0 309.5 309.5 0 0 0 -15.1-30.6 1.9 1.9 0 0 0 -1.9-.9A483.7 483.7 0 0 0 116.1 69.1a1.7 1.7 0 0 0 -.8 .7C39.1 183.7 18.2 294.7 28.4 404.4a2 2 0 0 0 .8 1.4A487.7 487.7 0 0 0 176 479.9a1.9 1.9 0 0 0 2.1-.7A348.2 348.2 0 0 0 208.1 430.4a1.9 1.9 0 0 0 -1-2.6 321.2 321.2 0 0 1 -45.9-21.9 1.9 1.9 0 0 1 -.2-3.1c3.1-2.3 6.2-4.7 9.1-7.1a1.8 1.8 0 0 1 1.9-.3c96.2 43.9 200.4 43.9 295.5 0a1.8 1.8 0 0 1 1.9 .2c2.9 2.4 6 4.9 9.1 7.2a1.9 1.9 0 0 1 -.2 3.1 301.4 301.4 0 0 1 -45.9 21.8 1.9 1.9 0 0 0 -1 2.6 391.1 391.1 0 0 0 30 48.8 1.9 1.9 0 0 0 2.1 .7A486 486 0 0 0 610.7 405.7a1.9 1.9 0 0 0 .8-1.4C623.7 277.6 590.9 167.5 524.5 69.8zM222.5 337.6c-29 0-52.8-26.6-52.8-59.2S193.1 219.1 222.5 219.1c29.7 0 53.3 26.8 52.8 59.2C275.3 311 251.9 337.6 222.5 337.6zm195.4 0c-29 0-52.8-26.6-52.8-59.2S388.4 219.1 417.9 219.1c29.7 0 53.3 26.8 52.8 59.2C470.7 311 447.5 337.6 417.9 337.6z"/>
                </svg>
                <span class="ml-1">Abrir Chat</span>
            </button>
        </div>
    </div>
</template>

<script>
import { ref, onMounted, watch, nextTick, computed } from "vue";
import { marked } from "marked";

let message = ref("");
let messages = ref([]);
let isDarkMode = ref(window.matchMedia('(prefers-color-scheme: dark)').matches);

window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', event => {
    isDarkMode.value = event.matches;
});

async function sendMessage() {
    const response = await axios.get("/discord/send-message/" + message.value);
    if (response.status === 200) {
        message.value = ""; // Clear the input after successful message send
    } else {
        alert("Erro ao enviar a mensagem.");
    }
}

async function getMessages() {
    const response = await axios.get("/discord/get-messages");
    if (response.status === 200) {
        messages.value = response.data;
        nextTick(() => {
            this.$refs.chatContainer.scrollTop = this.$refs.chatContainer.scrollHeight;
        });
    }
}

function isImageAttachment(url) {
    return /\.(jpeg|jpg|png|gif)(\?|$)/i.test(url)
}

function isVideoAttachment(url) {
    return /\.(mp4|webm|ogg)(\?|$)/i.test(url);
}

function isOtherAttachment(url) {
    return !isImageAttachment(url) && !isVideoAttachment(url);
}

function convertMarkdownToHtml(markdownText) {
    const renderer = new marked.Renderer();
    renderer.emoji = (emoji) => `<span class="emoji">${emoji}</span>`;
    return marked(markdownText, { renderer });
}

export default {
    setup() {
        const isOpen = ref(false);
        const chatContainer = ref(null);

        const toggleChat = () => {
            isOpen.value = !isOpen.value;
            if (isOpen.value) {
                getMessages();
            }
        };

        const minimizeChat = () => {
            isOpen.value = false;
        };

        const removeChat = () => {
            isOpen.value = false;
            this.$emit("closeChat", false);
        };

        const chatBoxClasses = computed(() => ({
            'fixed bottom-3 right-2 rounded-lg p-2 shadow-lg': true,
            'border border-gray-300': !isDarkMode.value,
            'border border-gray-700': isDarkMode.value,
            'bg-white': !isDarkMode.value,
            'bg-gray-800': isDarkMode.value
        }));

        const messageContainerClasses = computed(() => ({
            'w-72 h-96 rounded-lg overflow-auto': true,
            'border border-gray-300 bg-gray-200': !isDarkMode.value,
            'border border-gray-700 bg-gray-900': isDarkMode.value
        }));

        const messageClasses = computed(() => ({
            'rounded-lg m-2 p-2 shadow-sm': true,
            'bg-white border border-gray-300': !isDarkMode.value,
            'bg-gray-700 text-white border border-gray-700': isDarkMode.value
        }));

        const inputContainerClasses = computed(() => ({
            'fixed bottom-5 w-72 flex justify-between p-2 rounded-lg': true,
            'border-t border-b border-gray-300 bg-gray-200': !isDarkMode.value,
            'border-t border-b border-gray-700 bg-gray-900': isDarkMode.value
        }));

        onMounted(() => {
            setInterval(() => {
                if (isOpen.value) {
                    getMessages();
                }
            }, 5000);
        });

        return {
            isOpen,
            toggleChat,
            minimizeChat,
            removeChat,
            message,
            messages,
            sendMessage,
            getMessages,
            chatContainer,
            isImageAttachment,
            isVideoAttachment,
            isOtherAttachment,
            convertMarkdownToHtml,
            isDarkMode,
            chatBoxClasses,
            messageContainerClasses,
            messageClasses,
            inputContainerClasses
        };
    },
};
</script>
