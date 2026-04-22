<template>
  <AdminLayout>
    <div class="p-4">
      <div class="flex justify-between items-center mb-4">
        <div>
          <h1 class="text-2xl font-bold">Listagem de Testes</h1>
          <p class="text-sm text-gray-500">Endpoint e status do último teste executado.</p>
        </div>
        <div class="flex gap-2">
          <Button
            label="Histórico"
            icon="pi pi-chart-line"
            severity="secondary"
            @click="router.visit('/test-runs')"
          />
          <Button label="Nova Suite" icon="pi pi-plus" @click="router.visit('/test-suites/create')" />
        </div>
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
        <Column field="environments_count" header="Ambientes" />
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

        <Column header="Ações">
          <template #body="slotProps">
            <div class="flex items-center gap-1">
              <Button
                icon="pi pi-pencil"
                text
                rounded
                severity="secondary"
                @click="router.visit(`/test-suites/${slotProps.data.id}/edit`)"
              />
              <Button
                icon="pi pi-arrow-right"
                text
                rounded
                @click="router.visit(`/test-suites/${slotProps.data.id}`)"
              />
            </div>
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