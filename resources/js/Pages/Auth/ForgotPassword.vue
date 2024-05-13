<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticationCard from '@/Components/AuthenticationCard.vue';
import AuthenticationCardLogo from '@/Components/AuthenticationCardLogo.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

defineProps({
    status: String,
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <Head title="Esqueceu a Senha" />

    <AuthenticationCard>
        <template #logo>
            <AuthenticationCardLogo />
        </template>

        <div class="mb-4 text-sm" :class="{'text-gray-600': $theme === 'light', 'text-gray-300': $theme === 'dark'}">
            Esqueceu sua senha? Não se preocupe. Informe seu endereço de e-mail e enviaremos um link para você redefinir sua senha.
        </div>

        <div v-if="status" class="mb-4 font-medium text-sm" :class="{'text-green-600': $theme === 'light', 'text-green-300': $theme === 'dark'}">
            {{ status }}
        </div>

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="email" value="E-mail" />
                <TextInput
                    id="email"
                    v-model="form.email"
                    type="email"
                    class="mt-1 block w-full"
                    required
                    autofocus
                    autocomplete="username"
                />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="flex items-center justify-end mt-4">
                <PrimaryButton :class="{ 'opacity-25': form.processing, 'text-white': $theme === 'dark', 'text-black': $theme === 'light' }" :disabled="form.processing">
                    Enviar Link de Redefinição de Senha
                </PrimaryButton>
            </div>
        </form>
    </AuthenticationCard>
</template>
