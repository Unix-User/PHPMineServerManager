<script setup>
import { ref, onMounted, watch } from "vue";

// Reusable constants
const MIME_TYPES = {
    FOLDER: 'application/vnd.google-apps.folder'
};

// Reactive state
const isLoading = ref(false);
const authError = ref(null);
const fileList = ref([]);
const currentFolderId = ref('root');
const currentFolderName = ref('My Drive');
const folderHistory = ref([{id: 'root', name: 'My Drive'}]);
const downloadError = ref(null);

// Check if gapi is loaded
const isGapiLoaded = ref(false);

const clientId = import.meta.env.VITE_GOOGLE_CLIENT_ID;
let tokenClient;

// Verifica se o gapi está disponível
const isGapiAvailable = () => {
    return typeof gapi !== 'undefined' && gapi.client && typeof google !== 'undefined';
};

// Initialize gapi with CSP handling
const initGapi = () => {
    return new Promise((resolve, reject) => {
        if (window.google?.accounts?.oauth2) {
            resolve();
            return;
        }

        const script = document.createElement('script');
        script.src = 'https://accounts.google.com/gsi/client';
        script.onload = async () => {
            try {
                // Load gapi client separately with retry logic
                if (!isGapiAvailable()) {
                    await new Promise((res, rej) => {
                        const gapiScript = document.createElement('script');
                        gapiScript.src = 'https://apis.google.com/js/api.js';
                        gapiScript.onload = () => {
                            if (typeof gapi === 'undefined') {
                                rej(new Error('gapi is not defined'));
                                return;
                            }
                            
                            // Add retry mechanism for gapi.client initialization
                            const initClient = async (retries = 3, delay = 1000) => {
                                try {
                                    await gapi.load('client');
                                    await gapi.client.init({
                                        apiKey: import.meta.env.VITE_GOOGLE_API_KEY,
                                        discoveryDocs: ['https://www.googleapis.com/discovery/v1/apis/drive/v3/rest']
                                    });
                                    res();
                                } catch (error) {
                                    if (retries > 0) {
                                        setTimeout(() => initClient(retries - 1, delay), delay);
                                    } else {
                                        rej(error);
                                    }
                                }
                            };
                            
                            initClient();
                        };
                        gapiScript.onerror = rej;
                        document.head.appendChild(gapiScript);
                    });
                }

                if (!window.google?.accounts?.oauth2) {
                    throw new Error('Google Identity Services not loaded');
                }

                isGapiLoaded.value = true;
                resolve();
            } catch (error) {
                console.error('Error initializing GIS:', error);
                authError.value = 'Failed to initialize Google API. Please refresh the page.';
                reject(new Error('Failed to initialize Google Identity Services'));
            }
        };
        script.onerror = (error) => {
            console.error('Error loading GIS script:', error);
            authError.value = 'Failed to load Google API. Please refresh the page.';
            reject(new Error('Failed to load Google Identity Services script'));
        };
        document.head.appendChild(script);
    });
};

// Enhanced error handling for file listing
const fetchFileList = async () => {
    if (!isGapiAvailable()) {
        authError.value = 'Google API não está disponível. Tente recarregar a página.';
        return;
    }

    isLoading.value = true;
    authError.value = null;

    try {
        if (!gapi.client.getToken()) {
            tokenClient.requestAccessToken();
            return;
        }

        if (currentFolderId.value !== 'root') {
            try {
                const folderInfo = await gapi.client.drive.files.get({
                    fileId: currentFolderId.value,
                    fields: 'name'
                });
                currentFolderName.value = folderInfo.result.name;
            } catch (error) {
                handleDriveError(error);
                throw error;
            }
        } else {
            currentFolderName.value = 'My Drive';
        }

        const response = await gapi.client.drive.files.list({
            q: `'${currentFolderId.value}' in parents and trashed = false`,
            fields: 'files(id, name, mimeType)',
            orderBy: 'name',
            timeout: 10000
        });

        if (!response?.result?.files) {
            throw new Error('Invalid response from Google Drive API');
        }

        fileList.value = response.result.files.sort((a, b) => {
            const isAFolder = a.mimeType === MIME_TYPES.FOLDER;
            const isBFolder = b.mimeType === MIME_TYPES.FOLDER;
            return isAFolder === isBFolder ? 0 : isAFolder ? -1 : 1;
        });
    } catch (error) {
        handleDriveError(error);
    } finally {
        isLoading.value = false;
    }
};

