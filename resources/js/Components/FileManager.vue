<script setup>
import { ref, onMounted, watchEffect } from "vue";
import axios from "axios";

// Constantes para modos de visualização
const VIEW_MODES = {
    LIST: 'list',
    DETAILS: 'details',
    LARGE_ICONS: 'large-icons',
    SMALL_ICONS: 'small-icons'
};

const props = defineProps({
    baseUrl: {
        type: String,
        default: './',
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
    tryAgainButtonLabel: {
        type: String,
        default: 'Tentar novamente',
    },
    backButtonLabel: {
        type: String,
        default: 'Voltar',
    },
    descriptionText: {
        type: String,
        default: '',
    },
});

// Estados reativos
const isLoading = ref(false);
const authError = ref(null);
const fileList = ref([]);
const currentPath = ref('');
const pathHistory = ref(['']);
const viewMode = ref(VIEW_MODES.LIST);
const sortBy = ref('name');
const sortAsc = ref(true);

// Função para analisar a resposta do diretório
const parseDirectoryResponse = (response) => {
    if (!response.data || !Array.isArray(response.data) || response.data.length === 0) {
        throw new Error('Resposta inválida do servidor');
    }

    const firstResult = response.data[0];
    if (!firstResult.success || !Array.isArray(firstResult.success)) {
        throw new Error('Estrutura de dados inválida');
    }

    return firstResult.success;
};

// Função para formatar o caminho
const formatPath = (path) => {
    return path.replace(/^\.?\//, '')
              .replace(/\/+/g, '/')
              .replace(/\/$/, '');
};

// Função para buscar conteúdo do diretório
const fetchDirectoryContents = async () => {
    isLoading.value = true;
    fileList.value = [];
    authError.value = null; // Limpa erros anteriores ao tentar novamente

    try {
        const url = `${props.baseUrl.replace(/\/+$/, '')}/lsdirectory/${currentPath.value || ''}`.replace(/\/+/g, '/');
        const response = await axios.get(url);
        const items = parseDirectoryResponse(response);

        let sortedItems = sortItems(items);

        // Adiciona item ".." para voltar ao diretório anterior, exceto na raiz
        if (currentPath.value) {
            sortedItems.unshift('../');
        }

        fileList.value = sortedItems;


    } catch (error) {
        handleError(error);
    } finally {
        isLoading.value = false;
    }
};

// Função para ordenar itens
const sortItems = (items) => {
    return items.sort((a, b) => {
        const isAFolder = typeof a === 'string' && a.endsWith('/');
        const isBFolder = typeof b === 'string' && b.endsWith('/');
        const isParentDirA = a === '../';
        const isParentDirB = b === '../';

        if (isParentDirA) return -1; // ".." always on top
        if (isParentDirB) return 1;

        // Ordena pastas primeiro
        if (isAFolder !== isBFolder) {
            return isAFolder ? -1 : 1;
        }

        // Ordena por nome
        return sortAsc.value ? a.localeCompare(b) : b.localeCompare(a);
    });
};


// Função para tratar erros
const handleError = (error) => {
    if (error.response?.status === 401 || error.response?.status === 403) {
        authError.value = props.authErrorMessage;
    } else if (error.response?.status === 404) {
        authError.value = 'Diretório não encontrado';
    } else {
        authError.value = `${props.fetchErrorMessage} ${error.message}`;
    }
};

// Função para navegar para pasta
const navigateToFolder = (folderPath) => {
    if (folderPath === '../') {
        navigateToParentDirectory();
        return;
    }

    const cleanPath = folderPath.replace(/^\.\//, '');
    const newPath = currentPath.value + cleanPath;

    pathHistory.value.push(newPath);
    currentPath.value = newPath;
    fetchDirectoryContents();
};

// Função para voltar para o diretório pai
const navigateToParentDirectory = () => {
    if (pathHistory.value.length > 1) {
        pathHistory.value.pop(); // Remove o caminho atual do histórico
    }
    const parts = currentPath.value.split('/').filter(part => part); // Divide o caminho e remove partes vazias
    parts.pop(); // Remove a última parte do caminho
    currentPath.value = parts.join('/'); // Junta as partes restantes para formar o novo caminho
    if (!currentPath.value && pathHistory.value.length > 0) {
        pathHistory.value = ['']; // Reseta history to root if going back from top level
    } else if (!currentPath.value) {
        pathHistory.value = [''];
    }
    fetchDirectoryContents();
};


// Função para voltar
const navigateBack = () => {
    if (pathHistory.value.length > 1) {
        pathHistory.value.pop();
        currentPath.value = pathHistory.value[pathHistory.value.length - 1];
        fetchDirectoryContents();
    }
};

// Função para mudar modo de visualização
const changeViewMode = (mode) => {
    viewMode.value = mode;
};

// Função para ordenar
const changeSort = (by) => {
    if (sortBy.value === by) {
        sortAsc.value = !sortAsc.value;
    } else {
        sortBy.value = by;
        sortAsc.value = true;
    }
    fileList.value = sortItems(fileList.value);
};

// Observadores e montagem
watchEffect(() => {
    fetchDirectoryContents();
});

onMounted(() => {
    fetchDirectoryContents();
});
</script>

<template>
    <div class="bg-white/90 dark:bg-gray-800/90 backdrop-blur-sm rounded-lg shadow-md p-6 border border-gray-200/50 dark:border-gray-700/50 transition-colors duration-300">
        <header class="flex justify-between items-center mb-6">
            <div class="flex items-center space-x-3">
                <button
                    v-if="pathHistory.length > 1"
                    @click="navigateBack"
                    class="px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-md hover:bg-gray-200 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50"
                >
                    <i class="fas fa-arrow-left mr-2"></i> {{ backButtonLabel }}
                </button>
                <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 transition-colors">
                    {{ componentTitle }}
                </h2>
            </div>

            <!-- View Mode Toggles -->
            <div class="flex space-x-2">
                <button
                    @click="changeViewMode(VIEW_MODES.LIST)"
                    :class="{'bg-blue-500 text-white': viewMode === VIEW_MODES.LIST, 'hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300': viewMode !== VIEW_MODES.LIST}"
                    class="p-2 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition-colors"
                    aria-label="Visualizar em Lista"
                >
                    <svg v-if="viewMode !== VIEW_MODES.LIST" class="h-5 w-5 text-gray-700 dark:text-gray-300" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M3 5H21M3 12H21M3 19H21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <svg v-else class="h-5 w-5 text-white" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M3 5H21M3 12H21M3 19H21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
                <button
                    @click="changeViewMode(VIEW_MODES.DETAILS)"
                    :class="{'bg-blue-500 text-white': viewMode === VIEW_MODES.DETAILS, 'hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300': viewMode !== VIEW_MODES.DETAILS}"
                    class="p-2 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition-colors"
                    aria-label="Visualizar em Detalhes"
                >
                    <svg v-if="viewMode !== VIEW_MODES.DETAILS" class="h-5 w-5 text-gray-700 dark:text-gray-300" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect x="3" y="4" width="18" height="2" fill="currentColor"/>
                        <rect x="3" y="8" width="18" height="2" fill="currentColor"/>
                        <rect x="3" y="12" width="18" height="2" fill="currentColor"/>
                        <rect x="3" y="16" width="18" height="2" fill="currentColor"/>
                        <rect x="3" y="20" width="18" height="2" fill="currentColor"/>
                    </svg>
                    <svg v-else class="h-5 w-5 text-white" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect x="3" y="4" width="18" height="2" fill="currentColor"/>
                        <rect x="3" y="8" width="18" height="2" fill="currentColor"/>
                        <rect x="3" y="12" width="18" height="2" fill="currentColor"/>
                        <rect x="3" y="16" width="18" height="2" fill="currentColor"/>
                        <rect x="3" y="20" width="18" height="2" fill="currentColor"/>
                    </svg>
                </button>
                <button
                    @click="changeViewMode(VIEW_MODES.LARGE_ICONS)"
                    :class="{'bg-blue-500 text-white': viewMode === VIEW_MODES.LARGE_ICONS, 'hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300': viewMode !== VIEW_MODES.LARGE_ICONS}"
                    class="p-2 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition-colors"
                    aria-label="Visualizar Ícones Grandes"
                >
                    <svg v-if="viewMode !== VIEW_MODES.LARGE_ICONS" class="h-5 w-5 text-gray-700 dark:text-gray-300" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect x="3" y="3" width="5" height="5" fill="currentColor"/>
                        <rect x="10" y="3" width="5" height="5" fill="currentColor"/>
                        <rect x="17" y="3" width="5" height="5" fill="currentColor"/>
                        <rect x="3" y="10" width="5" height="5" fill="currentColor"/>
                        <rect x="10" y="10" width="5" height="5" fill="currentColor"/>
                        <rect x="17" y="10" width="5" height="5" fill="currentColor"/>
                        <rect x="3" y="17" width="5" height="5" fill="currentColor"/>
                        <rect x="10" y="17" width="5" height="5" fill="currentColor"/>
                        <rect x="17" y="17" width="5" height="5" fill="currentColor"/>
                    </svg>
                    <svg v-else class="h-5 w-5 text-white" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect x="3" y="3" width="5" height="5" fill="currentColor"/>
                        <rect x="10" y="3" width="5" height="5" fill="currentColor"/>
                        <rect x="17" y="3" width="5" height="5" fill="currentColor"/>
                        <rect x="3" y="10" width="5" height="5" fill="currentColor"/>
                        <rect x="10" y="10" width="5" height="5" fill="currentColor"/>
                        <rect x="17" y="10" width="5" height="5" fill="currentColor"/>
                        <rect x="3" y="17" width="5" height="5" fill="currentColor"/>
                        <rect x="10" y="17" width="5" height="5" fill="currentColor"/>
                        <rect x="17" y="17" width="5" height="5" fill="currentColor"/>
                    </svg>
                </button>
                <button
                    @click="changeViewMode(VIEW_MODES.SMALL_ICONS)"
                    :class="{'bg-blue-500 text-white': viewMode === VIEW_MODES.SMALL_ICONS, 'hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300': viewMode !== VIEW_MODES.SMALL_ICONS}"
                    class="p-2 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition-colors"
                    aria-label="Visualizar Ícones Pequenos"
                >
                    <svg v-if="viewMode !== VIEW_MODES.SMALL_ICONS" class="h-5 w-5 text-gray-700 dark:text-gray-300" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect x="3" y="3" width="3" height="3" fill="currentColor"/>
                        <rect x="7" y="3" width="3" height="3" fill="currentColor"/>
                        <rect x="11" y="3" width="3" height="3" fill="currentColor"/>
                        <rect x="15" y="3" width="3" height="3" fill="currentColor"/>
                        <rect x="19" y="3" width="3" height="3" fill="currentColor"/>
                        <rect x="3" y="7" width="3" height="3" fill="currentColor"/>
                        <rect x="7" y="7" width="3" height="3" fill="currentColor"/>
                        <rect x="11" y="7" width="3" height="3" fill="currentColor"/>
                        <rect x="15" y="7" width="3" height="3" fill="currentColor"/>
                        <rect x="19" y="7" width="3" height="3" fill="currentColor"/>
                        <rect x="3" y="11" width="3" height="3" fill="currentColor"/>
                        <rect x="7" y="11" width="3" height="3" fill="currentColor"/>
                        <rect x="11" y="11" width="3" height="3" fill="currentColor"/>
                        <rect x="15" y="11" width="3" height="3" fill="currentColor"/>
                        <rect x="19" y="11" width="3" height="3" fill="currentColor"/>
                        <rect x="3" y="15" width="3" height="3" fill="currentColor"/>
                        <rect x="7" y="15" width="3" height="3" fill="currentColor"/>
                        <rect x="11" y="15" width="3" height="3" fill="currentColor"/>
                        <rect x="15" y="15" width="3" height="3" fill="currentColor"/>
                        <rect x="19" y="15" width="3" height="3" fill="currentColor"/>
                        <rect x="3" y="19" width="3" height="3" fill="currentColor"/>
                        <rect x="7" y="19" width="3" height="3" fill="currentColor"/>
                        <rect x="11" y="19" width="3" height="3" fill="currentColor"/>
                        <rect x="15" y="19" width="3" height="3" fill="currentColor"/>
                        <rect x="19" y="19" width="3" height="3" fill="currentColor"/>
                    </svg>
                    <svg v-else class="h-5 w-5 text-white" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect x="3" y="3" width="3" height="3" fill="currentColor"/>
                        <rect x="7" y="3" width="3" height="3" fill="currentColor"/>
                        <rect x="11" y="3" width="3" height="3" fill="currentColor"/>
                        <rect x="15" y="3" width="3" height="3" fill="currentColor"/>
                        <rect x="19" y="3" width="3" height="3" fill="currentColor"/>
                        <rect x="3" y="7" width="3" height="3" fill="currentColor"/>
                        <rect x="7" y="7" width="3" height="3" fill="currentColor"/>
                        <rect x="11" y="7" width="3" height="3" fill="currentColor"/>
                        <rect x="15" y="7" width="3" height="3" fill="currentColor"/>
                        <rect x="19" y="7" width="3" height="3" fill="currentColor"/>
                        <rect x="3" y="11" width="3" height="3" fill="currentColor"/>
                        <rect x="7" y="11" width="3" height="3" fill="currentColor"/>
                        <rect x="11" y="11" width="3" height="3" fill="currentColor"/>
                        <rect x="15" y="11" width="3" height="3" fill="currentColor"/>
                        <rect x="19" y="11" width="3" height="3" fill="currentColor"/>
                        <rect x="3" y="15" width="3" height="3" fill="currentColor"/>
                        <rect x="7" y="15" width="3" height="3" fill="currentColor"/>
                        <rect x="11" y="15" width="3" height="3" fill="currentColor"/>
                        <rect x="15" y="15" width="3" height="3" fill="currentColor"/>
                        <rect x="19" y="15" width="3" height="3" fill="currentColor"/>
                        <rect x="3" y="19" width="3" height="3" fill="currentColor"/>
                        <rect x="7" y="19" width="3" height="3" fill="currentColor"/>
                        <rect x="11" y="19" width="3" height="3" fill="currentColor"/>
                        <rect x="15" y="19" width="3" height="3" fill="currentColor"/>
                        <rect x="19" y="19" width="3" height="3" fill="currentColor"/>
                    </svg>
                </button>
            </div>
        </header>

        <div class="mb-4 text-sm text-gray-600 dark:text-gray-300">
            Caminho atual: <span class="font-medium">{{ currentPath || 'Raiz' }}</span>
        </div>

        <div v-if="authError" class="p-4 mb-4 bg-red-100 text-red-700 rounded-md">
            <i class="fas fa-exclamation-triangle mr-2"></i> {{ authError }} -
            <button
                @click="fetchDirectoryContents"
                class="ml-2 text-blue-600 hover:text-blue-800 underline"
            >
                {{ tryAgainButtonLabel }}
            </button>
        </div>

        <div class="bg-gray-50/90 dark:bg-gray-900/90 backdrop-blur-sm rounded-md p-4 transition-colors duration-300 min-h-[200px] flex flex-col">
            <div v-if="isLoading" class="flex justify-center items-center p-6 flex-col space-y-3">
                <svg class="animate-spin h-8 w-8 text-blue-600 dark:text-blue-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <p class="text-gray-700 dark:text-gray-300">{{ loadingMessage }}</p>
            </div>
            <ul v-else-if="fileList.length > 0" class="file-list" :class="{'grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4': viewMode === VIEW_MODES.LARGE_ICONS, 'grid grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-2': viewMode === VIEW_MODES.SMALL_ICONS, 'space-y-2': viewMode === VIEW_MODES.LIST || viewMode === VIEW_MODES.DETAILS}">
                <li v-for="item in fileList" :key="item" class="file-item" :class="{'view-mode-list': viewMode === VIEW_MODES.LIST, 'view-mode-details': viewMode === VIEW_MODES.DETAILS, 'view-mode-large-icons': viewMode === VIEW_MODES.LARGE_ICONS, 'view-mode-small-icons': viewMode === VIEW_MODES.SMALL_ICONS}">
                    <button v-if="item === '../'" @click="navigateToFolder(item)" class="file-link parent-folder">
                        <span v-if="viewMode === VIEW_MODES.LARGE_ICONS || viewMode === VIEW_MODES.SMALL_ICONS" class="icon">
                            <svg class="h-6 w-6 text-gray-500 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 16l-4-4m0 0l4-4m-4 4h18" />
                            </svg>
                        </span>
                        <span v-else class="icon">
                            <svg class="h-5 w-5 mr-2 text-gray-500 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 16l-4-4m0 0l4-4m-4 4h18" />
                            </svg>
                        </span>
                        <span class="label">..</span>
                        <span v-if="viewMode === VIEW_MODES.DETAILS" class="details-label">({{ folderLabel }})</span>
                    </button>
                    <button v-else-if="typeof item === 'string' && item.endsWith('/')" @click="navigateToFolder(item)" class="file-link folder">
                        <span v-if="viewMode === VIEW_MODES.LARGE_ICONS || viewMode === VIEW_MODES.SMALL_ICONS" class="icon">
                            <svg class="h-6 w-6 text-yellow-400" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M2 7.5C2 5.57179 3.57179 4 5.5 4H8.5C9.05228 4 9.58418 4.21071 9.92893 4.58579L11.5 6.15685L13.0711 7.72792C13.4158 8.10299 13.9477 8.3137 14.5 8.3137H19C20.6569 8.3137 22 9.65679 22 11.3137V16.6863C22 18.3432 20.6569 19.6863 19 19.6863H5C3.34315 19.6863 2 18.3432 2 16.6863V7.5Z" fill="currentColor"/>
                            </svg>
                        </span>
                        <span v-else class="icon">
                            <svg class="h-5 w-5 mr-2 text-yellow-400" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M2 7.5C2 5.57179 3.57179 4 5.5 4H8.5C9.05228 4 9.58418 4.21071 9.92893 4.58579L11.5 6.15685L13.0711 7.72792C13.4158 8.10299 13.9477 8.3137 14.5 8.3137H19C20.6569 8.3137 22 9.65679 22 11.3137V16.6863C22 18.3432 20.6569 19.6863 19 19.6863H5C3.34315 19.6863 2 18.3432 2 16.6863V7.5Z" fill="currentColor"/>
                            </svg>
                        </span>
                        <span class="label">{{ formatPath(item) }}</span>
                        <span v-if="viewMode === VIEW_MODES.DETAILS" class="details-label">({{ folderLabel }})</span>
                    </button>
                    <div v-else-if="typeof item === 'string'" class="file-link file">
                        <span v-if="viewMode === VIEW_MODES.LARGE_ICONS || viewMode === VIEW_MODES.SMALL_ICONS" class="icon">
                            <svg class="h-6 w-6 text-blue-400" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M6 2H14L22 10V22H6V2Z" fill="#4299E1"/>
                                <path d="M14 2H22V10H14V2Z" fill="#63B3ED"/>
                                <path d="M14 2L22 10" stroke="#E0F7FA" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                        <span v-else class="icon">
                            <svg class="h-5 w-5 mr-2 text-blue-400" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M6 2H14L22 10V22H6V2Z" fill="#4299E1"/>
                                <path d="M14 2H22V10H14V2Z" fill="#63B3ED"/>
                                <path d="M14 2L22 10" stroke="#E0F7FA" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                        <span class="label">{{ formatPath(item) }}</span>
                        <span v-if="viewMode === VIEW_MODES.DETAILS" class="details-label">({{ fileLabel }})</span>
                    </div>
                </li>
            </ul>
            <div v-else-if="!isLoading" class="p-6 text-center text-gray-600 dark:text-gray-300 flex flex-col items-center space-y-3">
                <i class="fas fa-folder-open text-4xl mb-2 text-gray-400"></i>
                <p>{{ noFilesMessage }}</p>
            </div>
        </div>

        <footer v-if="descriptionText" class="mt-6 text-sm text-gray-600 dark:text-gray-300 transition-colors duration-300 border-t pt-4 border-gray-200 dark:border-gray-700">
            <p>{{ descriptionText }}</p>
        </footer>
    </div>
</template>

<style scoped>
.file-list {
    list-style-type: none;
    padding: 0;
}

.file-item {
    @apply rounded-md transition-colors duration-300;
}

/* List View */
.file-list.space-y-2 .file-item {
    @apply p-3 hover:bg-gray-100 dark:hover:bg-gray-700;
}
.file-list.space-y-2 .file-link {
    @apply flex items-center text-left w-full;
}
.file-list.space-y-2 .icon {
    @apply mr-2;
}

/* Details View */
.file-list.space-y-2 .file-item.view-mode-details {
    @apply p-3 hover:bg-gray-100 dark:hover:bg-gray-700 flex justify-between items-center;
}
.file-list.space-y-2 .file-link.file {
    @apply w-full text-left flex items-center justify-between;
}
.file-list.space-y-2 .details-label {
    @apply text-sm text-gray-500 dark:text-gray-400 ml-4;
}


/* Large Icons View */
.file-list.grid-cols-2 .file-item.view-mode-large-icons,
.file-list.grid-cols-3 .file-item.view-mode-large-icons,
.file-list.grid-cols-4 .file-item.view-mode-large-icons {
    @apply p-4 flex flex-col items-center justify-center hover:bg-gray-100 dark:hover:bg-gray-700 text-center;
}
.file-list.grid-cols-2 .file-link.folder,
.file-list.grid-cols-3 .file-link.folder,
.file-list.grid-cols-4 .file-link.folder,
.file-list.grid-cols-2 .file-link.file,
.file-list.grid-cols-3 .file-link.file,
.file-list.grid-cols-4 .file-link.file {
    @apply flex flex-col items-center justify-center text-center w-full;
}
.file-list.grid-cols-2 .icon,
.file-list.grid-cols-3 .icon,
.file-list.grid-cols-4 .icon {
    @apply mb-2;
}
.file-list.grid-cols-2 .label,
.file-list.grid-cols-3 .label,
.file-list.grid-cols-4 .label {
    @apply block truncate w-full;
}


/* Small Icons View */
.file-list.grid-cols-3 .file-item.view-mode-small-icons,
.file-list.grid-cols-4 .file-item.view-mode-small-icons,
.file-list.grid-cols-5 .file-item.view-mode-small-icons {
    @apply p-2 flex flex-col items-center justify-center hover:bg-gray-100 dark:hover:bg-gray-700 text-center;
}
.file-list.grid-cols-3 .file-link.folder,
.file-list.grid-cols-4 .file-link.folder,
.file-list.grid-cols-5 .file-link.folder,
.file-list.grid-cols-3 .file-link.file,
.file-list.grid-cols-4 .file-link.file,
.file-list.grid-cols-5 .file-link.file {
    @apply flex flex-col items-center justify-center text-center w-full;
}
.file-list.grid-cols-3 .icon,
.file-list.grid-cols-4 .icon,
.file-list.grid-cols-5 .icon {
    @apply mb-1;
}
.file-list.grid-cols-3 .label,
.file-list.grid-cols-4 .label,
.file-list.grid-cols-5 .label {
    @apply block truncate w-full text-sm;
}


.file-link {
    text-decoration: none;
    color: inherit;
    cursor: pointer;
}

.file-link:hover {
    text-decoration: underline;
}

.folder {
    color: #ca8a04; /* Cor amarela/dourada para pastas - Tailwind amber-600 */
}
.folder:hover {
    color: #9a3412; /* Amarelo mais escuro no hover - Tailwind orange-800 */
}

.file {
    color: #4b5563; /* Cor cinza para arquivos - Tailwind gray-600 */
}
.dark .file {
    color: #d1d5db; /* Cinza claro para arquivos no modo dark - Tailwind gray-300 */
}

.parent-folder {
    color: #6b7280; /* Tailwind gray-500 */
}
.dark .parent-folder {
    color: #9ca3af; /* Tailwind gray-400 */
}
</style>
