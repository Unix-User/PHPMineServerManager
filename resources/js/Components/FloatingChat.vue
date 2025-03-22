<template>
    <div>
        <!-- Chat Box when open -->
        <div v-if="isOpen" class="fixed bottom-4 right-4 w-80 sm:w-96 rounded-xl shadow-2xl z-50 transition-all duration-300 transform bg-white/95 dark:bg-gray-800/95 backdrop-blur-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <!-- Header -->
            <div class="flex items-center justify-between p-3 bg-gradient-to-r from-blue-500/90 to-indigo-600/90 text-white">
                <div class="flex items-center space-x-2">
                    <svg xmlns="http://www.w3.org/2000/svg" height="20" width="20" viewBox="0 0 640 512" class="mr-1">
                        <path fill="currentColor" d="M524.5 69.8a1.5 1.5 0 0 0 -.8-.7A485.1 485.1 0 0 0 404.1 32a1.8 1.8 0 0 0 -1.9 .9 337.5 337.5 0 0 0 -14.9 30.6 447.8 447.8 0 0 0 -134.4 0 309.5 309.5 0 0 0 -15.1-30.6 1.9 1.9 0 0 0 -1.9-.9A483.7 483.7 0 0 0 116.1 69.1a1.7 1.7 0 0 0 -.8 .7C39.1 183.7 18.2 294.7 28.4 404.4a2 2 0 0 0 .8 1.4A487.7 487.7 0 0 0 176 479.9a1.9 1.9 0 0 0 2.1-.7A348.2 348.2 0 0 0 208.1 430.4a1.9 1.9 0 0 0 -1-2.6 321.2 321.2 0 0 1 -45.9-21.9 1.9 1.9 0 0 1 -.2-3.1c3.1-2.3 6.2-4.7 9.1-7.1a1.8 1.8 0 0 1 1.9-.3c96.2 43.9 200.4 43.9 295.5 0a1.8 1.8 0 0 1 1.9 .2c2.9 2.4 6 4.9 9.1 7.2a1.9 1.9 0 0 1 -.2 3.1 301.4 301.4 0 0 1 -45.9 21.8 1.9 1.9 0 0 0 -1 2.6 391.1 391.1 0 0 0 30 48.8 1.9 1.9 0 0 0 2.1 .7A486 486 0 0 0 610.7 405.7a1.9 1.9 0 0 0 .8-1.4C623.7 277.6 590.9 167.5 524.5 69.8zM222.5 337.6c-29 0-52.8-26.6-52.8-59.2S193.1 219.1 222.5 219.1c29.7 0 53.3 26.8 52.8 59.2C275.3 311 251.9 337.6 222.5 337.6zm195.4 0c-29 0-52.8-26.6-52.8-59.2S388.4 219.1 417.9 219.1c29.7 0 53.3 26.8 52.8 59.2C470.7 311 447.5 337.6 417.9 337.6z"/>
                    </svg>
                    <h3 class="font-medium">Discord Chat</h3>
                </div>
                <div class="flex space-x-1">
                    <button @click="minimizeChat" class="p-1.5 rounded-full hover:bg-white/20 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-white/50" title="Minimizar">
                        <svg xmlns="http://www.w3.org/2000/svg" height="14" width="14" viewBox="0 0 448 512">
                            <path fill="currentColor" d="M432 256c0 17.7-14.3 32-32 32L48 288c-17.7 0-32-14.3-32-32s14.3-32 32-32l352 0c17.7 0 32 14.3 32 32z"/>
                        </svg>
                    </button>
                    <button @click="removeChat" class="p-1.5 rounded-full hover:bg-white/20 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-white/50" title="Fechar">
                        <svg xmlns="http://www.w3.org/2000/svg" height="14" width="14" viewBox="0 0 384 512">
                            <path fill="currentColor" d="M342.6 150.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L192 210.7 86.6 105.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L146.7 256 41.4 361.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L192 301.3 297.4 406.6c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L237.3 256 342.6 150.6z"/>
                        </svg>
                    </button>
                </div>
            </div>
            
            <!-- Messages Container -->
            <div class="h-80 overflow-y-auto p-2 bg-gray-50/80 dark:bg-gray-900/80 backdrop-blur-sm" ref="chatContainer">
                <div v-if="messages.length === 0" class="flex flex-col items-center justify-center h-full">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-400 dark:text-gray-500 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Nenhuma mensagem disponível</p>
                </div>
                
                <div v-else class="space-y-3">
                    <div v-for="message in messages" :key="message.id" class="message-bubble">
                        <div class="flex items-start space-x-2">
                            <img 
                                :src="message.author.avatar ? `https://cdn.discordapp.com/avatars/${message.author.id}/${message.author.avatar}.png` : 'https://cdn.discordapp.com/embed/avatars/0.png'" 
                                alt="avatar" 
                                class="rounded-full w-8 h-8 object-cover border-2 border-gray-200 dark:border-gray-700"
                            />
                            <div class="flex-1 bg-white dark:bg-gray-800 rounded-lg p-3 shadow-sm hover:shadow transition-shadow">
                                <div class="flex items-center justify-between mb-1">
                                    <p class="font-medium text-sm text-gray-900 dark:text-gray-100">{{ message.author.username }}</p>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">{{ formatTimestamp(message.timestamp) }}</span>
                                </div>
                                
                                <div v-html="convertMarkdownToHtml(message.content)" class="text-sm break-words text-gray-700 dark:text-gray-300"></div>
                                
                                <!-- Attachments -->
                                <div v-if="message.attachments.length" class="mt-2 space-y-2">
                                    <template v-for="attachment in message.attachments" :key="attachment.id">
                                        <!-- Images -->
                                        <div v-if="isImageAttachment(attachment.url)" class="relative group">
                                            <img 
                                                :src="attachment.url" 
                                                class="rounded-lg max-h-40 w-auto shadow-sm cursor-pointer transition-all duration-200 group-hover:brightness-90" 
                                                @click="openAttachment(attachment.url)" 
                                            />
                                            <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                                <span class="bg-black/50 text-white text-xs px-2 py-1 rounded-md backdrop-blur-sm">Ampliar</span>
                                            </div>
                                        </div>
                                        
                                        <!-- Videos -->
                                        <video v-if="isVideoAttachment(attachment.url)" controls :src="attachment.url" class="rounded-lg max-h-40 w-full shadow-sm"></video>
                                        
                                        <!-- Other files -->
                                        <a v-if="isOtherAttachment(attachment.url)" :href="attachment.url" target="_blank" class="inline-flex items-center space-x-2 bg-blue-50 dark:bg-blue-900/50 rounded-lg px-3 py-2 text-xs font-medium text-blue-700 dark:text-blue-300 hover:bg-blue-100 dark:hover:bg-blue-800/50 transition-colors">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                            </svg>
                                            <span>Download do anexo</span>
                                        </a>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Input Area -->
            <div class="p-3 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700">
                <div class="flex items-center space-x-2">
                    <input 
                        type="text" 
                        v-model="message" 
                        placeholder="Digite sua mensagem..." 
                        class="flex-grow p-2 rounded-lg bg-gray-100/90 dark:bg-gray-700/90 border border-gray-300 dark:border-gray-600 text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 transition-all" 
                        @keyup.enter="sendMessage"
                    />
                    <button 
                        @click="sendMessage" 
                        class="bg-blue-500 hover:bg-blue-600 text-white rounded-lg p-2 cursor-pointer transition-colors duration-200 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 disabled:opacity-50 disabled:cursor-not-allowed"
                        :disabled="!message.trim()"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Chat Button when closed -->
        <button 
            v-else
            @click="toggleChat" 
            class="fixed bottom-4 right-4 z-50 bg-gradient-to-r from-blue-500 to-indigo-600 text-white rounded-full p-3 cursor-pointer flex items-center transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50"
        >
            <svg xmlns="http://www.w3.org/2000/svg" height="20" width="20" viewBox="0 0 640 512" class="mr-1">
                <path fill="currentColor" d="M524.5 69.8a1.5 1.5 0 0 0 -.8-.7A485.1 485.1 0 0 0 404.1 32a1.8 1.8 0 0 0 -1.9 .9 337.5 337.5 0 0 0 -14.9 30.6 447.8 447.8 0 0 0 -134.4 0 309.5 309.5 0 0 0 -15.1-30.6 1.9 1.9 0 0 0 -1.9-.9A483.7 483.7 0 0 0 116.1 69.1a1.7 1.7 0 0 0 -.8 .7C39.1 183.7 18.2 294.7 28.4 404.4a2 2 0 0 0 .8 1.4A487.7 487.7 0 0 0 176 479.9a1.9 1.9 0 0 0 2.1-.7A348.2 348.2 0 0 0 208.1 430.4a1.9 1.9 0 0 0 -1-2.6 321.2 321.2 0 0 1 -45.9-21.9 1.9 1.9 0 0 1 -.2-3.1c3.1-2.3 6.2-4.7 9.1-7.1a1.8 1.8 0 0 1 1.9-.3c96.2 43.9 200.4 43.9 295.5 0a1.8 1.8 0 0 1 1.9 .2c2.9 2.4 6 4.9 9.1 7.2a1.9 1.9 0 0 1 -.2 3.1 301.4 301.4 0 0 1 -45.9 21.8 1.9 1.9 0 0 0 -1 2.6 391.1 391.1 0 0 0 30 48.8 1.9 1.9 0 0 0 2.1 .7A486 486 0 0 0 610.7 405.7a1.9 1.9 0 0 0 .8-1.4C623.7 277.6 590.9 167.5 524.5 69.8zM222.5 337.6c-29 0-52.8-26.6-52.8-59.2S193.1 219.1 222.5 219.1c29.7 0 53.3 26.8 52.8 59.2C275.3 311 251.9 337.6 222.5 337.6zm195.4 0c-29 0-52.8-26.6-52.8-59.2S388.4 219.1 417.9 219.1c29.7 0 53.3 26.8 52.8 59.2C470.7 311 447.5 337.6 417.9 337.6z"/>
            </svg>
            <span class="font-medium ml-1">Chat</span>
        </button>
    </div>
