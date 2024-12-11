<script setup>
import { ref, onMounted, computed } from "vue";
import ActionSection from "@/Components/ActionSection.vue";
import axios from "axios";

const props = defineProps({
    user: Object,
});

const inventory = ref(new Array(36).fill(null));
const armor = ref({
    chestplate: null,
    helmet: null,
    boots: null,
    leggings: null,
});
const hand = ref({});

onMounted(async () => {
    try {
        const response = await axios.get(
            `/api/get-player-inventory/${props.user.name}`
        );
        if (response.data && response.data.success) {
            const { inventory: newInventory, armor: newArmor, hand: newHand } = response.data.success;
            if (newInventory) inventory.value = newInventory;
            if (newArmor) armor.value = newArmor;
            if (newHand) hand.value = newHand;
            console.log("Inventory Data:", JSON.stringify(response.data, null, 2)); // Print the JSON result in the console
        } else {
            console.error("No success data received:", response.data);
        }
    } catch (error) {
        console.error("Failed to fetch inventory:", error);
    }
});

function resetInventory() {
    inventory.value = new Array(36).fill(null);
    armor.value = {
        chestplate: null,
        helmet: null,
        boots: null,
        leggings: null,
    };
    hand.value = {};
}

const slotIndex = computed(() => {
    return (row, col) => (row - 1) * 9 + (col - 1);
});
</script>

<template>
    <ActionSection>
        <template #title> Inventário do Jogador </template>

        <template #description>
            Visualização do inventário atual do jogador.
        </template>

        <template #content>
            <div class="max-w-xl text-sm text-gray-600 dark:text-gray-400">
                Mostrando itens para {{ props.user.name }}
            </div>
            <table class="table-auto border-collapse border border-gray-600">
                <tbody>
                    <tr>
                        <td v-for="(item, type) in armor.value" :key="type" class="border border-gray-600 p-2">
                            <div v-if="item">
                                🛡️ {{ type }} ({{ item.amount }})
                            </div>
                        </td>
                    </tr>
                    <tr v-for="row in 4" :key="`row-${row}`">
                        <td v-for="col in 9" :key="`col-${col}`" class="border border-gray-600 p-2">
                            <div v-if="inventory.value[slotIndex(row, col)]">
                                📦 {{ inventory.value[slotIndex(row, col)].type }}
                                <span v-if="inventory.value[slotIndex(row, col)].amount">
                                    ({{ inventory.value[slotIndex(row, col)].amount }})
                                </span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="border border-gray-600 p-2">
                            <div v-if="hand.value.type && hand.value.type !== 'AIR'">
                                ✋ {{ hand.value.type }} ({{ hand.value.amount }})
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </template>
    </ActionSection>
</template>
