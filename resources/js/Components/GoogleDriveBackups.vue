<script setup>
import { ref, onMounted, watchEffect } from "vue";
import axios from "axios";

const isLoading = ref(false);
const oauthToken = ref(null);
const authError = ref(null);
const googleClientId = import.meta.env.VITE_GOOGLE_CLIENT_ID;
const fileList = ref([]);
const currentFolderId = ref('root');
const folderHistory = ref(['root']);
const isGoogleScriptLoaded = ref(false);

const fetchBusyStatus = async () => {
    try {
        const response = await axios.get("/busy");
        isLoading.value = response.data.isBusy === true;
    } catch (error) {
        console.error(`Erro ao buscar status: ${error.message}`);
    }
};

const initGoogleAuth = () => {
    return new Promise((resolve, reject) => {
        if (window.google) {
            isGoogleScriptLoaded.value = true;
            return resolve();
        }

        const script = document.createElement('script');
        script.src = 'https://accounts.google.com/gsi/client';
        script.async = true;
        script.defer = true;
        script.onload = () => {
            isGoogleScriptLoaded.value = true;
            resolve();
        };
        script.onerror = () => {
            authError.value = 'Falha ao carregar Google Identity Services';
            reject(new Error('Falha ao carregar script Google'));
        };
        document.head.appendChild(script);
    });
};

const handleAuth = async () => {
    try {
        await initGoogleAuth();
        
        const client = google.accounts.oauth2.initCodeClient({
            client_id: googleClientId,
            scope: 'https://www.googleapis.com/auth/drive.readonly',
            callback: async (response) => {
                if (response.error) {
                    authError.value = 'Erro na autenticação: ' + response.error;
                    return;
                }

                try {
                    const tokenResponse = await axios.post('/auth/google', {
                        code: response.code
                    });
                    oauthToken.value = tokenResponse.data.access_token;
                    folderHistory.value = ['root'];
                    fetchFileList();
                } catch (error) {
                    handleAuthError(error);
                }
            },
            ux_mode: 'popup',
            client_type: 'IDP',
            redirect_uri: window.location.origin + '/auth/google/callback'
        });
        client.requestCode();
    } catch (error) {
        authError.value = 'Erro na inicialização: ' + error.message;
    }
};

const fetchFileList = async () => {
    if (!oauthToken.value) return;

    isLoading.value = true;
    fileList.value = [];

    try {
        const response = await axios.get('https://www.googleapis.com/drive/v3/files', {
            headers: { Authorization: `Bearer ${oauthToken.value}` },
            params: {
                q: `'${currentFolderId.value}' in parents and trashed=false`,
                fields: 'files(id, name, mimeType, size, modifiedTime, webContentLink)',
                orderBy: 'folder,name'
            },
        });
        fileList.value = response.data.files.sort((a, b) => {
            if (a.mimeType === 'application/vnd.google-apps.folder') return -1;
            if (b.mimeType === 'application/vnd.google-apps.folder') return 1;
            return 0;
        });
    } catch (error) {
        handleDriveError(error);
    } finally {
        isLoading.value = false;
    }
};

const handleDriveError = (error) => {
    if (error.response?.status === 401 || error.response?.status === 403) {
        authError.value = 'Sessão expirada. Por favor, autentique-se novamente.';
        oauthToken.value = null;
    } else {
        authError.value = 'Erro ao carregar arquivos: ' + error.message;
    }
};

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

const downloadFile = async (file) => {
    try {
        if (file.webContentLink) {
            window.open(file.webContentLink, '_blank');
        } else {
            const response = await axios.get(`https://www.googleapis.com/drive/v3/files/${file.id}`, {
                headers: { Authorization: `Bearer ${oauthToken.value}` },
                params: { alt: 'media' },
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
        }
    } catch (error) {
        alert("Erro ao baixar o arquivo: " + error.message);
    }
};

watchEffect(() => {
    if (oauthToken.value) {
        fetchFileList();
    }
});

onMounted(async () => {
    await fetchBusyStatus();
    await initGoogleAuth().catch(() => {});
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
                    Google Drive Backups
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
                @click="initGoogleAuth"
                class="ml-2 text-blue-600 hover:text-blue-800 underline"
            >
                Tentar novamente
            </button>
        </div>

        <div class="bg-gray-100/90 dark:bg-gray-900/90 backdrop-blur-sm rounded-lg p-6 transition-colors duration-300 flex flex-col items-center justify-center min-h-[300px]">
            <div class="max-w-md w-full space-y-4 text-center">
                <button
                    v-if="!oauthToken"
                    @click="handleAuth"
                    class="gsi-material-button w-full max-w-xs mx-auto transform hover:scale-105 transition-transform duration-200"
                    title="Autenticar com Google"
                    :disabled="!isGoogleScriptLoaded"
                >
                    <span class="gsi-material-button-contents flex items-center justify-center space-x-2">
                        <svg version="1.1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" class="gsi-material-button-icon w-6 h-6">
                            <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"></path>
                            <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"></path>
                            <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"></path>
                            <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"></path>
                        </svg>
                        <span class="gsi-material-button-text">Autenticar com Google</span>
                    </span>
                </button>

                <div v-if="oauthToken" class="w-full">
                    <ul v-if="fileList.length > 0" class="file-list space-y-2">
                        <li v-for="file in fileList" :key="file.id" class="file-item rounded-lg p-3 hover:bg-gray-200/50 dark:hover:bg-gray-700/50 transition-colors">
                            <button v-if="file.mimeType === 'application/vnd.google-apps.folder'" @click="navigateToFolder(file.id)" class="file-link folder w-full text-left">
                                <i class="fas fa-folder mr-2 text-yellow-400"></i> {{ file.name }}
                            </button>
                            <a v-else @click.prevent="downloadFile(file.id)" class="file-link file w-full text-left block">
                                <i class="fas fa-file mr-2 text-blue-400"></i> {{ file.name }}
                            </a>
                        </li>
                    </ul>
                    <div v-else-if="oauthToken && !isLoading" class="p-4 text-center text-gray-600 dark:text-gray-300">
                        <i class="fas fa-inbox text-3xl mb-2 text-gray-400"></i>
                        <p>Nenhum backup encontrado nesta pasta.</p>
                    </div>
                    <div v-if="oauthToken && isLoading" class="flex justify-center p-4">
                        <svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-blue-600 dark:text-blue-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Carregando arquivos...
                    </div>
                </div>
                <div v-else class="p-4 text-center text-gray-600 dark:text-gray-300">
                    <i class="fas fa-cloud-upload-alt text-3xl mb-2 text-gray-400"></i>
                    <p>Por favor, autentique-se para acessar os backups do Google Drive.</p>
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


