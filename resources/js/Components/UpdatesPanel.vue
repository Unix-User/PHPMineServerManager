<template>
    <div
        class="bg-white/90 dark:bg-gray-800/90 backdrop-blur-sm rounded-lg shadow-md transition-all duration-300 hover:shadow-lg"
        ref="messagesContainer"
        style="height: 400px; overflow-y: auto"
    >
        <div
            v-for="message in messages"
            :key="message.id"
            class="bg-gray-100/80 dark:bg-gray-700/80 backdrop-blur-sm rounded-lg mx-3 my-4 p-5 shadow-sm transition-all duration-200 hover:shadow-md"
        >
            <div v-html="convertMarkdown(message.content)" class="mb-4 prose dark:prose-invert max-w-none"></div>

            <div v-if="message.attachments.length" class="attachments mb-3 space-y-2">
                <template
                    v-for="attachment in message.attachments"
                    :key="attachment.id"
                >
                    <div
                        v-if="
                            isVideoAttachment(attachment.url) ||
                            isImageAttachment(attachment.url) ||
                            isTextAttachment(attachment.url)
                        "
                        class="attachment-preview transition-transform duration-300 hover:scale-[1.02]"
                    >
                        <video
                            v-if="isVideoAttachment(attachment.url)"
                            controls
                            :src="attachment.url"
                            class="rounded-lg mt-2 w-full max-h-48 object-contain shadow-sm"
                        ></video>
                        <img
                            v-else-if="isImageAttachment(attachment.url)"
                            :src="attachment.url"
                            :alt="`Attachment from ${message.author.username}`"
                            class="rounded-lg mt-2 w-full max-h-48 object-contain shadow-sm"
                            loading="lazy"
                        />
                        <div
                            v-else-if="isTextAttachment(attachment.url)"
                            class="text-preview rounded-lg mt-2 p-3 bg-gray-200/90 dark:bg-gray-600/90 backdrop-blur-sm shadow-sm"
                            style="max-height: 200px; overflow-y: auto"
                        >
                            <pre class="text-sm text-gray-800 dark:text-gray-200 whitespace-pre-wrap">{{ textContents[attachment.url] }}</pre>
                        </div>
                    </div>
                    <a
                        :href="attachment.url"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-block mt-2 transition-all duration-200"
                    >
                        <span
                            class="bg-blue-100/90 dark:bg-blue-900/90 backdrop-blur-sm rounded-full px-4 py-1.5 text-sm font-medium text-blue-700 dark:text-blue-300 shadow-sm transition-all duration-200 hover:shadow-md hover:bg-blue-200/90 dark:hover:bg-blue-800/90"
                            >Download Attachment</span
                        >
                    </a>
                </template>
            </div>
            <div class="flex flex-wrap justify-between items-center pt-2 border-t border-gray-200 dark:border-gray-600">
                <div class="flex items-center text-gray-600 dark:text-gray-300 text-sm">
                    <img
                        :src="
                            message.author.avatar
                                ? `https://cdn.discordapp.com/avatars/${message.author.id}/${message.author.avatar}.png`
                                : 'https://cdn.discordapp.com/embed/avatars/0.png'
                        "
                        alt="avatar"
                        class="rounded-full w-7 h-7 inline-block mr-2 shadow-sm border border-gray-200 dark:border-gray-600"
                        loading="lazy"
                    />
                    <span class="font-medium">{{ message.author.username }}</span>
                </div>
                <p class="text-gray-500 dark:text-gray-400 text-sm mt-1 sm:mt-0">
                    {{ new Date(message.timestamp).toLocaleString("pt-BR") }}
                </p>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, onMounted, nextTick, reactive } from "vue";
import axios from "axios";
import { marked } from "marked"; // Corrected import statement for marked

export default {
    setup() {
        const messages = ref([]);
        const messagesContainer = ref(null);
        const textContents = reactive({});

        const getMessages = async () => {
            try {
                const response = await axios.get("/discord/get-updates");
                if (response.status === 200) {
                    messages.value = response.data.reverse(); // Reverse the order of messages
                    await Promise.all(messages.value.map(async (message) => {
                        await Promise.all(message.attachments.map(async (attachment) => {
                            if (isTextAttachment(attachment.url)) {
                                textContents[attachment.url] = await fetchTextContent(attachment.url);
                            }
                        }));
                    }));
                }
            } catch (error) {
                console.error("Failed to fetch messages:", error);
            }
        };

        const scrollToBottom = () => {
            if (messagesContainer.value) {
                messagesContainer.value.scrollTop =
                    messagesContainer.value.scrollHeight;
            }
        };

        onMounted(async () => {
            await getMessages();
            nextTick(() => {
                scrollToBottom(); // Ensure the scroll to bottom is called after messages are fetched and DOM is updated
            });
        });

        const convertMarkdown = (markdownText) => {
            const renderer = new marked.Renderer();
            renderer.heading = (text, level) => {
                const escapedText = text.toLowerCase().replace(/[^\w]+/g, "-");
                return `<h${level} id="${escapedText}" class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight mb-4">${text}</h${level}>`;
            };
            renderer.link = (href, title, text) => {
                return `<a href="${href}" title="${title || ''}" target="_blank" rel="noopener noreferrer" class="text-blue-600 dark:text-blue-400 hover:underline">${text}</a>`;
            };
            renderer.text = (text) => {
                return text.replace(/<@(\d+)>/g, (match, id) => {
                    const user = messages.value.find(m => m.author.id === id);
                    return user ? `@${user.author.username}` : match;
                });
            };
            return marked(markdownText, { renderer });
        };

        const isTextAttachment = (url) => {
            return url.match(/\.(txt|md|html)(\?|$)/i) != null;
        };

        const isImageAttachment = (url) => {
            return url.match(/\.(jpeg|jpg|gif|png)(\?|$)/i) != null;
        };

        const isVideoAttachment = (url) => {
            return url.match(/\.(mp4|webm|ogg)(\?|$)/i) != null;
        };

        const fetchTextContent = async (url) => {
            try {
                const response = await axios.get(url, { responseType: "blob" });
                if (response.data) {
                    const text = await response.data.text();
                    return text;
                } else {
                    throw new Error("No data received");
                }
            } catch (error) {
                console.error("Failed to fetch text content:", error);
                return "Failed to load content";
            }
        };

        return {
            messages,
            convertMarkdown,
            messagesContainer,
            isTextAttachment,
            isImageAttachment,
            isVideoAttachment,
            fetchTextContent,
            textContents,
        };
    },
};
</script>
