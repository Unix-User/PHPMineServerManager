<script setup>
import { ref, onMounted, watchEffect } from "vue";
import axios from "axios";

const props = defineProps({
    fetchFileListUrl: {
        type: String,
        required: true,
    },
    fetchBusyStatusUrl: {
        type: String,
        default: null, // Opcional, pode não ter status ocupado
    },
    downloadFileUrlPrefix: {
        type: String,
        required: true,
    },
    componentTitle: {
        type: String,
        default: 'Navegador de Arquivos',
    },
    folderLabel: {
        type: String,
        default: 'Pasta',
    },
    fileLabel: {
        type: String,
        default: 'Arquivo',
    },
    loadingMessage: {
        type: String,
        default: 'Carregando arquivos...',
    },
    noFilesMessage: {
        type: String,
        default: 'Nenhum arquivo encontrado nesta pasta.',
    },
    authErrorMessage: {
        type: String,
        default: 'Erro de autenticação. Por favor, faça login novamente.',
    },
    fetchErrorMessage: {
        type: String,
        default: 'Erro ao carregar arquivos:',
    },
    downloadErrorMessage: {
        type: String,
        default: 'Erro ao baixar o arquivo:',
    },
    tryAgainButtonLabel: {
        type: String,
        default: 'Tentar novamente',
    },
    backButtonLabel: {
        type: String,
        default: '← Voltar',
    },
    descriptionText: {
        type: String,
        default: '', // Pode ser deixado vazio para não exibir descrição
    },
    isFolder: {
        type: Function,
        required: true, // Função para detectar pasta, agora é obrigatória e definida pelo usuário
    },
});

// Estado reativo
const isLoading = ref(false);
const authError = ref(null);
const fileList = ref([]);
const currentFolderId = ref('root');
const folderHistory = ref(['root']);

// Função para buscar status ocupado (opcional)
const fetchBusyStatus = async () => {
    if (!props.fetchBusyStatusUrl) return; // Se não houver URL, não busca status
    try {
        const response = await axios.get(props.fetchBusyStatusUrl);
        isLoading.value = response.data.isBusy === true;
    } catch (error) {
        console.error(`Erro ao buscar status: ${error.message}`);
        authError.value = 'Erro ao conectar ao servidor. Tente novamente mais tarde.';
    }
};

// Função para listar arquivos
const fetchFileList = async () => {
    isLoading.value = true;
    fileList.value = [];

    try {
        const response = await axios.get(props.fetchFileListUrl, {
            params: {
                folderId: currentFolderId.value
            }
        });

        fileList.value = response.data.files.sort((a, b) => {
            if (props.isFolder(a)) return -1;
            if (props.isFolder(b)) return 1;
            return 0;
        });
    } catch (error) {
        handleError(error);
    } finally {
        isLoading.value = false;
    }
};

// Tratamento de erros genérico
const handleError = (error) => {
    if (error.response?.status === 401 || error.response?.status === 403) {
        authError.value = props.authErrorMessage;
    } else if (error.response?.status === 404) {
        authError.value = 'Rota não encontrada. Contate o administrador.';
    } else {
        authError.value = `${props.fetchErrorMessage} ${error.message}`;
    }
};

// Navegação entre pastas
const navigateToFolder = (folderId) => {
    folderHistory.value.push(folderId);
    currentFolderId.value = folderId;
    fetchFileList();
};

const navigateBack = () => {
    if (folderHistory.value.length > 1) {
        folderHistory.value.pop();
        currentFolderId.value = folderHistory.value[folderHistory.value.length - 1];
        fetchFileList();
    }
};

// Download de arquivos
const downloadFile = async (file) => {
    try {
        const response = await axios.get(`${props.downloadFileUrlPrefix}/${file.id}`, {
            responseType: 'blob'
        });

        const url = window.URL.createObjectURL(response.data);
        const link = document.createElement('a');
        link.href = url;
        link.download = file.name;
        document.body.appendChild(link);
        link.click();
        window.URL.revokeObjectURL(url);
        link.remove();
    } catch (error) {
        alert(`${props.downloadErrorMessage} ${error.message}`);
    }
};

// Watchers e lifecycle hooks
watchEffect(() => {
    fetchFileList();
});

onMounted(async () => {
    if (props.fetchBusyStatusUrl) {
        await fetchBusyStatus();
    }
    fetchFileList();
});
</script>