// Enhanced error handling
const handleDriveError = (error) => {
    console.error('Google Drive error:', error);

    if (error.status === 401 || error.status === 403) {
        authError.value = 'Erro de autenticação. Por favor, faça login novamente.';
    } else if (error.status === 404) {
        authError.value = 'Recurso não encontrado. Verifique as permissões e tente novamente.';
    } else if (error.message.includes('Cannot read properties of undefined') || error.message.includes('Google API não está disponível')) {
        authError.value = 'Erro ao carregar a API do Google Drive. Tente recarregar a página.';
    } else {
        authError.value = 'Erro ao carregar arquivos: ' + (error.message || 'Erro desconhecido');
    }
};

// Folder navigation
const navigateToFolder = (folderId, folderName) => {
    authError.value = null;
    downloadError.value = null;
    folderHistory.value.push({id: folderId, name: folderName});
    currentFolderId.value = folderId;
    currentFolderName.value = folderName;
    fetchFileList();
};

const navigateBack = () => {
    authError.value = null;
    downloadError.value = null;
    if (folderHistory.value.length > 1) {
        const newHistory = [...folderHistory.value];
        newHistory.pop();
        folderHistory.value = newHistory;
        const previousFolder = newHistory[newHistory.length - 1];
        currentFolderId.value = previousFolder.id;
        currentFolderName.value = previousFolder.name;
        fetchFileList();
    }
};

// File download from Google Drive
const downloadFile = async (file) => {
    try {
        if (!isGapiAvailable()) {
            throw new Error('Google API não está disponível');
        }

        const token = gapi.client.getToken();
        if (!token) {
            tokenClient.requestAccessToken();
            return;
        }

        const url = `https://www.googleapis.com/drive/v3/files/${file.id}?alt=media`;

        const response = await fetch(url, {
            headers: {
                Authorization: `Bearer ${token.access_token}`
            }
        });

        const blob = await response.blob();
        const downloadUrl = window.URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = downloadUrl;
        link.download = file.name;
        document.body.appendChild(link);
        link.click();
        window.URL.revokeObjectURL(downloadUrl);
        link.remove();
    } catch (error) {
        downloadError.value = `Failed to download ${file.name}: ${error.message}`;
    }
};

// Watchers and lifecycle hooks
watch(
    () => currentFolderId.value,
    (newVal, oldVal) => {
        if (newVal !== oldVal) {
            fetchFileList();
        }
    },
    { immediate: true }
);

