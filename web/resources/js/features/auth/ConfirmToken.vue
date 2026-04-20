<template>
    <AuthLayout>
        <h2 class="text-2xl font-bold mb-6">Confirmar código</h2>
        <p class="text-gray-600 mb-6">
            Enviamos um código de 6 dígitos para
            <strong>{{ form.email }}</strong>
        </p>

        <form class="space-y-4" @submit.prevent="submit">
            <BaseInputText
                v-model="form.email"
                type="hidden"
                placeholder="Email"
            />
            <BaseInputText
                v-model="form.token"
                label="Código de verificação"
                placeholder="000000"
                maxlength="6"
                @input="form.token = form.token.replace(/\D/g, '')"
                :error="errors.token || form.errors.token"
            />
            <Button
                type="submit"
                label="Confirmar"
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
    </AuthLayout>
</template>

<script setup>
import { useForm, usePage, router } from "@inertiajs/vue3";
import AuthLayout from "./layouts/AuthLayout.vue";
import Button from "primevue/button";

import { rules } from "@/validations/rules";
import { useValidator } from "@/validations/useValidator";

const page = usePage();
const emailFromFlash = page.props.email || "";

const form = useForm({
    email: emailFromFlash,
    token: "",
});

const schema = {
    email: [rules.required(), rules.email()],
    token: [rules.required()],
};

const { errors, validate } = useValidator(schema, form);

const submit = () => {
    const isValid = validate();
    if (!isValid) return;

    form.post("/auth/confirmar-token");
};

const goBack = () => {
    router.visit("/auth/login");
};
</script>
