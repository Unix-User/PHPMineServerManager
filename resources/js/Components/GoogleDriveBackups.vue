<script setup>
import { ref, onMounted, watch } from "vue";

// Constantes reutilizáveis
const MIME_TYPES = {
    FOLDER: 'application/vnd.google-apps.folder'
};

// Estado reativo
const isLoading = ref(false);
const authError = ref(null);
const fileList = ref([]);
const currentFolderId = ref('root');
const currentFolderName = ref('Meu Drive');
const folderHistory = ref([{id: 'root', name: 'Meu Drive'}]);
const downloadError = ref(null);

// Verifica se o gapi está carregado
const isGapiLoaded = ref(false);

// Função para inicializar o gapi com tratamento de CSP
const initGapi = () => {
    return new Promise((resolve, reject) => {
        // Verifica se o script já foi carregado
        if (window.gapi) {
            resolve();
            return;
        }

        const script = document.createElement('script');
        script.src = 'https://apis.google.com/js/api.js';
        script.onload = () => {
            gapi.load('client:auth2', {
                timeout: 10000, // Timeout de 10 segundos
                callback: () => {
                    gapi.client.init({
                        'apiKey': import.meta.env.VITE_GOOGLE_API_KEY,
                        'discoveryDocs': ['https://www.googleapis.com/discovery/v1/apis/drive/v3/rest'],
                        'clientId': import.meta.env.VITE_GOOGLE_CLIENT_ID,
                        'scope': 'https://www.googleapis.com/auth/drive.readonly'
                    }).then(() => {
                        isGapiLoaded.value = true;
                        resolve();
                    }).catch(error => {
                        console.error('Erro ao inicializar gapi.client:', error);
                        reject(new Error('Falha ao inicializar a API do Google Drive'));
                    });
                },
                onerror: (error) => {
                    console.error('Erro ao carregar gapi.client:', error);
                    reject(new Error('Falha ao carregar a API do Google Drive'));
                }
            });
        };
        script.onerror = (error) => {
            console.error('Erro ao carregar script da API:', error);
            reject(new Error('Falha ao carregar o script da API do Google'));
        };
        document.head.appendChild(script);
    });
};

// Função para listar arquivos com tratamento de erros aprimorado
const fetchFileList = async () => {
    if (!isGapiLoaded.value) {
        try {
            await initGapi();
        } catch (error) {
            authError.value = 'Erro ao carregar a API do Google Drive: ' + error.message;
            return;
        }
    }

    isLoading.value = true;
    
    try {
        // Se não for a pasta raiz, busca o nome da pasta atual
        if (currentFolderId.value !== 'root') {
            const folderInfo = await gapi.client.drive.files.get({
                fileId: currentFolderId.value,
                fields: 'name'
            });
            currentFolderName.value = folderInfo.result.name;
        } else {
            currentFolderName.value = 'Meu Drive';
        }

        // Usando a API do Google Drive com tratamento de erros
        const response = await gapi.client.drive.files.list({
            q: `'${currentFolderId.value}' in parents and trashed = false`,
            fields: 'files(id, name, mimeType)',
            orderBy: 'name',
            timeout: 10000 // Timeout de 10 segundos
        });

        if (!response || !response.result || !response.result.files) {
            throw new Error('Resposta inválida da API do Google Drive');
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

// Tratamento de erros aprimorado
const handleDriveError = (error) => {
    console.error('Erro no Google Drive:', error);
    
    if (error.status === 401 || error.status === 403) {
        authError.value = 'Erro de autenticação. Por favor, faça login novamente.';
    } else if (error.status === 404) {
        authError.value = 'Recurso não encontrado. Verifique as permissões e tente novamente.';
    } else {
        authError.value = 'Erro ao carregar arquivos: ' + (error.message || 'Erro desconhecido');
    }
};

// Navegação entre pastas
const navigateToFolder = (folderId, folderName) => {
    folderHistory.value.push({id: folderId, name: folderName});
    currentFolderId.value = folderId;
    currentFolderName.value = folderName;
    fetchFileList();
};

const navigateBack = () => {
    if (folderHistory.value.length > 1) {
        const newHistory = [...folderHistory.value.slice(0, -1)];
        folderHistory.value = newHistory;
        const previousFolder = newHistory[newHistory.length - 1];
        currentFolderId.value = previousFolder.id;
        currentFolderName.value = previousFolder.name;
        fetchFileList();
    }
};

// Download de arquivos diretamente do Google Drive
const downloadFile = async (file) => {
    try {
        if (!isGapiLoaded.value) {
            await initGapi();
        }
        
        const accessToken = gapi.auth.getToken().access_token;
        const url = `https://www.googleapis.com/drive/v3/files/${file.id}?alt=media`;
        
        const response = await fetch(url, {
            headers: {
                Authorization: `Bearer ${accessToken}`
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
        downloadError.value = `Falha ao baixar ${file.name}: ${error.message}`;
    }
};

// Watchers e lifecycle hooks
watch(
    () => currentFolderId.value,
    () => fetchFileList(),
    { immediate: true }
);

onMounted(() => {
    initGapi().then(() => {
        fetchFileList();
    }).catch(error => {
        authError.value = 'Erro ao carregar a API do Google Drive: ' + error.message;
    });
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
                    ← Voltar
                </button>
                <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 transition-colors">
                    Google Drive Backups - {{ currentFolderName }}
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
                Tentar novamente
            </button>
        </div>
        <div v-if="downloadError" class="p-4 mb-4 bg-red-100 text-red-700 rounded-lg">
            {{ downloadError }}
            <button @click="downloadError = null" class="ml-2 text-red-600 hover:text-red-800">×</button>
        </div>

        <div class="bg-gray-100/90 dark:bg-gray-900/90 backdrop-blur-sm rounded-lg p-6 transition-colors duration-300 flex flex-col items-center justify-center min-h-[300px]">
            <div class="max-w-md w-full space-y-4 text-center">
                <ul v-if="fileList.length > 0" class="file-list space-y-2">
                    <li v-for="file in fileList" :key="file.id" class="file-item rounded-lg p-3 hover:bg-gray-200/50 dark:hover:bg-gray-700/50 transition-colors">
                        <button
                            v-if="file.mimeType === MIME_TYPES.FOLDER"
                            @click="navigateToFolder(file.id, file.name)"
                            class="file-link folder w-full text-left"
                            role="treeitem"
                            :aria-label="`Abrir pasta ${file.name}`"
                        >
                            <i class="fas fa-folder mr-2 text-yellow-400"></i> {{ file.name }}
                        </button>
                        <a v-else @click.prevent="downloadFile(file)" class="file-link file w-full text-left block">
                            <i class="fas fa-file mr-2 text-blue-400"></i> {{ file.name }}
                        </a>
                    </li>
                </ul>
                <div v-else-if="!isLoading" class="p-4 text-center text-gray-600 dark:text-gray-300">
                    <i class="fas fa-inbox text-3xl mb-2 text-gray-400"></i>
                    <p>Nenhum backup encontrado nesta pasta.</p>
                </div>
                <div v-if="isLoading" class="flex justify-center p-4">
                    <svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-blue-600 dark:text-blue-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Carregando arquivos...
                </div>
            </div>
        </div>
        <div class="mt-4 text-sm text-gray-600 dark:text-gray-300 transition-colors duration-300">
            <p>Os backups são atualizados automaticamente a cada 24 horas. Clique em um arquivo para fazer o download.</p>
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
