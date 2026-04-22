<template>
  <DataTable :value="cases" stripedRows>
    <Column field="name" header="Nome" />
    <Column field="expected_status" header="HttpCode Esperado" />
    <Column field="status_received" header="HttpCode Retornado" />
    <Column field="endpoint" header="Endpoint" />
    <Column header="Request">
      <template #body="slot">
        <Button
          icon="pi pi-eye"
          text
          @click="showJson(slot.data.request_payload)"
        />
      </template>
    </Column>
    <Column header="Response">
      <template #body="slot">
        <Button
          icon="pi pi-eye"
          text
          @click="showJson(slot.data.response_body)"
        />
      </template>
    </Column>
    <Column header="Status">
      <template #body="slot">
        <TestResultBadge :status="slot.data.last_result" />
      </template>
    </Column>
    <Column header="Ações">
      <template #body="slot">
        <div class="flex gap-2">
          <Button icon="pi pi-pencil" text @click="emit('edit', slot.data)" />
          <Button icon="pi pi-trash" severity="danger" text />
        </div>
      </template>
    </Column>
  </DataTable>

  <!-- MODAL JSON -->
  <Dialog v-model:visible="jsonVisible" header="JSON">
    <JsonViewer :data="selectedJson" />
  </Dialog>
</template>

<script setup>
import { ref } from 'vue'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Button from 'primevue/button'
import Dialog from 'primevue/dialog'
import TestResultBadge from './TestResultBadge.vue'
import JsonViewer from './JsonViewer.vue'

defineProps({
  cases: Array
})

const emit = defineEmits(['edit'])

const jsonVisible = ref(false)
const selectedJson = ref(null)

function showJson(json) {
  selectedJson.value = json
  jsonVisible.value = true
}
</script>