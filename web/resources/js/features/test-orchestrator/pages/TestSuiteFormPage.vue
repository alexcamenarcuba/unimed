<template>
  <AdminLayout>
    <div class="p-4 max-w-2xl">
      <h1 class="text-2xl font-bold mb-1">{{ suite ? 'Editar Suite' : 'Nova Suite' }}</h1>
      <p class="text-sm text-gray-500 mb-6">
        {{ suite ? 'Atualize os dados da suite.' : 'Cadastre a suite para seguir para o cadastro de ambientes.' }}
      </p>

      <div class="flex flex-col gap-4">
        <BaseInputText
          v-model="form.name"
          label="Nome"
          placeholder="Login Contratante"
        />

        <BaseInputText
          v-if="suite"
          v-model="form.base_url"
          label="Base URL"
          placeholder="http://crm"
        />

        <template v-else>
          <BaseInputText
            v-model="form.base_path"
            label="Caminho base comum"
            placeholder="/api/v1/contratante"
          />

          <div class="border rounded-md p-4 flex flex-col gap-3">
            <div class="flex items-center justify-between">
              <p class="text-sm font-medium">Ambientes (N URLs)</p>
              <Button
                label="Adicionar"
                icon="pi pi-plus"
                size="small"
                severity="secondary"
                @click="addEnvironment"
              />
            </div>

            <div
              v-for="(environment, index) in form.environments"
              :key="index"
              class="grid grid-cols-1 md:grid-cols-12 gap-2 items-center"
            >
              <div class="md:col-span-4">
                <BaseInputText
                  v-model="environment.name"
                  label="Nome"
                  placeholder="Filial SP"
                />
              </div>
              <div class="md:col-span-7">
                <BaseInputText
                  v-model="environment.url"
                  label="URL host"
                  placeholder="https://crm-sp.empresa.com"
                />
              </div>
              <div class="md:col-span-1 pt-5">
                <Button
                  icon="pi pi-trash"
                  text
                  severity="danger"
                  :disabled="form.environments.length === 1"
                  @click="removeEnvironment(index)"
                />
              </div>
            </div>
          </div>
        </template>
      </div>

      <div class="flex justify-between w-full mt-6">
        <Button label="Cancelar" severity="secondary" @click="onCancel" />
        <Button :label="suite ? 'Salvar Suite' : 'Criar Suite'" icon="pi pi-check" :loading="submitting" @click="submit" />
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import Button from 'primevue/button'
import AdminLayout from '../../../layouts/AdminLayout.vue'

const props = defineProps({
  suite: {
    type: Object,
    default: null,
  },
})

const submitting = ref(false)
const form = ref({
  name: props.suite?.name ?? '',
  base_url: props.suite?.base_url ?? '',
  base_path: '/api/v1/contratante',
  environments: [
    {
      name: 'Principal',
      url: '',
    },
  ],
})

function submit() {
  if (!form.value.name) return

  if (props.suite && !form.value.base_url) return

  if (!props.suite && form.value.environments.some((item) => !item.name || !item.url)) return

  submitting.value = true

  if (props.suite) {
    router.put(`/test-suites/${props.suite.id}`, form.value, {
      onFinish: () => {
        submitting.value = false
      },
    })
    return
  }

  router.post('/test-suites', form.value, {
    onFinish: () => {
      submitting.value = false
    },
  })
}

function addEnvironment() {
  form.value.environments.push({
    name: '',
    url: '',
  })
}

function removeEnvironment(index) {
  if (form.value.environments.length === 1) return

  form.value.environments.splice(index, 1)
}

function onCancel() {
  if (props.suite) {
    router.visit(`/test-suites/${props.suite.id}`)
    return
  }

  router.visit('/test-suites')
}
</script>
