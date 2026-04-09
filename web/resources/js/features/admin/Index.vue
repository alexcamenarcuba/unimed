<template>
    <div>
        <h1>{{ mensagem }}</h1>
        <p>Laravel + Inertia.js + Vue 3 funcionando! 🚀</p>
        <h1>Teste PrimeVue</h1>

        <InputText v-model="nome" placeholder="Digite seu nome" class="mb-4" />

        <Button
            label="Enviar"
            icon="pi pi-check"
            @click="() => alert('Olá, ' + nome)"
        />
    </div>
</template>

<script setup>
import Button from "primevue/button";
import InputText from "primevue/inputtext";
import { ref } from "vue";

const nome = ref("");
defineProps({
    mensagem: String,
});
</script>
