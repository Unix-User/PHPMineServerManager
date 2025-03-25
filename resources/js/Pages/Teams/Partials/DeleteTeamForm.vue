<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import ActionSection from '@/Components/ActionSection.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import DangerButton from '@/Components/DangerButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    team: Object,
});

const confirmingTeamDeletion = ref(false);
const form = useForm({});

const confirmTeamDeletion = () => {
    confirmingTeamDeletion.value = true;
};

const deleteTeam = () => {
    form.delete(route('teams.destroy', props.team), {
        errorBag: 'deleteTeam',
    });
};
</script>

<template>
    <ActionSection>
        <template #title>
            Excluir Equipe
        </template>

        <template #description>
            Exclusão permanente da equipe.
        </template>

        <template #content>
            <div class="max-w-xl text-sm text-gray-600 dark:text-gray-300">
                Ao excluir uma equipe, todos os seus recursos e dados serão permanentemente excluídos. Antes de proceder, certifique-se de baixar todos os dados ou informações da equipe que deseja preservar.
            </div>

            <div class="mt-5">
                <DangerButton @click="confirmTeamDeletion">
                    Excluir Equipe
                </DangerButton>
            </div>

            <!-- Modal de Confirmação de Exclusão da Equipe -->
            <ConfirmationModal :show="confirmingTeamDeletion" @close="confirmingTeamDeletion = false">
                <template #title>
                    Confirmar Exclusão da Equipe
                </template>

                <template #content>
                    Tem certeza de que deseja excluir esta equipe? Uma vez excluída, todos os recursos e dados associados serão permanentemente perdidos.
                </template>

                <template #footer>
                    <SecondaryButton @click="confirmingTeamDeletion = false">
                        Cancelar
                    </SecondaryButton>

                    <DangerButton
                        class="ml-3"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                        @click="deleteTeam"
                    >
                        Confirmar Exclusão
                    </DangerButton>
                </template>
            </ConfirmationModal>
        </template>
    </ActionSection>
</template>
