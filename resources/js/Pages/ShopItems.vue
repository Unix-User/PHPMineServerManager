<script setup>
import { ref, reactive, toRefs, onMounted } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const page = usePage();
const editMode = ref(false);
const isOpen = ref(false);
const form = reactive({
    name: null,
    description: null,
    price: null, // Preço em centavos
    item_photo_path: '/storage/shop-item-photos/default.png',
    link: null,
});
const imageFile = ref(null);
const imagePreview = ref(null);
const shopItems = ref(page.props.shopItems || []);
const errors = ref({});
const searchQuery = ref('');
const sortOption = ref('default');

const resetForm = () => {
    for (let key in form) {
        if (key === 'item_photo_path') {
            form[key] = '/storage/shop-item-photos/default.png';
        } else {
            form[key] = null;
        }
    }
    imageFile.value = null;
    imagePreview.value = null;
    errors.value = {};
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
            form.item_photo_path = '/storage/shop-item-photos/default.png';
        }
        toggleModal();
    }
};

const save = () => {
    const formData = new FormData();
    formData.append('name', form.name);
    formData.append('description', form.description);
    formData.append('price', Math.round(form.price * 100)); // Converte para centavos
    formData.append('link', form.link);
    
    if (imageFile.value) {
        formData.append('item_photo_path', imageFile.value);
    }
    
    submitForm(formData, 'post', '/shop/items');
};

const update = () => {
    const formData = new FormData();
    formData.append('_method', 'PUT');
    formData.append('name', form.name);
    formData.append('description', form.description);
    formData.append('price', Math.round(form.price * 100)); // Converte para centavos
    formData.append('link', form.link);
    
    if (imageFile.value) {
        formData.append('item_photo_path', imageFile.value);
    }
    
    submitForm(formData, 'post', `/shop/items/${form.id}`);
};

const submitForm = (formData, method, url) => {
    router.post(url, formData, {
        forceFormData: true,
        onSuccess: () => {
            resetForm();
            if (isOpen.value) {
                toggleModal();
            }
            router.reload();
        },
        onError: (err) => {
            errors.value = err;
        }
    });
};

const deleteRow = (data) => {
    if (data && confirm('Tem certeza de que deseja remover?')) {
        router.delete(`/shop/items/${data.id}`, {
            onSuccess: () => {
                router.reload();
            }
        });
    }
};

