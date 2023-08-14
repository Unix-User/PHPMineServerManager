<script setup>
import { ref, reactive } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import axios from 'axios';

const editMode = ref(false);
const isOpen = ref(false);
const form = reactive({
    name: null,
    description: null,
    price: null,
});

const shopItems = ref([]);

const fetchShopItems = async () => {
    try {
        const response = await axios.get('/shop/items');
        shopItems.value = response.data;
    } catch (error) {
        console.error(error);
    }
};

const resetForm = () => {
    for (let key in form) {
        form[key] = null;
    }
};

const toggleModal = () => {
    isOpen.value = !isOpen.value;
    if (!isOpen.value) {
        resetForm();
        editMode.value = false;
    }
};

const edit = (data) => {
    if (data) {
        editMode.value = true;
        Object.assign(form, data);
        toggleModal();
    }
};

const handleRequest = async (method, url, data) => {
    try {
        await axios({ method, url, data });
        resetForm();
        if (isOpen.value) {
            toggleModal();
        }
        fetchShopItems();
    } catch (error) {
        console.error(error);
    }
};

const save = async (data) => {
    await handleRequest('post', '/shop/items', data);
    if (isOpen.value) {
        toggleModal();
    }
};

const update = async (data) => {
    await handleRequest('put', `/shop/items/${data.id}`, data);
    if (isOpen.value) {
        toggleModal();
    }
};

const deleteRow = (data) => data && confirm('Are you sure want to remove?') && handleRequest('delete', `/shop/items/${data.id}`);

fetchShopItems();
</script>

<template>
    <AppLayout title="Shop Items">
        <template #header>
            <div class="flex justify-between w-full">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Shop Items
                </h2>
                <div class="ml-auto" v-if="$page.props.user.roles.includes('admin')">
                    <PrimaryButton @click="toggleModal" class="ml-auto mr-2">
                        Create New Shop Item
                    </PrimaryButton>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg px-4 py-4">

                    <table class="table-fixed w-full">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="px-4 py-2 w-20">No.</th>
                                <th class="px-4 py-2">Name</th>
                                <th class="px-4 py-2">Description</th>
                                <th class="px-4 py-2">Price</th>
                                <th class="px-4 py-2" v-if="$page.props.user.roles.includes('admin')">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(row, index) in $page.props.shopItems" :key="index">
                                <td class="border px-4 py-2">{{ index + 1 }}</td>
                                <td class="border px-4 py-2">{{ row.name }}</td>
                                <td class="border px-4 py-2">{{ row.description }}</td>
                                <td class="border px-4 py-2">{{ row.price }}</td>
                                <td class="border px-4 py-2" v-if="$page.props.user.roles.includes('admin')">
                                    <PrimaryButton @click="edit(row)">Edit</PrimaryButton>
                                    <SecondaryButton @click="deleteRow(row)">Delete</SecondaryButton>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <Modal :show="isOpen" @close="toggleModal">
                        <form enctype="multipart/form-data">
                            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                <div class="">
                                    <div class="mb-4">
                                        <InputLabel for="name" value="Name" />
                                        <TextInput id="name" v-model="form.name" type="text" class="mt-1 block w-full"
                                            autocomplete="name" />
                                    </div>
                                    <div class="mb-4">
                                        <InputLabel for="description" value="Description" />
                                        <TextInput id="description" v-model="form.description" type="text"
                                            class="mt-1 block w-full" autocomplete="description" />
                                    </div>
                                    <div class="mb-4">
                                        <InputLabel for="price" value="Price" />
                                        <TextInput id="price" v-model="form.price" type="number" class="mt-1 block w-full"
                                            autocomplete="price" />
                                    </div>
                                </div>
                            </div>
                            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                <PrimaryButton type="button"
                                    class="inline-flex justify-center w-full rounded-md border border-transparent px-4 py-2 bg-green-600 text-base leading-6 font-medium text-white shadow-sm hover:bg-green-500 focus:outline-none focus:border-green-700 focus:shadow-outline-green transition ease-in-out duration-150 sm:text-sm sm:leading-5"
                                    v-show="!editMode" @click="save(form)">
                                    Save
                                </PrimaryButton>
                                <PrimaryButton type="button"
                                    class="inline-flex justify-center w-full rounded-md border border-transparent px-4 py-2 bg-green-600 text-base leading-6 font-medium text-white shadow-sm hover:bg-green-500 focus:outline-none focus:border-green-700 focus:shadow-outline-green transition ease-in-out duration-150 sm:text-sm sm:leading-5"
                                    v-show="editMode" @click="update(form)">
                                    Update
                                </PrimaryButton>
                                <SecondaryButton @click="toggleModal" type="button"
                                    class="inline-flex justify-center w-full rounded-md border border-gray-300 px-4 py-2 bg-white text-base leading-6 font-medium text-gray-700 shadow-sm hover:text-gray-500 focus:outline-none focus:border-blue-300 focus:shadow-outline-blue transition ease-in-out duration-150 sm:text-sm sm:leading-5">
                                    Cancel
                                </SecondaryButton>
                            </div>
                        </form>
                    </Modal>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
