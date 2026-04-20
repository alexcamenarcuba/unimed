<template>
    <Toast />
    <AuthLayout>
        <h2 class="text-2xl font-bold mb-6">Login</h2>
        <form class="space-y-4" @submit.prevent="submit">
            <BaseInputText
                v-model="form.email"
                label="Email ou Usuário"
                placeholder="Digite seu nome"
                :error="errors.email || form.errors.email"
            />
            <BaseInputText
                v-model="form.password"
                label="Senha"
                type="password"
                placeholder="Digite sua senha"
                :error="errors.password || form.errors.password"
            />

            <Button
                type="submit"
                label="Entrar"
                class="bg-primary w-full"
                :loading="form.processing"
            />
        </form>
        <div class="text-left">
            <Link
                href="/auth/esqueci-senha"
                class="text-sm text-primary hover:underline"
            >
                Esqueci minha senha
            </Link>
        </div>
    </AuthLayout>
</template>

<script setup>
import AuthLayout from "./layouts/AuthLayout.vue";
import Button from "primevue/button";
import { Link, useForm } from "@inertiajs/vue3";

import { rules } from "@/validations/rules";
import { useValidator } from "@/validations/useValidator";

const form = useForm({
    email: "alex.camenar@gmail.com",
    password: "Unimed@975",
});

const schema = {
    email: [rules.required()],
    password: [rules.required(), rules.min(6)],
};

const { errors, validate } = useValidator(schema, form);

const submit = () => {
    const isValid = validate();
    if (!isValid) return;

    form.post("/auth/login");
};
</script>
