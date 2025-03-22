<script>
import { ref, defineProps } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import axios from 'axios';
import PrimaryButton from '@/Components/PrimaryButton.vue';

defineProps({
    type: {
        type: String,
        default: 'submit',
    },
});

const isLoading = ref(false);

const fetchBusyStatus = async () => {
    try {
        const response = await axios.get('/busy');
        isLoading.value = response.data.isBusy === true;
    } catch (error) {
        console.error(`AxiosError: ${error.message}`);
    }
};

const runPoiUpdate = async () => {
    try {
        isLoading.value = true;
        const response = await axios.post('/run-poi-update');
        console.log(response.data);
        alert('Entidades atualizadas com sucesso.');
    } catch (error) {
        console.error(`AxiosError: ${error.message}`);
        alert('Ocorreu um erro ao Atualizar as entidades.');
    } finally {
        isLoading.value = false;
    }
};

const runMapUpdate = async () => {
    try {
        isLoading.value = true;
        const response = await axios.post('/run-map-update');
        console.log(response.data);
        alert('Mapa atualizado com sucesso.');
    } catch (error) {
        console.error(`AxiosError: ${error.message}`);
        alert('Ocorreu um erro ao atualizar o mapa.');
    } finally {
        isLoading.value = false;
    }
};

export default {
    components: {
        AppLayout,
        PrimaryButton
    },
    setup() {
        const factionsIframeSrc = ref('https://dynmap.udianix.com.br/?worldname=udianix.com.br&mapname=surface&zoom=5');
        const skyblockIframeSrc = ref('https://dynmap.udianix.com.br/?worldname=SuperiorWorld&mapname=surface&zoom=7');
        fetchBusyStatus();
        return {
            isLoading,
            factionsIframeSrc,
            skyblockIframeSrc,
            runPoiUpdate,
            runMapUpdate
        };
    },
};
</script>

<template>
    <AppLayout title="Mapa">
        <template #header>
            <div class="flex flex-col sm:flex-row justify-between w-full">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight transition-colors duration-300">
                    Mapa
                </h2>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white/90 dark:bg-gray-800/90 backdrop-blur-sm overflow-hidden shadow-xl sm:rounded-lg transition-all duration-300 ease-in-out hover:shadow-2xl">
                    <div class="container mx-auto p-4">
                        <div class="rounded-lg bg-white/80 dark:bg-gray-800/60 backdrop-blur-sm shadow-sm p-4 m-4 transition-all duration-300 ease-in-out hover:bg-white/90 dark:hover:bg-gray-700/50">
                            <a href="https://dynmap.udianix.com.br/?worldname=udianix.com.br&mapname=surface&zoom=5" 
                               target="_blank"
                               aria-label="Abrir mapa dinâmico em nova aba" 
                               title="Abrir mapa dinâmico em nova aba"
                               class="block">
                                <div class="flex justify-between items-center mb-4">
                                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white transition-colors">
                                        Mapa dinâmico
                                    </h2>
                                    <div class="sm:mt-0 sm:ml-auto">
                                        <svg xmlns="http://www.w3.org/2000/svg" height="1.2em" viewBox="0 0 448 512" 
                                             class="text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors duration-300">
                                            <path fill="currentColor" d="M32 32C14.3 32 0 46.3 0 64v96c0 17.7 14.3 32 32 32s32-14.3 32-32V96h64c17.7 0 32-14.3 32-32s-14.3-32-32-32H32zM64 352c0-17.7-14.3-32-32-32s-32 14.3-32 32v96c0 17.7 14.3 32 32 32h96c17.7 0 32-14.3 32-32s-14.3-32-32-32H64V352zM320 32c-17.7 0-32 14.3-32 32s14.3 32 32 32h64v64c0 17.7 14.3 32 32 32s32-14.3 32-32V64c0-17.7-14.3-32-32-32H320zM448 352c0-17.7-14.3-32-32-32s-32 14.3-32 32v64H320c-17.7 0-32 14.3-32 32s14.3 32 32 32h96c17.7 0 32-14.3 32-32V352z" />
                                        </svg>
                                    </div>
                                </div>
                                <iframe :src="factionsIframeSrc" class="w-full h-[500px] rounded-lg border border-gray-200/50 dark:border-gray-700/50"></iframe>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
@media (max-width: 640px) {
    .container {
        padding: 0;
    }
    
    iframe {
        height: 300px;
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