onMounted(async () => {
    try {
        await initGapi();
        tokenClient = google.accounts.oauth2.initTokenClient({
            client_id: clientId,
            scope: 'https://www.googleapis.com/auth/drive.readonly',
            callback: (tokenResponse) => {
                if (tokenResponse.error) {
                    authError.value = 'Falha na autenticação: ' + tokenResponse.error;
                    return;
                }
                fetchFileList();
            }
        });
    } catch (error) {
        authError.value = 'Erro na inicialização: ' + error.message;
    }
});
</script>
<template>
    <div class="bg-white/90 dark:bg-gray-800/90 backdrop-blur-sm rounded-lg shadow-md p-6 border border-gray-200/50 dark:border-gray-700/50 transition-colors duration-300">
        <header class="flex justify-between items-center mb-6">
            <div class="flex items-center space-x-3">
                <button
                    v-if="folderHistory.length > 1"
                    @click="navigateBack"
                    class="px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-md hover:bg-gray-200 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50"
                >
                    <i class="fas fa-arrow-left mr-2"></i> Voltar
                </button>
                <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 transition-colors">
                    Backups do Google Drive - {{ currentFolderName }}
                </h2>
            </div>

            <div class="flex items-center space-x-4">
                <div v-if="isLoading" class="flex items-center text-blue-600 dark:text-blue-400">
                    <svg class="animate-spin h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Carregando...
                </div>

                <div class="view-toggle flex space-x-2">
                    <button 
                        @click="viewMode = 'list'" 
                        :class="{'text-blue-600 dark:text-blue-400': viewMode === 'list', 'text-gray-500 dark:text-gray-400': viewMode !== 'list'}" 
                        class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded transition-colors"
                    >
                        <i class="fas fa-list"></i>
                    </button>
                    <button 
                        @click="viewMode = 'grid'" 
                        :class="{'text-blue-600 dark:text-blue-400': viewMode === 'grid', 'text-gray-500 dark:text-gray-400': viewMode !== 'grid'}" 
                        class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded transition-colors"
                    >
                        <i class="fas fa-th-large"></i>
                    </button>
                </div>
            </div>
        </header>

        <div v-if="authError" class="p-4 m-4 bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-300 rounded-md">
            <i class="fas fa-exclamation-triangle mr-2"></i> {{ authError }} -
            <button
                @click="fetchFileList"
                class="ml-2 text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 underline"
            >
                Tentar novamente
            </button>
        </div>
        <div v-if="downloadError" class="p-4 m-4 bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-300 rounded-md">
            <i class="fas fa-exclamation-triangle mr-2"></i> {{ downloadError }}
            <button @click="downloadError = null" class="ml-2 text-red-600 dark:text-red-300 hover:text-red-800 dark:hover:text-red-400">×</button>
        </div>

        <div class="p-4">
            <div v-if="fileList.length > 0" :class="{'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4': viewMode === 'grid', 'space-y-1': viewMode === 'list'}">
                <div v-for="file in fileList" :key="file.id" class="file-item p-2 rounded-md hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                    <button
                        v-if="file.mimeType === MIME_TYPES.FOLDER"
                        @click="navigateToFolder(file.id, file.name)"
                        class="file-link folder w-full text-left flex items-center"
                        :aria-label="`Abrir pasta ${file.name}`"
                    >
                        <span class="icon">
                            <svg class="h-5 w-5 mr-2 text-yellow-500" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M2 7.5C2 5.57179 3.57179 4 5.5 4H8.5C9.05228 4 9.58418 4.21071 9.92893 4.58579L11.5 6.15685L13.0711 7.72792C13.4158 8.10299 13.9477 8.3137 14.5 8.3137H19C20.6569 8.3137 22 9.65679 22 11.3137V16.6863C22 18.3432 20.6569 19.6863 19 19.6863H5C3.34315 19.6863 2 18.3432 2 16.6863V7.5Z" fill="currentColor"/>
                            </svg>
                        </span>
                        <span class="label">{{ file.name }}</span>
                    </button>
                    <button
                        v-else
                        @click="downloadFile(file)"
                        class="file-link file w-full text-left flex items-center"
                    >
                        <span class="icon">
                            <svg class="h-5 w-5 mr-2 text-blue-500" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M6 2H14L22 10V22H6V2Z" fill="currentColor"/>
                            </svg>
                        </span>
                        <span class="label">{{ file.name }}</span>
                    </button>
                </div>
            </div>
            <div v-else-if="!isLoading" class="p-6 text-center text-gray-500 dark:text-gray-400 flex flex-col items-center space-y-3">
                <i class="fas fa-folder-open text-4xl mb-2 text-gray-400"></i>
                <p>Nenhum backup encontrado nesta pasta.</p>
            </div>
            <div v-if="isLoading" class="flex justify-center items-center p-6 flex-col space-y-3">
                <svg class="animate-spin h-8 w-8 text-blue-600 dark:text-blue-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <p class="text-gray-700 dark:text-gray-300">Carregando arquivos...</p>
            </div>
        </div>

        <footer class="p-4 text-sm text-gray-500 dark:text-gray-400 transition-colors duration-300 border-t border-gray-200 dark:border-gray-700">
            <p>Os backups são atualizados automaticamente a cada 24 horas. Clique em um arquivo para fazer o download.</p>
        </footer>
    </div>
</template>

<style scoped>
.file-item {
    @apply rounded-md transition-colors duration-300;
}

.file-item:hover {
    @apply bg-gray-50 dark:bg-gray-800;
}

.file-link {
    @apply flex items-center text-left w-full;
    text-decoration: none;
    color: inherit;
    cursor: pointer;
}

.file-link:hover {
    text-decoration: underline;
}

.folder {
    @apply text-yellow-600 dark:text-yellow-500;
}
.folder:hover {
    @apply text-yellow-700 dark:text-yellow-600;
}

.file {
    @apply text-gray-700 dark:text-gray-300;
}
</style>