<template>
    <div class="bg-white/90 dark:bg-gray-800/90 backdrop-blur-sm rounded-lg shadow-md p-4 border border-gray-200/50 dark:border-gray-700/50 transition-colors duration-300">
        <div class="flex justify-between items-center mb-2">
            <div class="flex items-center space-x-2">
                <button
                    v-if="folderHistory.length > 1"
                    @click="navigateBack"
                    class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
                    aria-label="Voltar para pasta anterior"
                >
                    {{ backButtonLabel }}
                </button>
                <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 transition-colors">
                    {{ componentTitle }}
                </h2>
            </div>
            <div v-if="isLoading" class="flex items-center text-blue-600 dark:text-blue-400 transition-colors">
                <svg class="animate-spin -ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Processando...
            </div>
        </div>

        <div v-if="authError" class="p-4 mb-4 bg-red-100 text-red-700 rounded-lg">
            {{ authError }} -
            <button
                @click="fetchFileList"
                class="ml-2 text-blue-600 hover:text-blue-800 underline"
            >
                {{ tryAgainButtonLabel }}
            </button>
        </div>

        <div class="bg-gray-100/90 dark:bg-gray-900/90 backdrop-blur-sm rounded-lg p-6 transition-colors duration-300 flex flex-col items-center justify-center min-h-[300px]">
            <div class="max-w-md w-full space-y-4 text-center">
                <ul v-if="fileList.length > 0" class="file-list space-y-2">
                    <li v-for="file in fileList" :key="file.id" class="file-item rounded-lg p-3 hover:bg-gray-200/50 dark:hover:bg-gray-700/50 transition-colors">
                        <button v-if="props.isFolder(file)" @click="navigateToFolder(file.id)" class="file-link folder w-full text-left">
                            <i class="fas fa-folder mr-2 text-yellow-400"></i> {{ file.name }} ({{ folderLabel }})
                        </button>
                        <a v-else @click.prevent="downloadFile(file)" class="file-link file w-full text-left block">
                            <i class="fas fa-file mr-2 text-blue-400"></i> {{ file.name }} ({{ fileLabel }})
                        </a>
                    </li>
                </ul>
                <div v-else-if="!isLoading" class="p-4 text-center text-gray-600 dark:text-gray-300">
                    <i class="fas fa-inbox text-3xl mb-2 text-gray-400"></i>
                    <p>{{ noFilesMessage }}</p>
                </div>
                <div v-if="isLoading" class="flex justify-center p-4">
                    <svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-blue-600 dark:text-blue-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    {{ loadingMessage }}
                </div>
            </div>
        </div>
        <div v-if="descriptionText" class="mt-4 text-sm text-gray-600 dark:text-gray-300 transition-colors duration-300">
            <p>{{ descriptionText }}</p>
        </div>
    </div>
</template>

<style scoped>
@media (max-width: 640px) {
    .rounded-lg {
        padding: 1rem;
        margin: 0.5rem;
    }
}

/* Smooth transitions for all elements */
* {
    transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease, opacity 0.3s ease;
}

.file-list {
    list-style-type: none;
    padding: 0;
}

.file-item {
    padding: 0.5rem 1rem;
    border-bottom: 1px solid rgba(209, 213, 219, 0.3);
    transition: background-color 0.3s ease;
}

.file-item:last-child {
    border-bottom: none;
}

.file-item:hover {
    background-color: rgba(243, 244, 246, 0.5);
}

.dark .file-item:hover {
    background-color: rgba(55, 65, 81, 0.3);
}

.file-link {
    display: flex;
    align-items: center;
    text-decoration: none;
    color: inherit;
    width: 100%; /* Garante que o botão/link ocupe toda a largura do item */
    padding: 0.5rem 0; /* Adiciona padding vertical para melhor toque */
    cursor: pointer; /* Indica que é clicável */
}

.file-link:hover {
    text-decoration: underline;
}

.folder {
    color: #facc15; /* Cor amarela/dourada para pastas */
}
.folder:hover {
    color: #eab308; /* Amarelo mais escuro no hover */
}

.file {
    color: #6b7280; /* Cor cinza para arquivos */
}
.dark .file {
    color: #d1d5db; /* Cinza claro para arquivos no modo dark */
}
</style>
