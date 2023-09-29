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
        console.error(error);
    }
};

const runPoiUpdate = async () => {
    try {
        isLoading.value = true;
        const response = await axios.post('/run-poi-update');
        console.log(response.data);
        alert('Entidades atualizadas com sucesso.');
    } catch (error) {
        console.error(error);
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
        console.error(error);
        alert('Ocorreu um erro ao autualiza o mapa.');
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
        const iframeSrc = ref('https://overviewer.udianix.com.br/#/-433/64/241/-1/survival/daytime');
        fetchBusyStatus();
        return {
            isLoading,
            iframeSrc,
            runPoiUpdate,
            runMapUpdate
        };
    },
};

</script>
<template>
    <AppLayout title="Map">
        <template #header class="flex flex-col sm:flex-row justify-between items-center">
            <div class="flex flex-col sm:flex-row justify-between w-full">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Mapa atualizado diariamente
                </h2>
                <div class="mt-4 sm:mt-0 sm:ml-auto" v-if="$page.props.user.roles.includes('admin')">
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
        </template>
        
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="container mx-auto" id="content">
                        <div id="mapRow">
                            <div class="bg-white rounded shadow-sm p-4 mb-4" id="mapContent">
                                <iframe :src="iframeSrc" style="width:100%; height:500px;"></iframe>
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
    #mapContent {
        padding: 0;
    }
}
</style>