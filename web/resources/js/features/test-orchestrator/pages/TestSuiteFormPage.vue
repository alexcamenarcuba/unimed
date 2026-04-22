<template>
  <AdminLayout>
    <div class="p-4 max-w-2xl">
      <h1 class="text-2xl font-bold mb-1">Nova Suite</h1>
      <p class="text-sm text-gray-500 mb-6">
        Cadastre a suite para seguir para o cadastro de endpoint.
      </p>

      <div class="flex flex-col gap-4">
        <BaseInputText
          v-model="form.name"
          label="Nome"
          placeholder="Login Contratante"
        />

        <BaseInputText
          v-model="form.base_url"
          label="Base URL"
          placeholder="http://crm"
        />
      </div>

      <div class="flex justify-between w-full mt-6">
        <Button label="Cancelar" severity="secondary" @click="onCancel" />
        <Button label="Criar Suite" icon="pi pi-check" :loading="submitting" @click="submit" />
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import Button from 'primevue/button'
import AdminLayout from '../../../layouts/AdminLayout.vue'

const submitting = ref(false)
const form = ref({
  name: '',
  base_url: '',
})

function submit() {
  if (!form.value.name || !form.value.base_url) return

  submitting.value = true

  router.post('/test-suites', form.value, {
    onFinish: () => {
      submitting.value = false
    },
  })
}

function onCancel() {
  router.visit('/test-suites')
}
</script>
