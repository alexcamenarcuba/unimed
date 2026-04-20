<template>
    <AuthLayout>
        <h2 class="text-2xl font-bold mb-6">Recuperar senha</h2>

        <form class="space-y-4" @submit.prevent="submit">
            <BaseInputText
                v-model="form.email"
                label="Email"
                placeholder="Digite seu email"
                :error="errors.email || form.errors.email"
            />
            <Button
                type="submit"
                label="Enviar código"
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
import { useForm, router } from "@inertiajs/vue3";
import AuthLayout from "./layouts/AuthLayout.vue";
import Button from "primevue/button";

import { rules } from "@/validations/rules";
import { useValidator } from "@/validations/useValidator";

const form = useForm({
    email: "",
});

const schema = {
    email: [rules.required(), rules.email()],
};

const { errors, validate } = useValidator(schema, form);

const submit = () => {
    const isValid = validate();
    if (!isValid) return;

    form.post("/auth/esqueci-senha");
};

const goBack = () => {
    router.visit("/auth/login");
};
</script>
