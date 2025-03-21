<template>
    <div
        class="bg-white dark:bg-gray-800 rounded-lg p-0"
        ref="messagesContainer"
        style="height: 400px; overflow-y: scroll"
    >
        <div
            v-for="message in messages"
            :key="message.id"
            class="bg-gray-100 dark:bg-gray-700 rounded-lg mt-3 mb-3 p-4 shadow-sm"
        >
            <div v-html="convertMarkdown(message.content)" class="mb-4"></div>

            <div v-if="message.attachments.length" class="attachments mb-2">
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
                    >
                        <video
                            v-if="isVideoAttachment(attachment.url)"
                            controls
                            :src="attachment.url"
                            class="rounded-lg mt-2"
                            style="max-height: 200px"
                        ></video>
                        <img
                            v-else-if="isImageAttachment(attachment.url)"
                            :src="attachment.url"
                            :alt="`Attachment from ${message.author.username}`"
                            class="rounded-lg mt-2"
                            style="max-height: 200px"
                        />
                        <div
                            v-else-if="isTextAttachment(attachment.url)"
                            class="text-preview rounded-lg mt-2 p-2 bg-gray-200 dark:bg-gray-600"
                            style="max-height: 200px; overflow-y: auto"
                        >
                            <pre>{{ textContents[attachment.url] }}</pre>
                        </div>
                    </div>
                    <a
                        :href="attachment.url"
                        target="_blank"
                        class="text-blue-500 hover:text-blue-700"
                    >
                        <span
                            class="inline-block bg-blue-100 dark:bg-blue-900 rounded-full px-3 py-1 text-sm font-semibold text-blue-700 dark:text-blue-300 mr-2 mb-2 mt-2"
                            >Download</span
                        >
                    </a>
                </template>
            </div>
            <div class="flex justify-between items-center">
                <p class="text-gray-500 dark:text-gray-300 text-sm">
                    <img
                        :src="
                            message.author.avatar
                                ? `https://cdn.discordapp.com/avatars/${message.author.id}/${message.author.avatar}.png`
                                : 'https://cdn.discordapp.com/embed/avatars/0.png'
                        "
                        alt="avatar"
                        class="rounded-full w-6 h-6 inline-block mr-2"
                    />
                    <span>{{ message.author.username }}</span>
                </p>
                <p class="text-gray-500 dark:text-gray-300 text-sm">
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