</template>

<script setup>
import { ref, onMounted, watch, nextTick, computed, onUnmounted } from "vue";
import { marked } from "marked";

const message = ref("");
const messages = ref([]);
const isOpen = ref(false);
const chatContainer = ref(null);
const refreshInterval = ref(null);

// Detect dark mode from parent components or system preference
const isDarkMode = computed(() => {
    return document.documentElement.classList.contains('dark') || 
           document.querySelector('[class*="dark"]') !== null;
});

function toggleChat() {
    isOpen.value = !isOpen.value;
    if (isOpen.value) {
        getMessages();
        nextTick(() => {
            scrollToBottom();
        });
    }
}

function minimizeChat() {
    isOpen.value = false;
}

function removeChat() {
    isOpen.value = false;
}

async function sendMessage() {
    if (!message.value.trim()) return;
    
    try {
        const response = await axios.get("/discord/send-message/" + message.value);
        if (response.status === 200) {
            message.value = ""; // Clear the input after successful message send
            await getMessages(); // Refresh messages
            nextTick(() => {
                scrollToBottom();
            });
        }
    } catch (error) {
        console.error("Erro ao enviar a mensagem:", error);
    }
}

async function getMessages() {
    try {
        const response = await axios.get("/discord/get-messages");
        if (response.status === 200) {
            messages.value = response.data;
            nextTick(() => {
                scrollToBottom();
            });
        }
    } catch (error) {
        console.error("Erro ao buscar mensagens:", error);
    }
}

