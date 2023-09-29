<script setup>
import { ref, reactive, toRefs } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import axios from 'axios';

const state = reactive({
    editMode: false,
    isOpen: false,
    form: {
        title: null,
        content: null,
        author_id: null,
        category_id: null,
        published_at: null,
        post_image_path: '/storage/post-image-photos/default.png',
    },
    imagePreview: null,
    updatePosts: [],
    errors: {
        title: [],
        content: [],
        author_id: [],
        category_id: [],
        published_at: [],
    },
});

const { editMode, isOpen, form, imagePreview, updatePosts, errors } = toRefs(state);

const fetchUpdatePosts = async () => {
    try {
        const response = await axios.get('/update/posts');
        console.log('Dados recebidos da API:', response.data); // Verifique os dados recebidos
        if (Array.isArray(response.data)) {
            updatePosts.value = response.data.map((post) => {
                if (!post.post_image_path) {
                    post.post_image_path = '/storage/post-image-photos/default.png';
                }
                return post;
            });
        } else {
            console.error('Erro: os dados da resposta não são um array');
        }
    } catch (error) {
        console.error(error);
    }
};

const resetForm = () => {
    for (let key in form) {
        if (key === 'post_image_path') {
            form[key] = '/storage/post-image-photos/default.png'; // Reset image path to default
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
        toggleModal();
    }
};

const handleRequest = async (method, url, data) => {
    try {
        const config = {
            headers: {
                'content-type': 'multipart/form-data',
            },
        };
        await axios({ method, url, data, config });
        resetForm();
        if (isOpen.value) {
            toggleModal();
        }
        await fetchUpdatePosts(); // Atualize a lista de posts após uma operação bem-sucedida.
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
    formData.append('title', data.title);
    formData.append('content', data.content);
    formData.append('category_id', data.category_id);
    if (data.post_image_path && data.post_image_path !== '/storage/post-image-photos/default.png') {
        let blob = await fetch(data.post_image_path).then(r => r.blob());
        formData.append('post_image_path', blob, data.item_photo_path.name);
    }
    await handleRequest('post', '/update/posts', formData);
    updatePosts.value.push(data);
    if (isOpen.value) {
        toggleModal();
    }
};

const update = async (data) => {
    await handleRequest('put', `/update/posts/${data.id}`, data);
    if (isOpen.value) {
        toggleModal();
    }
};

const deleteRow = async (data) => {
    if (data && confirm('Tem certeza de que deseja remover?')) {
        await handleRequest('delete', `/update/posts/${data.id}`);
    }
};

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
    form.item_photo_path = '/storage/post-image-photos/default.png'; // Set image path to default when deleted
    imagePreview.value = null;
    document.getElementById('image').value = null;
};

fetchUpdatePosts();
</script>

<template>
    <AppLayout title="Posts">
        <template #header>
            <div class="flex justify-between w-full">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Posts
                </h2>
                <div class="ml-auto" v-if="$page.props.user.roles.includes('admin')">
                    <PrimaryButton @click="toggleModal" class="ml-auto mr-2">
                        Criar Novo Post
                    </PrimaryButton>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg px-4 py-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div v-for="(row, index) in $page.props.updatePosts" :key="index"
                            class="rounded-lg overflow-hidden shadow-lg p-4 bg-white">
                            <div class="px-6 py-4">
                                <div class="font-bold text-xl mb-2 text-center">{{ row.title }}</div>
                                <img class="w-full h-64 object-cover mt-2"
                                    :src="row.post_image_path || '/storage/post-image-photos/default.png'" alt="Post image">
                                <p class="text-gray-700 text-base mt-2">{{ row.content }}</p>
                            </div>
                            <div class="px-6 py-4 flex justify-between items-center mt-4">
                                <div class="px-6 py-4 flex" v-if="$page.props.user.roles.includes('admin')">
                                    <PrimaryButton @click="edit(row)">Editar</PrimaryButton>
                                    <SecondaryButton @click="deleteRow(row)">Deletar</SecondaryButton>
                                </div>
                            </div>
                        </div>
                    </div>
                    <Modal :show="isOpen" @close="toggleModal">
                        <form enctype="multipart/form-data">
                            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                <div class="">
                                    <div class="mb-4">
                                        <InputLabel for="title" value="Título" />
                                        <TextInput id="title" v-model="form.title" type="text" class="mt-1 block w-full"
                                            autocomplete="title" />
                                        <p class="text-red-500 text-xs italic" v-if="errors.title">{{ errors.title[0] }}</p>
                                    </div>
                                    <div class="mb-4">
                                        <InputLabel for="content" value="Conteúdo" />
                                        <TextInput id="content" v-model="form.content" type="text" class="mt-1 block w-full"
                                            autocomplete="content" />
                                        <p class="text-red-500 text-xs italic" v-if="errors.content">{{
                                            errors.content[0] }}</p>
                                    </div>
                                    <div class="mb-4">
                                        <InputLabel for="category_id" value="Categoria" />
                                        <TextInput id="category_id" v-model="form.category_id" type="text"
                                            class="mt-1 block w-full" autocomplete="category_id" />
                                        <p class="text-red-500 text-xs italic" v-if="errors.category_id">{{
                                            errors.category_id[0] }}</p>
                                    </div>
                                    <div class="mb-4">
                                        <InputLabel for="post_image_path" value="Imagem" />
                                        <input id="post_image_path" type="file" class="mt-1 block w-full"
                                            @change="updateImagePreview($event)" />
                                        <img v-if="imagePreview" :src="imagePreview" class="mt-2" />
                                    </div>
                                </div>
                            </div>
                            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                <PrimaryButton type="button"
                                    class="inline-flex justify-center w-full rounded-md border border-transparent px-4 py-2 bg-green-600 text-base leading-6 font-medium text-white shadow-sm hover:bg-green-500 focus:outline-none focus:border-green-700 focus:shadow-outline-green transition ease-in-out duration-150 sm:text-sm sm:leading-5"
                                    v-show="!editMode" @click="save(form)">
                                    Salvar
                                </PrimaryButton>
                                <PrimaryButton type="button"
                                    class="inline-flex justify-center w-full rounded-md border border-transparent px-4 py-2 bg-green-600 text-base leading-6 font-medium text-white shadow-sm hover:bg-green-500 focus:outline-none focus:border-green-700 focus:shadow-outline-green transition ease-in-out duration-150 sm:text-sm sm:leading-5"
                                    v-show="editMode" @click="update(form)">
                                    Atualizar
                                </PrimaryButton>
                                <SecondaryButton @click="toggleModal" type="button"
                                    class="inline-flex justify-center w-full rounded-md border border-gray-300 px-4 py-2 bg-white text-base leading-6 font-medium text-gray-700 shadow-sm hover:text-gray-500 focus:outline-none focus:border-blue-300 focus:shadow-outline-blue transition ease-in-out duration-150 sm:text-sm sm:leading-5">
                                    Cancelar
                                </SecondaryButton>
                            </div>
                        </form>
                    </Modal>
                </div>
            </div>
        </div>
    </AppLayout>
</template>