const updateImagePreview = (event) => {
    const file = event.target.files[0];
    if (file) {
        imageFile.value = file;
        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

const selectNewImage = () => {
    imagePreview.value = null;
    document.getElementById('image').click();
};

const deleteImage = () => {
    form.item_photo_path = '/storage/shop-item-photos/default.png';
    imageFile.value = null;
    imagePreview.value = null;
    document.getElementById('image').value = null;
};

const filteredItems = () => {
    let items = [...shopItems.value];
    
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        items = items.filter(item => 
            item.name.toLowerCase().includes(query) || 
            item.description.toLowerCase().includes(query)
        );
    }
    
    switch(sortOption.value) {
        case 'price-low':
            items.sort((a, b) => parseFloat(a.price) - parseFloat(b.price));
            break;
        case 'price-high':
            items.sort((a, b) => parseFloat(b.price) - parseFloat(a.price));
            break;
        case 'name-asc':
            items.sort((a, b) => a.name.localeCompare(b.name));
            break;
        case 'name-desc':
            items.sort((a, b) => b.name.localeCompare(a.name));
            break;
    }
    
    return items;
};

const buyItem = (item) => {
    if (item) {
        axios.post(`/shop/items/${item.id}/buy`)
            .then(response => {
                if (response.data.payment_link) {
                    window.open(response.data.payment_link, '_blank', 'noopener,noreferrer');
                } else {
                    console.log(response);
                    alert('Erro ao gerar link de pagamento.');
                }
            })
            .catch(error => {
                // Exibe mensagens de erro específicas do backend
                if (error.response && error.response.data && error.response.data.message) {
                    alert(error.response.data.message);
                } else if (error.response && error.response.data && error.response.data.error) {
                    alert(error.response.data.error);
                } else {
                    alert('Erro ao processar compra. Tente novamente mais tarde.');
                }
            });
    }
};
</script>
<template>
    <AppLayout title="Shop Items">
        <template #header>
            <div class="flex flex-col sm:flex-row justify-between w-full gap-4">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Loja de Itens
                </h2>
                <div class="flex sm:flex-row gap-2 w-full sm:w-auto h-6 items-center">
                    <div class="relative flex-grow">
                        <input type="text" v-model="searchQuery" placeholder="Buscar itens..." 
                            class="w-full h-10 rounded-lg bg-white/80 dark:bg-gray-700/80 border border-gray-300 dark:border-gray-600 
                            focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all px-3" 
                            aria-label="Buscar itens" />
                        <span class="absolute right-3 top 2.5 text-gray-400">
                            <i class="fas fa-search"></i>
                        </span>
                    </div>
                    <select v-model="sortOption" 
                        class="h-10 rounded-lg bg-white/80 dark:bg-gray-700/80 border border-gray-300 dark:border-gray-600 
                        text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all px-3"
                        aria-label="Ordenar por">
                        <option value="default">Ordenar por</option>
                        <option value="price-low">Menor preço</option>
                        <option value="price-high">Maior preço</option>
                        <option value="name-asc">Nome (A-Z)</option>
                        <option value="name-desc">Nome (Z-A)</option>
                    </select>
                    <div v-if="page.props.user.roles.includes('admin')">
                        <PrimaryButton @click="toggleModal" class="h-10 w-full sm:w-auto hover:scale-105 transition-transform rounded-lg">
                            <i class="fas fa-plus-circle mr-2"></i> Novo Item
                        </PrimaryButton>
                    </div>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white/90 dark:bg-gray-800/90 backdrop-blur-sm overflow-hidden shadow-xl sm:rounded-lg px-12 py-12 transition-all duration-300 hover:shadow-2xl">
                    <div v-if="filteredItems().length === 0" class="text-center py-12">
                        <div class="text-5xl mb-4 text-gray-400 dark:text-gray-500">
                            <i class="fas fa-shopping-basket"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-700 dark:text-gray-300">Nenhum item encontrado</h3>
                        <p class="text-gray-500 dark:text-gray-400 mt-2">Tente ajustar sua busca or filtros</p>
                    </div>
                    
                    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div v-for="(row, index) in filteredItems()" :key="index"
                            class="rounded-lg overflow-hidden shadow-lg bg-white/95 dark:bg-gray-700/95 backdrop-blur-sm transition-all duration-300 hover:scale-105 hover:shadow-xl flex flex-col relative">
                            <div class="absolute top-0 right-0 z-10 m-3">
                                <div class="bg-teal-600 text-white font-bold text-xl px-4 py-2 rounded-lg shadow-lg transform rotate-3 hover:rotate-0 transition-all duration-300 animate-pulse">
                                    R$ {{ (row.price / 100).toFixed(2).replace('.', ',') }}
                                </div>
                            </div>
                            
                            <div class="relative overflow-hidden group">
                                <img class="w-full h-64 object-cover transition-transform duration-500 group-hover:scale-110" 
                                    :src="row.image || '/storage/shop-item-photos/default.png'"
                                    alt="Product image">
                                <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <div v-if="page.props.user.roles.includes('admin')" 
                                    class="absolute bottom-0 left-0 right-0 p-4 flex justify-center gap-2 transform translate-y-full group-hover:translate-y-0 transition-transform duration-300">
                                    <PrimaryButton @click="edit(row)" class="hover:scale-105 transition-transform rounded-lg">
                                        <i class="fas fa-edit mr-1"></i> Editar
                                    </PrimaryButton>
                                    <SecondaryButton @click="deleteRow(row)" class="hover:scale-105 transition-transform rounded-lg">
                                        <i class="fas fa-trash-alt mr-1"></i> Excluir
                                    </SecondaryButton>
                                </div>
                            </div>
                            
                            <div class="p-6 flex-grow flex flex-col">
                                <div class="font-bold text-xl mb-2 text-center text-gray-800 dark:text-gray-200 transition-colors duration-300">{{ row.name }}</div>
                                <p class="text-gray-700 dark:text-gray-300 text-base mt-2 mb-4 flex-grow transition-colors duration-300">{{ row.description }}</p>
                                
                                <div class="mt-auto flex justify-center">
                                    <button @click.prevent="buyItem(row)" class="btn btn-primary btn-lg w-full flex items-center justify-center rounded-lg">
                                        <i class="fas fa-shopping-cart mr-2"></i> Comprar Agora
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Promotional ribbon -->
                            <div class="absolute top-0 left-0 bg-red-600 text-white px-8 py-1 transform -rotate-45 translate-x-[-24%] translate-y-[40%] shadow-md">
                                Oferta
                            </div>
                        </div>
                    </div>
                    
                    <Modal :show="isOpen" @close="toggleModal">
                        <form enctype="multipart/form-data">
                            <div class="bg-white/95 dark:bg-gray-700/95 backdrop-blur-sm px-4 pt-5 pb-4 sm:p-6 sm:pb-4 rounded-lg">
                                <div class="space-y-6">
                                    <div>
                                        <InputLabel for="name" value="Nome" class="text-gray-800 dark:text-gray-200" />
                                        <TextInput id="name" v-model="form.name" type="text" class="mt-1 block w-full bg-white/50 dark:bg-gray-600/50"
                                            autocomplete="name" />
                                        <p class="text-red-500 text-xs italic mt-1" v-if="errors.name">{{ errors.name }}</p>
                                    </div>
                                    <div>
                                        <InputLabel for="description" value="Descrição" class="text-gray-800 dark:text-gray-200" />
                                        <TextInput id="description" v-model="form.description" type="text"
                                            class="mt-1 block w-full bg-white/50 dark:bg-gray-600/50" autocomplete="description" />
                                        <p class="text-red-500 text-xs italic mt-1" v-if="errors.description">{{ errors.description }}</p>
                                    </div>
                                    <div>
                                        <InputLabel for="price" value="Preço" class="text-gray-800 dark:text-gray-200" />
                                        <TextInput id="price" v-model="form.price" type="number" class="mt-1 block w-full bg-white/50 dark:bg-gray-600/50"
                                            autocomplete="price" />
                                        <p class="text-red-500 text-xs italic mt-1" v-if="errors.price">{{ errors.price }}</p>
                                    </div>
                                    <div>
                                        <InputLabel for="link" value="Link de Pagamento" class="text-gray-800 dark:text-gray-200" />
                                        <TextInput id="link" v-model="form.link" type="url" class="mt-1 block w-full bg-white/50 dark:bg-gray-600/50"
                                            placeholder="https://exemplo.com/pagamento" />
                                        <p class="text-red-500 text-xs italic mt-1" v-if="errors.link">{{ errors.link }}</p>
                                    </div>
                                    <div>
                                        <InputLabel for="image" value="Imagem" class="text-gray-800 dark:text-gray-200" />
                                        <input id="image" type="file" class="mt-1 block w-full text-gray-800 dark:text-gray-200 file:mr-4 file:py-2 file:px-4
                                            file:rounded-full file:border-0 file:text-sm file:font-semibold
                                            file:bg-gray-100/50 file:text-gray-700 hover:file:bg-gray-200/50
                                            dark:file:bg-gray-600/50 dark:file:text-gray-200 dark:hover:file:bg-gray-500/50"
                                            @change="updateImagePreview($event)" />
                                        <div class="mt-4 flex items-center" v-if="imagePreview || form.item_photo_path">
                                            <img v-if="imagePreview" :src="imagePreview" class="h-32 w-32 object-cover rounded-lg" />
                                            <img v-else-if="form.item_photo_path" :src="form.image || form.item_photo_path" class="h-32 w-32 object-cover rounded-lg" />
                                            <button type="button" @click="deleteImage" class="ml-4 text-red-500 hover:text-red-600 dark:hover:text-red-400 transition-colors">
                                                <i class="fas fa-trash-alt mr-1"></i> Remover Imagem
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-gray-100/80 dark:bg-gray-600/80 backdrop-blur-sm px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-3 rounded-b-lg">
                                <PrimaryButton type="button"
                                    class="w-full sm:w-auto justify-center rounded-md px-4 py-2 bg-green-600 hover:bg-green-500 text-white shadow-sm transition-all duration-300 hover:scale-105"
                                    v-show="!editMode" @click="save">
                                    <i class="fas fa-save mr-2"></i> Salvar
                                </PrimaryButton>
                                <PrimaryButton type="button"
                                    class="w-full sm:w-auto justify-center rounded-md px-4 py-2 bg-green-600 hover:bg-green-500 text-white shadow-sm transition-all duration-300 hover:scale-105"
                                    v-show="editMode" @click="update">
                                    <i class="fas fa-check mr-2"></i> Atualizar
                                </PrimaryButton>
                                <SecondaryButton @click="toggleModal" type="button"
                                    class="w-full sm:w-auto justify-center rounded-md px-4 py-2 bg-white/50 dark:bg-gray-600/50 text-gray-700 dark:text-gray-200 shadow-sm hover:bg-gray-200/50 dark:hover:bg-gray-500/50 transition-all duration-300 hover:scale-105">
                                    <i class="fas fa-times mr-2"></i> Cancelar
                                </SecondaryButton>
                            </div>
                        </form>
                    </Modal>
                </div>
            </div>
        </div>
    </AppLayout>
</template>


<style>
.btn-primary {
    @apply bg-blue-500 hover:bg-blue-600 text-white dark:text-gray-900 dark:bg-orange-500 hover:dark:bg-orange-600;
}

.btn-secondary {
    @apply bg-gray-500 hover:bg-gray-600 text-white dark:text-gray-900 dark:bg-gray-700 hover:dark:bg-gray-800;
}

.btn-lg {
    @apply py-3 px-6 text-lg;
}
</style>
