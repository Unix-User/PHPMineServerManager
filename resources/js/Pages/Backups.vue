<script setup>
import { ref, onMounted } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import axios from "axios";
import PrimaryButton from "@/Components/PrimaryButton.vue";

const isLoading = ref(false);
const iframeSrc = ref("https://drive.google.com/embeddedfolderview?id=1ce6Uen0tXTAwM_URk2KfKv6LwpbF77Nk#grid");
const oauthToken = ref(null);
const gapiLoaded = ref(false);
const authError = ref(null);
const googleClientId = import.meta.env.VITE_GOOGLE_CLIENT_ID;

const fetchBusyStatus = async () => {
    try {
        const response = await axios.get("/busy");
        isLoading.value = response.data.isBusy === true;
    } catch (error) {
        console.error(`AxiosError: ${error.message}`);
    }
};

const loadGoogleApi = () => {
  return new Promise((resolve, reject) => {
    if (window.gapi) {
      resolve();
      return;
    }

    const script = document.createElement('script');
    script.src = 'https://apis.google.com/js/api.js';
    script.onload = () => {
      window.gapi.load('client:auth2', () => {
        console.log('Google API carregada com sucesso');
        gapiLoaded.value = true;
        resolve();
      });
    };
    script.onerror = (error) => {
      reject(new Error('Falha ao carregar Google API'));
    };
    document.head.appendChild(script);
  });
};

const initClient = async () => {
  try {
    await loadGoogleApi();
    
    await window.gapi.client.init({
      apiKey: import.meta.env.VITE_GOOGLE_API_KEY,
      clientId: googleClientId,
      discoveryDocs: ["https://www.googleapis.com/discovery/v1/apis/drive/v3/rest"],
      scope: 'https://www.googleapis.com/auth/drive.readonly'
    });

    console.log('Cliente Google inicializado');
    
    window.gapi.auth2.getAuthInstance().isSignedIn.listen(isSignedIn => {
      if (isSignedIn) {
        oauthToken.value = window.gapi.auth2.getAuthInstance().currentUser.get().getAuthResponse().access_token;
      }
    });

    if (window.gapi.auth2.getAuthInstance().isSignedIn.get()) {
      oauthToken.value = window.gapi.auth2.getAuthInstance().currentUser.get().getAuthResponse().access_token;
    }

  } catch (error) {
    console.error('Erro na inicialização:', error);
    authError.value = 'Erro ao conectar com o Google Drive';
  }
};

const handleAuth = async () => {
    try {
        if (!window.gapi || !window.gapi.auth2) {
            throw new Error('Biblioteca Google não carregada');
        }
        
        const authInstance = window.gapi.auth2.getAuthInstance();
        const authResponse = await authInstance.signIn({
            scope: 'https://www.googleapis.com/auth/drive.readonly'
        });
        oauthToken.value = authResponse.getAuthResponse().access_token;
    } catch (error) {
        console.error('Erro de autenticação:', error);
        authError.value = 'Falha ao conectar com Google Drive';
    }
};

onMounted(async () => {
    await fetchBusyStatus();
    await initClient();
});
</script>

<template>
    <AppLayout title="Backups">
        <template #header>
            <div class="flex flex-col sm:flex-row justify-between w-full">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight transition-colors duration-300">
                    Gerenciamento de Backups
                </h2>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white/90 dark:bg-gray-800/90 backdrop-blur-sm overflow-hidden shadow-xl sm:rounded-lg transition-all duration-300 ease-in-out hover:shadow-2xl p-4">
                    <div class="container mx-auto">
                        <div class="bg-white/90 dark:bg-gray-800/90 backdrop-blur-sm rounded-lg shadow-md p-4 m-4 border border-gray-200/50 dark:border-gray-700/50 transition-colors duration-300">
                            <div class="flex justify-between items-center mb-2">
                                <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 transition-colors">
                                    Google Drive Backups
                                </h2>
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
                                    @click="initClient"
                                    class="ml-2 text-blue-600 hover:text-blue-800 underline"
                                >
                                    Tentar novamente
                                </button>
                            </div>

                            <div v-if="!gapiLoaded && !authError" class="p-4 text-red-500">
                                Carregando Google Drive...
                            </div>
                            
                            <div v-else class="bg-gray-100/90 dark:bg-gray-900/90 backdrop-blur-sm rounded-lg p-2 transition-colors duration-300">
                                <button
                                    v-if="!oauthToken"
                                    @click="handleAuth"
                                    class="px-4 py-2 mb-4 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors duration-300"
                                    title="Autenticar com Google">
                                    <i class="fas fa-sign-in-alt"></i> Autenticar com Google
                                </button>
                                <iframe
                                    v-if="oauthToken"
                                    :src="iframeSrc"
                                    class="w-full h-[500px] rounded-lg border border-gray-200/50 dark:border-gray-700/50 transition-colors duration-300"
                                    title="Google Drive Backups"
                                    loading="lazy"
                                    sandbox="allow-scripts allow-same-origin allow-forms allow-popups allow-modals allow-top-navigation"
                                    allow="fullscreen"
                                    referrerpolicy="no-referrer-when-downgrade"
                                ></iframe>
                                <div v-else class="p-4 text-center text-gray-600 dark:text-gray-300">
                                    Por favor, autentique-se para acessar os backups do Google Drive.
                                </div>
                                <div v-if="oauthToken" class="mt-2 flex justify-end space-x-2">
                                    <button
                                        @click="iframeSrc = 'https://drive.google.com/embeddedfolderview?id=1ce6Uen0tXTAwM_URk2KfKv6LwpbF77Nk#grid'"
                                        class="px-3 py-1.5 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors duration-300"
                                        title="Voltar para pasta principal">
                                        <i class="fas fa-home"></i>
                                    </button>
                                    <button
                                        @click="iframeSrc = iframeSrc"
                                        class="px-3 py-1.5 text-sm bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors duration-300"
                                        title="Recarregar página">
                                        <i class="fas fa-sync-alt"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="mt-4 text-sm text-gray-600 dark:text-gray-300 transition-colors duration-300">
                                <p>Os backups são atualizados automaticamente a cada 24 horas. Clique em um arquivo para fazer o download.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
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

/* Improved iframe styling */
iframe {
    background-color: transparent;
    min-height: 500px;
    border: 1px solid rgba(209, 213, 219, 0.3);
}

.dark iframe {
    border-color: rgba(55, 65, 81, 0.3);
}
</style>
