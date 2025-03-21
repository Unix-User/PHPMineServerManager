<script setup>
import { ref, watch } from 'vue';
import { useForm, usePage, router } from '@inertiajs/vue3';
import FormSection from '@/Components/FormSection.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import ActionSection from '@/Components/ActionSection.vue';
import ConfirmsPassword from '@/Components/ConfirmsPassword.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DialogModal from '@/Components/DialogModal.vue';

const passwordInput = ref(null);
const nicknameInput = ref(null);
const user = usePage().props.auth.user;
const confirmingPasswordReset = ref(false);

const nicknameForm = useForm({
    nickname: user.minecraft_id || '',
});

const passwordForm = useForm({
    password: '',
    password_confirmation: '',
});

const hasNickname = ref(!!user.minecraft_id);

const registerNickname = () => {
    nicknameForm.post(route('account.link.register'), {
        errorBag: 'registerMinecraftNickname',
        preserveScroll: true,
        onSuccess: () => {
            nicknameForm.reset('nickname');
            nicknameForm.clearErrors();
            nicknameForm.setError('nickname', 'Mensagem de confirmação enviada para o servidor. Verifique sua caixa de mensagens no jogo.');
            hasNickname.value = true;
            router.reload({ only: ['auth.user'] });
        },
        onError: () => {
            if (nicknameForm.errors.nickname) {
                nicknameInput.value.focus();
            }
        },
    });
};

const confirmPasswordReset = () => {
    confirmingPasswordReset.value = true;
    setTimeout(() => passwordInput.value.focus(), 250);
};

const requestPasswordReset = () => {
    passwordForm.post(route('minecraft-password.request-reset'), {
        errorBag: 'requestMinecraftPasswordReset',
        preserveScroll: true,
        onSuccess: () => {
            passwordForm.reset('password', 'password_confirmation');
            passwordForm.clearErrors();
            confirmingPasswordReset.value = false;
        },
        onError: () => {
            if (passwordForm.errors.password) {
                passwordForm.reset('password', 'password_confirmation');
                passwordInput.value.focus();
            }
        },
    });
};

const closePasswordResetModal = () => {
    confirmingPasswordReset.value = false;
    passwordForm.reset();
};

watch(() => usePage().props.auth.user, (newUser) => {
    nicknameForm.nickname = newUser.minecraft_id || '';
    hasNickname.value = !!newUser.minecraft_id;
}, { deep: true });
</script>

<template>
    <ActionSection>
        <template #title>
            Vinculação de Conta Minecraft
        </template>

        <template #description>
            Vincule sua conta do servidor Minecraft para acessar todos os recursos.
        </template>

        <template #content>
            <div class="max-w-xl text-sm text-gray-600 dark:text-gray-400">
                <p v-if="hasNickname" class="text-lg font-medium text-gray-900 dark:text-gray-100">
                    Sua conta está vinculada ao nickname: 
                    <span class="font-semibold text-indigo-600 dark:text-indigo-400">
                        {{ user.minecraft_id }}
                    </span>
                </p>
                <p v-else class="text-lg font-medium text-gray-900 dark:text-gray-100">
                    Sua conta ainda não está vinculada a um nickname Minecraft.
                </p>
                <p class="mt-2">
                    Ao vincular sua conta, você terá acesso a funcionalidades exclusivas e maior controle sobre sua conta no servidor.
                </p>
            </div>

            <div v-if="!hasNickname" class="mt-5 space-y-4">
                <div>
                    <InputLabel for="nickname" value="Nickname" />
                    <TextInput
                        id="nickname"
                        ref="nicknameInput"
                        v-model="nicknameForm.nickname"
                        type="text"
                        class="mt-1 block w-full"
                        placeholder="Digite seu nickname do Minecraft"
                    />
                    <InputError 
                        :message="nicknameForm.errors.nickname" 
                        class="mt-2"
                        :class="{'text-green-600': nicknameForm.errors.nickname && !nicknameForm.errors.nickname.includes('Erro'), 'text-red-600': nicknameForm.errors.nickname && nicknameForm.errors.nickname.includes('Erro')}"
                    />
                </div>

                <ConfirmsPassword @confirmed="registerNickname">
                    <PrimaryButton 
                        class="mt-5" :class="{ 'opacity-25': nicknameForm.processing }"
                        :disabled="nicknameForm.processing"
                    >
                        Vincular Conta
                    </PrimaryButton>
                </ConfirmsPassword>
            </div>

            <div v-else class="mt-5">
                <PrimaryButton @click="confirmPasswordReset">
                    Alterar Senha
                </PrimaryButton>
            </div>

            <!-- Modal de Confirmação de Alteração de Senha -->
            <DialogModal :show="confirmingPasswordReset" @close="closePasswordResetModal">
                <template #title>
                    Alterar Senha da Conta Minecraft
                </template>

                <template #content>
                    <div class="space-y-4">
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Para alterar a senha, ao menos um jogador deve estar conectado ao servidor.
                        </p>
                        <div>
                            <InputLabel for="password" value="Nova Senha" />
                            <TextInput
                                id="password"
                                ref="passwordInput"
                                v-model="passwordForm.password"
                                type="password"
                                class="mt-1 block w-full"
                                autocomplete="new-password"
                                placeholder="Digite sua nova senha"
                                @keyup.enter="requestPasswordReset"
                            />
                            <InputError 
                                v-if="passwordForm.errors.password"
                                :message="passwordForm.errors.password" 
                                class="mt-2"
                            />
                        </div>

                        <div>
                            <InputLabel for="password_confirmation" value="Confirmar Senha" />
                            <TextInput
                                id="password_confirmation"
                                v-model="passwordForm.password_confirmation"
                                type="password"
                                class="mt-1 block w-full"
                                autocomplete="new-password"
                                placeholder="Confirme sua nova senha"
                                @keyup.enter="requestPasswordReset"
                            />
                            <InputError 
                                v-if="passwordForm.errors.password_confirmation"
                                :message="passwordForm.errors.password_confirmation" 
                                class="mt-2"
                            />
                        </div>
                    </div>
                </template>

                <template #footer>
                    <SecondaryButton @click="closePasswordResetModal">
                        Cancelar
                    </SecondaryButton>

                    <PrimaryButton
                        class="ml-3"
                        :class="{ 'opacity-25': passwordForm.processing }"
                        :disabled="passwordForm.processing"
                        @click="requestPasswordReset"
                    >
                        Alterar Senha
                    </PrimaryButton>
                </template>
            </DialogModal>
        </template>
    </ActionSection>
</template>
