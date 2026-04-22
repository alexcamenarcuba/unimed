<template>
  <AdminLayout>
    <div class="p-4">
      <div class="flex justify-between items-center mb-4">
        <div>
          <h1 class="text-2xl font-bold">Listagem de Testes</h1>
          <p class="text-sm text-gray-500">Endpoint e status do último teste executado.</p>
        </div>
        <Button label="Nova Suite" icon="pi pi-plus" @click="router.visit('/test-suites/create')" />
      </div>
      <DataTable :value="suites" stripedRows responsiveLayout="scroll">
        <Column field="name" header="Cenário Principal">
          <template #body="slotProps">
            <a
              class="text-primary font-medium cursor-pointer hover:underline"
              @click="router.visit(`/test-suites/${slotProps.data.id}`)"
            >{{ slotProps.data.name }}</a>
          </template>
        </Column>
        <Column field="base_url" header="Base URL" />
        <Column field="cases_count" header="Cenários" />
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
    </div>
  </AdminLayout>
</template>

<script setup>
import { router } from '@inertiajs/vue3'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Tag from 'primevue/tag'
import Button from 'primevue/button'
import AdminLayout from "../../../layouts/AdminLayout.vue";

defineProps({
  suites: {
    type: Array,
    default: () => []
  }
})
</script>