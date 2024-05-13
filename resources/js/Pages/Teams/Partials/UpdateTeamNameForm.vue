<script setup>
import { useForm } from '@inertiajs/vue3';
import ActionMessage from '@/Components/ActionMessage.vue';
import FormSection from '@/Components/FormSection.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    team: Object,
    permissions: Object,
});

const form = useForm({
    name: props.team.name,
});

const updateTeamName = () => {
    form.put(route('teams.update', props.team), {
        errorBag: 'updateTeamName',
        preserveScroll: true,
    });
};
</script>

<template>
    <FormSection @submitted="updateTeamName" class="dark:bg-gray-800 bg-white">
        <template #title>
            Atualizar Nome do Time
        </template>

        <template #description>
            Altere o nome do seu time e confira os detalhes do proprietário abaixo.
        </template>

        <template #form>
            <!-- Informações do Proprietário do Time -->
            <div class="col-span-6">
                <InputLabel value="Proprietário do Time" />

                <div class="flex items-center mt-2">
                    <img class="w-12 h-12 rounded-full object-cover" :src="team.owner.profile_photo_url" :alt="team.owner.name">

                    <div class="ml-4 leading-tight">
                        <div :class="{'text-gray-900 dark:text-gray-100': $page.props.jetstream.usingDarkMode, 'text-gray-700': !$page.props.jetstream.usingDarkMode}">{{ team.owner.name }}</div>
                        <div :class="{'text-gray-600 dark:text-gray-300': $page.props.jetstream.usingDarkMode, 'text-gray-500': !$page.props.jetstream.usingDarkMode}" class="text-sm">
                            {{ team.owner.email }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Nome do Time -->
            <div class="col-span-6 sm:col-span-4">
                <InputLabel for="name" value="Nome do Time" />

                <TextInput
                    id="name"
                    v-model="form.name"
                    type="text"
                    class="mt-1 block w-full"
                    :disabled="! permissions.canUpdateTeam"
                />

                <InputError :message="form.errors.name" class="mt-2" />
            </div>
        </template>

        <template v-if="permissions.canUpdateTeam" #actions>
            <ActionMessage :on="form.recentlySuccessful" class="mr-3">
                Alterações Salvas.
            </ActionMessage>

            <PrimaryButton :class="{ 'opacity-25': form.processing, 'dark:bg-blue-500 bg-blue-600': !form.processing }" :disabled="form.processing">
                Salvar Alterações
            </PrimaryButton>
        </template>
    </FormSection>
</template>
