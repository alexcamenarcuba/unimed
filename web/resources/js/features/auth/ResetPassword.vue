<template>
    <AuthLayout>
        <!-- Página: Token expirado -->
        <template v-if="tokenExpired">
            <div class="text-center space-y-6">
                <div class="flex justify-center mb-4">
                    <svg
                        class="w-16 h-16 text-red-500"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 8v4m0 4v.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                        />
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-red-600">
                    Este link expirou
                </h2>
                <p class="text-gray-600">
                    O link de recuperação de senha tem validade de 30 minutos.
                    Por favor, solicite um novo link.
                </p>
                <form @submit.prevent="requestNewToken" class="space-y-4">
                    <input v-model="newTokenForm.email" type="hidden" />
                    <Button
                        type="submit"
                        label="Solicitar novo link"
                        class="bg-primary w-full"
                        :loading="newTokenForm.processing"
                    />
                    <Button
                        type="button"
                        label="Voltar para login"
                        class="bg-secondary w-full"
                        @click="goToLogin"
                    />
                </form>
            </div>
        </template>

        <!-- Página: Formulário de nova senha -->
        <template v-else>
            <h2 class="text-2xl font-bold mb-6">Redefinir senha</h2>

            <form class="space-y-4" @submit.prevent="submit">
                <BaseInputText v-model="form.email" type="hidden" />
                <BaseInputText v-model="form.token" type="hidden" />
                <BaseInputText
                    v-model="form.password"
                    label="Nova senha"
                    type="password"
                    placeholder="Digite sua nova senha"
                    :error="errors.password || form.errors.password"
                />
                <BaseInputText
                    v-model="form.password_confirmation"
                    label="Confirmar senha"
                    type="password"
                    placeholder="Confirme sua nova senha"
                    :error="
                        errors.password_confirmation ||
                        form.errors.password_confirmation
                    "
                />
                <Button
                    type="submit"
                    label="Alterar senha"
                    class="bg-primary w-full"
                    :loading="form.processing"
                />
                <Button
                    type="button"
                    label="Voltar"
                    class="bg-secondary w-full"
                    @click="goBack"
                />
            </form>
        </template>
    </AuthLayout>
</template>

<script setup>
import { useForm, usePage, router } from "@inertiajs/vue3";
import AuthLayout from "./layouts/AuthLayout.vue";
import Button from "primevue/button";
import BaseInputText from "@/components/ui/BaseInputText.vue";

import { rules } from "@/validations/rules";
import { useValidator } from "@/validations/useValidator";

const page = usePage();

// Props recebidas do backend
const tokenExpired = page.props.tokenExpired || false;
const token = page.props.token || "";
const email = page.props.email || "";

// Formulário para nova senha
const form = useForm({
    email: email,
    token: token,
    password: "",
    password_confirmation: "",
});

const schema = {
    password: [rules.required(), rules.min(6)],
    password_confirmation: [rules.required()],
};

const { errors, validate } = useValidator(schema, form);

// Formulário para solicitar novo token
const newTokenForm = useForm({
    email: email,
});

const submit = () => {
    // Validar que as senhas coincidem
    if (form.password !== form.password_confirmation) {
        errors.password_confirmation = "As senhas não coincidem";
        return;
    }

    const isValid = validate();
    if (!isValid) return;

    form.post("/auth/reset-password");
};

const requestNewToken = () => {
    newTokenForm.post("/auth/esqueci-senha", {
        onSuccess: () => {
            router.visit("/auth/login");
        },
    });
};

const goToLogin = () => {
    router.visit("/auth/login");
};

const goBack = () => {
    router.visit("/auth/login");
};
</script>
