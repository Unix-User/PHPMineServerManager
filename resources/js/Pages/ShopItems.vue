<script setup>
import { ref, reactive, toRefs } from 'vue';
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
    item_photo_path: '/storage/shop-item-photos/default.png', // Set default image path
});
const imagePreview = ref(null);
const shopItems = ref([]);
const errors = reactive({
    name: [],
    description: [],
    price: [],
});

const fetchShopItems = async () => {
    try {
        const response = await axios.get('/shop/items');
        if (Array.isArray(response.data)) {
            shopItems.value = response.data.map(item => {
                // If item does not have an image, set it to default.png
                if (!item.image) {
                    item.image = '/storage/shop-item-photos/default.png';
                }
                return item;
            });
        } else {
            console.error('Error: response data is not an array');
        }
    } catch (error) {
        console.error(error);
    }
};

const resetForm = () => {
    for (let key in form) {
        if (key === 'item_photo_path') {
            form[key] = '/storage/shop-item-photos/default.png'; // Reset image path to default
        } else {
            form[key] = null;
        }
    }
    imagePreview.value = null;
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
        if (!form.item_photo_path) {
            form.item_photo_path = '/storage/shop-item-photos/default.png'; // If no image, set to default
        }
        toggleModal();
    }
};

const handleRequest = async (method, url, data) => {
    try {
        const config = {
            headers: {
                'content-type': 'multipart/form-data'
            }
        };
        await axios({ method, url, data, config });
        resetForm();
        if (isOpen.value) {
            toggleModal();
        }
        fetchShopItems();
    } catch (error) {
        if (error.response && error.response.data.errors) {
            Object.assign(errors, error.response.data.errors);
        } else {
            console.error(error);
        }
    }
};

const save = async (data) => {
    const formData = new FormData();
    formData.append('name', data.name);
    formData.append('description', data.description);
    formData.append('price', data.price);
    if (data.item_photo_path && data.item_photo_path !== '/storage/shop-item-photos/default.png') {
        let blob = await fetch(data.item_photo_path).then(r => r.blob());
        formData.append('item_photo_path', blob, data.item_photo_path.name);
    }
    await handleRequest('post', '/shop/items', formData);
    if (isOpen.value) {
        toggleModal();
    }
};
const update = async (data) => {
    const formData = new FormData();
    formData.append('_method', 'PUT');
    formData.append('name', data.name);
    formData.append('description', data.description);
    formData.append('price', data.price);
    if (data.item_photo_path && data.item_photo_path !== '/storage/shop-item-photos/default.png') {
        let blob = await fetch(data.item_photo_path).then(r => r.blob());
        formData.append('item_photo_path', blob, data.item_photo_path.name);
    }
    try {
        await handleRequest('put', `/shop/items/${data.id}`, formData);
        if (isOpen.value) {
            toggleModal();
        }
    } catch (error) {
        console.error(error);
    }
};

const deleteRow = (data) => data && confirm('Are you sure want to remove?') && handleRequest('delete', `/shop/items/${data.id}`);

const updateImagePreview = (event) => {
    const reader = new FileReader();
    reader.onload = (e) => {
        imagePreview.value = e.target.result;
        form.item_photo_path = e.target.result;
    };
    reader.readAsDataURL(event.target.files[0]);
};

const selectNewImage = () => {
    imagePreview.value = null;
    document.getElementById('image').click();
};

const deleteImage = () => {
    form.item_photo_path = '/storage/shop-item-photos/default.png'; // Set image path to default when deleted
    imagePreview.value = null;
    document.getElementById('image').value = null;
};

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
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div v-for="(row, index) in $page.props.shopItems" :key="index"
                            class="rounded-lg overflow-hidden shadow-lg p-4 bg-white">
                            <div class="px-6 py-4">
                                <div class="font-bold text-xl mb-2 text-center">{{ row.name }}</div>
                                <img class="w-full h-64 object-cover mt-2" :src="row.image || '/storage/shop-item-photos/default.png'"
                                    alt="Product image">
                                <p class="text-gray-700 text-base mt-2">{{ row.description }}</p>
                            </div>
                            <div class="px-6 py-4 flex justify-between items-center mt-4">
                                <span
                                    class="inline-block bg-gray-200 rounded-full px-3 py-1 text-sm font-semibold text-gray-700 mr-2 mb-2">{{
                                        row.price }}</span>
                                <div class="px-6 py-4 flex" v-if="$page.props.user.roles.includes('admin')">
                                    <PrimaryButton @click="edit(row)">Edit</PrimaryButton>
                                    <SecondaryButton @click="deleteRow(row)">Delete</SecondaryButton>
                                </div>
                            </div>
                        </div>
                    </div>
                    <Modal :show="isOpen" @close="toggleModal">
                        <form enctype="multipart/form-data">
                            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                <div class="">
                                    <div class="mb-4">
                                        <InputLabel for="name" value="Name" />
                                        <TextInput id="name" v-model="form.name" type="text" class="mt-1 block w-full"
                                            autocomplete="name" />
                                        <p class="text-red-500 text-xs italic" v-if="errors.name">{{ errors.name[0] }}</p>
                                    </div>
                                    <div class="mb-4">
                                        <InputLabel for="description" value="Description" />
                                        <TextInput id="description" v-model="form.description" type="text"
                                            class="mt-1 block w-full" autocomplete="description" />
                                        <p class="text-red-500 text-xs italic" v-if="errors.description">{{
                                            errors.description[0] }}</p>
                                    </div>
                                    <div class="mb-4">
                                        <InputLabel for="price" value="Price" />
                                        <TextInput id="price" v-model="form.price" type="number" class="mt-1 block w-full"
                                            autocomplete="price" />
                                        <p class="text-red-500 text-xs italic" v-if="errors.price">{{ errors.price[0] }}</p>
                                    </div>
                                    <div class="mb-4">
                                        <InputLabel for="image" value="Image" />
                                        <input id="image" type="file" class="mt-1 block w-full"
                                            @change="updateImagePreview($event)" />
                                        <img v-if="imagePreview" :src="imagePreview" class="mt-2" />
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

