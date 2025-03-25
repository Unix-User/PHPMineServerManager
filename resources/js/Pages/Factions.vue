<script>
import { ref, defineProps } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import axios from 'axios';
import PrimaryButton from '@/Components/PrimaryButton.vue';

defineProps({
    title: String,
});

let factions = [
    { id: 1, name: 'Facção 1', description: 'Descrição da Facção 1' },
    { id: 2, name: 'Facção 2', description: 'Descrição da Facção 2' },
    { id: 3, name: 'Facção 3', description: 'Descrição da Facção 3' }
];


export default {
    components: {
        AppLayout,
        PrimaryButton
    },
    setup() {
        return {
            factions,
            message,
            sendMessage
        };
    },
};
</script>

<template>
    <AppLayout title="Facções">
        <template #header class="flex flex-col sm:flex-row justify-between items-center">
            <div class="flex flex-col sm:flex-row justify-between w-full">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Facções
                </h2>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-5">
                    <div class="container mx-auto" id="content">
                        <div v-for="faction in factions" :key="faction.id">
                            <h2>{{ faction.name }}</h2>
                            <p>{{ faction.description }}</p>

                            <form @submit.prevent="sendMessage">
                                <label for="message" class="sr-only">Mensagem</label>
                                <input type="text" id="message" v-model="message" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" placeholder="Digite sua mensagem aqui">
                                <button type="submit" class="mt-2 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    Enviar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
