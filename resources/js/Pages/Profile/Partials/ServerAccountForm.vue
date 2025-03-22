<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
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
import DangerButton from '@/Components/DangerButton.vue';

// Refs e computed
const passwordInput = ref(null);
const user = computed(() => usePage().props.auth.user);
const hasNickname = computed(() => !!user.value.minecraft_id);
const confirmingPasswordReset = ref(false);
const confirmingUnlink = ref(false);
const checkNicknameInterval = ref(null);
const isWaitingConfirmation = ref(false);
const localNickname = ref(user.value.minecraft_id || '');

// Forms
const nicknameForm = useForm({
    nickname: localNickname.value,
});

const passwordForm = useForm({
    password: '',
    password_confirmation: '',
});

const unlinkForm = useForm({});

// Verificação do nickname com atualização automática
const checkNicknameStatus = async () => {
    try {
        // Atualiza os dados do usuário diretamente do backend
        await router.reload({ only: ['auth.user'], preserveScroll: true });
        
        // Verifica se o minecraft_id foi preenchido
        if (user.value.minecraft_id) {
            clearInterval(checkNicknameInterval.value);
            checkNicknameInterval.value = null;
            isWaitingConfirmation.value = false;
            localNickname.value = user.value.minecraft_id;
        } else if (isWaitingConfirmation.value) {
            nicknameForm.setError('nickname', 'Aguardando confirmação do nickname...');
        }
    } catch (error) {
        console.error('Erro na verificação:', error);
        nicknameForm.setError('nickname', 'Erro na verificação');
        clearInterval(checkNicknameInterval.value);
    }
};

// Inicia a verificação periódica
const startNicknameCheck = () => {
    if (!checkNicknameInterval.value) {
        checkNicknameStatus();
        checkNicknameInterval.value = setInterval(checkNicknameStatus, 5000); // Verifica a cada 5 segundos
    }
};

// Registro do nickname
const registerNickname = () => {
    isWaitingConfirmation.value = true;
    nicknameForm.transform(data => ({
        ...data,
        nickname: data.nickname.trim()
    })).post(route('account.link.register'), {
        preserveScroll: true,
        onSuccess: () => {
            startNicknameCheck();
            localNickname.value = nicknameForm.nickname;
        },
        onError: () => {
            isWaitingConfirmation.value = false;
        }
    });
};

// Funções de senha e desvinculação
const confirmPasswordReset = () => {
    confirmingPasswordReset.value = true;
    setTimeout(() => passwordInput.value.focus(), 250);
};

