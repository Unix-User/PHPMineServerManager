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

export default {
    data() {
        return {
            isLoading: false,
        }
    },
    methods: {
        runPoiUpdate() {
            this.isLoading = true;
            axios.post('/run-poi-update')
                .then(response => {
                    console.log(response.data);
                    alert('Entidades atualizadas com sucesso.');
                    this.isLoading = false;
                })
                .catch(error => {
                    console.error(error);
                    alert('Ocorreu um erro ao Atualizar as entidades.');
                    this.isLoading = false;
                });
        },
        runMapUpdate() {
            this.isLoading = true;
            axios.post('/run-map-update')
                .then(response => {
                    console.log(response.data);
                    alert('Mapa atualizado com sucesso.');
                    this.isLoading = false;
                })
                .catch(error => {
                    console.error(error);
                    alert('Ocorreu um erro ao autualiza o mapa.');
                    this.isLoading = false;
                });
        }
    },
    components: {
        AppLayout,
        PrimaryButton
    },
    setup() {
        const iframeSrc = ref('http://minecraft.udianix.com.br:8082/'); // Removed process.env usage
        return {
            iframeSrc,
        };
    },
};

</script>

<template>
    <AppLayout title="Map">
        <template #header class="flex justify-between items-center">
            <div class="flex justify-between w-full">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Mapa
                </h2>
                <div class="ml-auto">
                    <PrimaryButton @click="runPoiUpdate" class="ml-auto mr-2" :loading="isLoading">
                        <template v-if="isLoading">
                            <i class="fas fa-spinner fa-spin"></i> Loading...
                        </template>
                        <template v-else>
                            <i class="fas fa-sync"></i> Atualizar Entidades
                        </template>
                    </PrimaryButton>
                    <PrimaryButton @click="runMapUpdate" class="ml-auto" :loading="isLoading">
                        <template v-if="isLoading">
                            <i class="fas fa-spinner fa-spin"></i> Loading...
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

<style scoped></style>