function scrollToBottom() {
    if (chatContainer.value) {
        chatContainer.value.scrollTop = chatContainer.value.scrollHeight;
    }
}

function isImageAttachment(url) {
    return /\.(jpeg|jpg|png|gif|webp)(\?|$)/i.test(url);
}

function isVideoAttachment(url) {
    return /\.(mp4|webm|ogg)(\?|$)/i.test(url);
}

function isOtherAttachment(url) {
    return !isImageAttachment(url) && !isVideoAttachment(url);
}

function openAttachment(url) {
    window.open(url, '_blank');
}

function formatTimestamp(timestamp) {
    const date = new Date(timestamp);
    const now = new Date();
    
    // Se for hoje, mostra apenas a hora
    if (date.toDateString() === now.toDateString()) {
        return `Hoje às ${date.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' })}`;
    }
    
    // Se for ontem
    const yesterday = new Date(now);
    yesterday.setDate(now.getDate() - 1);
    if (date.toDateString() === yesterday.toDateString()) {
        return `Ontem às ${date.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' })}`;
    }
    
    // Caso contrário, mostra a data completa
    return date.toLocaleString('pt-BR', { 
        day: '2-digit', 
        month: '2-digit', 
        year: 'numeric',
        hour: '2-digit', 
        minute: '2-digit'
    });
}

function convertMarkdownToHtml(markdownText) {
    if (!markdownText) return '';
    
    const renderer = new marked.Renderer();
    renderer.link = (href, title, text) => {
        return `<a href="${href}" target="_blank" class="text-blue-500 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 underline">${text}</a>`;
    };
    
    // Customize code blocks for better appearance
    renderer.code = (code, language) => {
        return `<pre class="bg-gray-100 dark:bg-gray-900 p-2 rounded-md overflow-x-auto"><code class="text-sm text-gray-800 dark:text-gray-200">${code}</code></pre>`;
    };
    
    // Customize blockquotes
    renderer.blockquote = (quote) => {
        return `<blockquote class="border-l-4 border-gray-300 dark:border-gray-600 pl-3 italic text-gray-600 dark:text-gray-400">${quote}</blockquote>`;
    };
    
    return marked(markdownText, { 
        renderer,
        breaks: true,
        gfm: true
    });
}

onMounted(() => {
    // Set up auto-refresh for messages
    refreshInterval.value = setInterval(() => {
        if (isOpen.value) {
            getMessages();
        }
    }, 5000);
});

onUnmounted(() => {
    // Clean up interval when component is destroyed
    if (refreshInterval.value) {
        clearInterval(refreshInterval.value);
    }
});

// Watch for theme changes
watch(isDarkMode, (newValue) => {
    // You could add additional theme-specific logic here if needed
    console.log("Theme changed:", newValue ? "dark" : "light");
});
</script>

<style scoped>
.message-bubble {
    animation: fadeIn 0.3s ease-in-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.theme-transition {
    transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease;
}
</style>