const requestPasswordReset = () => {
    passwordForm.post(route('minecraft-password.request-reset'), {
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

const confirmUnlink = () => {
    confirmingUnlink.value = true;
};

const unlinkAccount = () => {
    unlinkForm.post(route('account.unlink'), {
        preserveScroll: true,
        onSuccess: () => {
            confirmingUnlink.value = false;
            router.reload({ only: ['auth.user'] });
        },
        onError: () => {
            confirmingUnlink.value = false;
        },
    });
};

const closePasswordResetModal = () => {
    confirmingPasswordReset.value = false;
    passwordForm.reset();
};

const closeUnlinkModal = () => {
    confirmingUnlink.value = false;
};

// Hooks de ciclo de vida
onMounted(() => {
    if (!hasNickname.value && nicknameForm.nickname) {
        startNicknameCheck();
    }
});

onUnmounted(() => {
    clearInterval(checkNicknameInterval.value);
});

// Watchers
watch(user, (newUser) => {
    if (newUser.minecraft_id && newUser.minecraft_id !== localNickname.value) {
        localNickname.value = newUser.minecraft_id;
        nicknameForm.nickname = newUser.minecraft_id;
    }
});
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
                    Vinculado ao nickname: 
                    <span class="font-semibold text-indigo-600 dark:text-indigo-400">
                        {{ localNickname }}
                    </span>
                </p>
                <p v-else class="text-lg font-medium text-gray-900 dark:text-gray-100">
                    Sua conta ainda não está vinculada a um nickname Minecraft.
                </p>
                <p class="mt-2">
                    Ao vincular sua conta, você terá acesso a funcionalidades exclusivas e maior controle sobre sua
                    conta no servidor.
                </p>
            </div>

            <!-- Componente de vinculação -->
            <div v-if="!hasNickname" class="mt-5 space-y-4">
                <div>
                    <InputLabel for="nickname" value="Nickname" />
                    <TextInput 
                        id="nickname" 
                        v-model="nicknameForm.nickname" 
                        type="text"
                        class="mt-1 block w-full" 
                        placeholder="Digite seu nickname do Minecraft"
                        :disabled="isWaitingConfirmation"
                        :class="{ 
                            'opacity-50 cursor-not-allowed': isWaitingConfirmation,
                            'border-green-500': hasNickname
                        }"
                    />
                    <InputError 
                        :message="nicknameForm.errors.nickname" 
                        class="mt-2 transition-all duration-300"
                        :class="{
                            'animate-pulse': isWaitingConfirmation,
                            'text-green-600': nicknameForm.errors.nickname?.includes('Aguardando'),
                            'text-red-600': nicknameForm.errors.nickname && !nicknameForm.errors.nickname.includes('Aguardando')
                        }" 
                    />
                </div>

                <ConfirmsPassword @confirmed="registerNickname">
                    <PrimaryButton 
                        class="mt-5"
                        :class="{ 'opacity-25': nicknameForm.processing }" 
                        :disabled="nicknameForm.processing"
                    >
                        {{ isWaitingConfirmation ? 'Verificando...' : 'Vincular Conta' }}
                    </PrimaryButton>
                </ConfirmsPassword>
            </div>

            <!-- Componente de gerenciamento após vinculação -->
            <div v-if="hasNickname" class="mt-5 flex gap-3 animate-jump-in">
                <PrimaryButton @click="confirmPasswordReset">
                    Alterar Senha
                </PrimaryButton>
                <DangerButton @click="confirmUnlink">
                    Desvincular Conta
                </DangerButton>
            </div>

            <!-- Modais -->
            <DialogModal :show="confirmingPasswordReset" @close="closePasswordResetModal">
                <template #title>
                    Alterar Senha do Minecraft
                </template>

                <template #content>
                    <div class="mt-4">
                        <InputLabel for="password" value="Nova Senha" />
                        <TextInput id="password" ref="passwordInput" v-model="passwordForm.password" type="password"
                            class="mt-1 block w-full" placeholder="Digite a nova senha" />
                        <InputError :message="passwordForm.errors.password" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <InputLabel for="password_confirmation" value="Confirmar Senha" />
                        <TextInput id="password_confirmation" v-model="passwordForm.password_confirmation"
                            type="password" class="mt-1 block w-full" placeholder="Confirme a nova senha" />
                    </div>
                </template>

                <template #footer>
                    <SecondaryButton @click="closePasswordResetModal">
                        Cancelar
                    </SecondaryButton>

                    <PrimaryButton class="ml-3" :class="{ 'opacity-25': passwordForm.processing }"
                        :disabled="passwordForm.processing" @click="requestPasswordReset">
                        Alterar Senha
                    </PrimaryButton>
                </template>
            </DialogModal>

            <DialogModal :show="confirmingUnlink" @close="closeUnlinkModal">
                <template #title>
                    Desvincular Conta Minecraft
                </template>

                <template #content>
                    <p class="text-gray-600 dark:text-gray-400">
                        Tem certeza que deseja desvincular sua conta Minecraft? Esta ação não pode ser desfeita.
                    </p>
                    <InputError v-if="nicknameForm.errors.nickname" :message="nicknameForm.errors.nickname" class="mt-2" />
                </template>

                <template #footer>
                    <SecondaryButton @click="closeUnlinkModal">
                        Cancelar
                    </SecondaryButton>

                    <DangerButton class="ml-3" :class="{ 'opacity-25': unlinkForm.processing }"
                        :disabled="unlinkForm.processing" @click="unlinkAccount">
                        Desvincular Conta
                    </DangerButton>
                </template>
            </DialogModal>
        </template>
    </ActionSection>
</template>
