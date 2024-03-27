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
        const iframeSrc = ref('https://minecraft.udianix.com.br/dynmap/factions');
        const overviewerSrc = ref('https://minecraft.udianix.com.br/dynmap/skyblock');
        fetchBusyStatus();
        return {
            isLoading,
            iframeSrc,
            overviewerSrc,
            runPoiUpdate,
            runMapUpdate
        };
    },
};

</script>
<template>
    <AppLayout title="Mapa">
        <template #header class="flex flex-col sm:flex-row justify-between items-center">
            <div class="flex flex-col sm:flex-row justify-between w-full">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Mapa
                </h2>

            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="container mx-auto" id="content">
                        <div id="mapRow">
                            <a href="https://minecraft.udianix.com.br/dynmap/factions" target="_blank"
                                aria-label="Abrir mapa dinâmico em nova aba" title="Abrir mapa dinâmico em nova aba">
                                <div class="bg-white rounded shadow-sm p-4 m-4" id="mapContent">
                                    <div class="flex justify-between items-center">
                                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Mapa dinâmico
                                        </h2>
                                        <div class="sm:mt-0 sm:ml-auto">
                                            <svg xmlns="http://www.w3.org/2000/svg" height="1em"
                                                viewBox="0 0 448 512"><!--! Font Awesome Free 6.4.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. -->
                                                <path
                                                    d="M32 32C14.3 32 0 46.3 0 64v96c0 17.7 14.3 32 32 32s32-14.3 32-32V96h64c17.7 0 32-14.3 32-32s-14.3-32-32-32H32zM64 352c0-17.7-14.3-32-32-32s-32 14.3-32 32v96c0 17.7 14.3 32 32 32h96c17.7 0 32-14.3 32-32s-14.3-32-32-32H64V352zM320 32c-17.7 0-32 14.3-32 32s14.3 32 32 32h64v64c0 17.7 14.3 32 32 32s32-14.3 32-32V64c0-17.7-14.3-32-32-32H320zM448 352c0-17.7-14.3-32-32-32s-32 14.3-32 32v64H320c-17.7 0-32 14.3-32 32s14.3 32 32 32h96c17.7 0 32-14.3 32-32V352z" />
                                            </svg>
                                        </div>
                                    </div>
                                    <iframe :src="iframeSrc" style="width:100%; height:500px;"></iframe>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="container mx-auto" id="content">
                        <div id="mapRow">
                            <a href="https://minecraft.udianix.com.br/dynmap/skyblock"
                                target="_blank" aria-label="Abrir mapa dinâmico em nova aba"
                                title="Abrir mapa dinâmico em nova aba">
                                <div class="bg-white rounded shadow-sm p-4 m-4" id="mapContent">
                                    <div class="flex justify-between items-center">
                                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Mapa estático
                                        </h2>
                                        <div class="mt-4 sm:mt-0 sm:ml-auto mb-4"
                                            v-if="$page.props.user.roles.includes('admin')">
                                            <PrimaryButton @click="runPoiUpdate" class="ml-auto mr-2" :loading="isLoading">
                                                <template v-if="isLoading">
                                                    <i class="fas fa-spinner fa-spin"></i> Atualizando...
                                                </template>
                                                <template v-else>
                                                    <i class="fas fa-sync"></i> Atualizar Entidades
                                                </template>
                                            </PrimaryButton>
                                            <PrimaryButton @click="runMapUpdate" class="ml-auto" :loading="isLoading">
                                                <template v-if="isLoading">
                                                    <i class="fas fa-spinner fa-spin"></i> Atualizando...
                                                </template>
                                                <template v-else>
                                                    <i class="fas fa-sync"></i> Atualizar Mapa
                                                </template>
                                            </PrimaryButton>
                                        </div>
                                    </div>
                                    <iframe :src="overviewerSrc" style="width:100%; height:500px;"></iframe>
                                </div>
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
    #mapContent {
        padding: 0;
    }
}
</style>