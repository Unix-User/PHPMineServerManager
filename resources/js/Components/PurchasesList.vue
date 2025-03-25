<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const purchases = ref([])
const currentPage = ref(1)
const lastPage = ref(1)
const isLoading = ref(true)

// Função para buscar as transações
const fetchPurchases = async (page = 1) => {
    try {
        isLoading.value = true
        const response = await axios.get('/purchases', { params: { page } })
        purchases.value = response.data.data
        currentPage.value = response.data.current_page
        lastPage.value = response.data.last_page
    } catch (error) {
        console.error('Erro ao buscar transações:', error)
    } finally {
        isLoading.value = false
    }
}

// Função para formatar o valor monetário
const formatCurrency = (value) => {
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL'
    }).format(value / 100)
}

// Função para formatar a data
const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}

// Busca inicial ao montar o componente
onMounted(() => {
    fetchPurchases()
})
</script>

<template>
    <div class="bg-white/90 dark:bg-gray-800/90 backdrop-blur-sm rounded-lg shadow-md p-6 border border-gray-200/50 dark:border-gray-700/50 transition-colors duration-300">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">
                Histórico de Transações
            </h2>
            <button
                @click="fetchPurchases(currentPage)"
                class="p-2 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 transition-colors"
                title="Atualizar lista"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
            </button>
        </div>

        <div v-if="isLoading" class="flex justify-center items-center p-6">
            <svg class="animate-spin h-8 w-8 text-blue-600 dark:text-blue-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>

        <div v-else>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Data
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Email
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Valor
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Status
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Tipo
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        <tr v-for="purchase in purchases" :key="purchase.id" class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                {{ formatDate(purchase.created_at) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-9 00 dark:text-gray-100">
                                {{ purchase.customer_email }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                {{ formatCurrency(purchase.amount) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span :class="{
                                    'bg-green-100 text-green-800': purchase.status === 'approved',
                                    'bg-yellow-100 text-yellow-800': purchase.status === 'pending',
                                    'bg-red-100 text-red-800': purchase.status === 'rejected'
                                }" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                                    {{ purchase.status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                {{ purchase.product_type || 'N/A' }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Paginação -->
            <div class="flex justify-between items-center mt-6">
                <button
                    @click="fetchPurchases(currentPage - 1)"
                    :disabled="currentPage === 1"
                    class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-md hover:bg-gray-200 dark:hover:bg-gray-600 disabled:opacity-50"
                >
                    Anterior
                </button>
                <span class="text-sm text-gray-700 dark:text-gray-300">
                    Página {{ currentPage }} de {{ lastPage }}
                </span>
                <button
                    @click="fetchPurchases(currentPage + 1)"
                    :disabled="currentPage === lastPage"
                    class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-md hover:bg-gray-200 dark:hover:bg-gray-600 disabled:opacity-50"
                >
                    Próxima
                </button>
            </div>
        </div>
    </div>
</template>
