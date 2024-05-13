<script setup>
import { ref } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import axios from 'axios';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Modal from '@/Components/Modal.vue'; // Importing a Modal component

const vipItems = ref([]);
const showModal = ref(false); // State to control modal visibility

const fetchVipItems = async () => {
    try {
        const response = await axios.get('assinatura/vip/items');
        vipItems.value = response.data;
    } catch (error) {
        console.error('Error fetching VIP items:', error);
    }
};

fetchVipItems();

const openModal = () => {
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
};
</script>

<template>
    <AppLayout title="Planos de Assinatura VIP">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Planos de Assinatura VIP
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                    <Modal v-if="showModal" @close="closeModal">
                        <template #header>
                            <h3 class="text-lg font-bold">Adicionar Nova Assinatura VIP</h3>
                        </template>
                        <template #body>
                            <form @submit.prevent="addNewVipItem">
                                <label for="name">Nome:</label>
                                <input type="text" id="name" name="name" required>
                                <label for="description">Descrição:</label>
                                <textarea id="description" name="description" required></textarea>
                                <PrimaryButton type="submit">Salvar</PrimaryButton>
                            </form>
                        </template>
                    </Modal>
                    <div class="px-4 py-4">
                        <div class="flex justify-between mb-4">
                            <h2 class="text-xl font-bold">Itens de Assinatura VIP</h2>
                            <PrimaryButton @click="openModal">Adicionar Nova Assinatura</PrimaryButton>
                        </div>
                        <div class="grid grid-cols-3 gap-4">
                            <div v-for="(item, index) in vipItems" :key="index" class="bg-white dark:bg-gray-800 border dark:border-gray-700 rounded-lg p-4">
                                <img :src="item.vip_photo_path || '/storage/vip-photos/default.png'" alt="VIP Image" class="w-full h-32 object-cover rounded-t-lg">
                                <div class="p-4">
                                    <h3 class="text-lg font-bold">{{ item.name }}</h3>
                                    <p>{{ item.description }}</p>
                                    <PrimaryButton class="mt-4">Comprar</PrimaryButton>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script>
const addNewVipItem = () => {
    // Logic to add a new VIP subscription item
    console.log("Adding a new VIP subscription item");
    closeModal(); // Close modal after adding item
};
</script>
