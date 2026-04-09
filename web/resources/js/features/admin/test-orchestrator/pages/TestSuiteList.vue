<template>
  <div class="p-4">
    <div class="flex justify-between items-center mb-4">
      <div>
        <h1 class="text-2xl font-bold">Listagem de Testes</h1>
        <p class="text-sm text-gray-500">Endpoint e status do ultimo teste executado.</p>
      </div>
      <Button label="Nova Suite" icon="pi pi-plus" @click="showModal = true" />
    </div>

    <DataTable :value="suites" stripedRows responsiveLayout="scroll">
      <Column field="name" header="Suite">
        <template #body="slotProps">
          <a
            class="text-primary font-medium cursor-pointer hover:underline"
            @click="router.visit(`/test-suites/${slotProps.data.id}`)"
          >{{ slotProps.data.name }}</a>
        </template>
      </Column>

      <Column field="base_url" header="Base URL" />

      <Column field="cases_count" header="Cenarios" />

      <Column header="Ultimo Resultado">
        <template #body="slotProps">
          <Tag
            v-if="slotProps.data.last_status !== null"
            :severity="slotProps.data.last_status ? 'success' : 'danger'"
            :value="slotProps.data.last_status ? 'PASSOU' : 'FALHOU'"
          />
          <Tag v-else severity="secondary" value="SEM EXECUCAO" />
        </template>
      </Column>

      <Column header="">
        <template #body="slotProps">
          <Button
            icon="pi pi-arrow-right"
            text
            @click="router.visit(`/test-suites/${slotProps.data.id}`)"
          />
        </template>
      </Column>
    </DataTable>

    <!-- MODAL NOVA SUITE -->
    <Dialog v-model:visible="showModal" modal header="Nova Suite" :style="{ width: '480px' }">
      <div class="flex flex-col gap-4 pt-2">
        <div>
          <label class="block mb-1 font-medium">Nome</label>
          <InputText v-model="form.name" class="w-full" placeholder="Login Contratante" />
        </div>
        <div>
          <label class="block mb-1 font-medium">Base URL</label>
          <InputText v-model="form.base_url" class="w-full" placeholder="http://crm" />
        </div>
      </div>

      <template #footer>
        <div class="flex justify-between w-full">
          <Button label="Cancelar" severity="secondary" @click="showModal = false" />
          <Button label="Criar Suite" icon="pi pi-check" :loading="submitting" @click="submit" />
        </div>
      </template>
    </Dialog>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Tag from 'primevue/tag'
import Button from 'primevue/button'
import Dialog from 'primevue/dialog'
import InputText from 'primevue/inputtext'

defineProps({
  suites: {
    type: Array,
    default: () => []
  }
})

const showModal = ref(false)
const submitting = ref(false)
const form = ref({ name: '', base_url: '' })

function submit() {
  if (!form.value.name || !form.value.base_url) return

  submitting.value = true

  router.post('/test-suites', form.value, {
    onFinish: () => { submitting.value = false },
    onError: () => { submitting.value = false },
  })
}
</script>