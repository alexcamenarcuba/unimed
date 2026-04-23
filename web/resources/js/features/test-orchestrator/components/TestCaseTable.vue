<template>
  <DataTable :value="cases" stripedRows>
    <Column field="name" header="Nome" />
    <Column field="expected_status" header="HttpCode Esperado" />
    <Column header="Endpoint">
      <template #body="slot">
        <div class="flex items-center gap-2">
          <span>{{ slot.data.endpoint }}</span>
          <Button
            icon="pi pi-pencil"
            text
            size="small"
            aria-label="Editar endpoint"
            @click="emit('edit-endpoint', slot.data.endpoint_id)"
          />
        </div>
      </template>
    </Column>
    <Column header="Resultado por Ambiente">
      <template #body="slot">
        <div class="flex flex-col gap-2 min-w-72">
          <div
            v-for="environmentResult in getEnvironmentResults(slot.data)"
            :key="`${slot.data.id}-${environmentResult.environment_id}`"
            class="border rounded-md p-2"
          >
            <div class="flex items-center justify-between gap-2">
              <p class="text-sm font-medium">{{ environmentResult.environment_name }}</p>
              <TestResultBadge :status="environmentResult.last_result" />
            </div>

            <div class="flex items-center justify-between mt-1 text-xs text-gray-500">
              <span>HTTP: {{ environmentResult.status_received ?? '-' }}</span>
              <span>{{ environmentResult.executed_at ?? 'Sem execução' }}</span>
            </div>

            <div class="mt-2 flex justify-end">
              <Button
                icon="pi pi-eye"
                text
                size="small"
                :disabled="environmentResult.response_body === null || environmentResult.response_body === undefined"
                @click="showJson(environmentResult.response_body)"
              />
            </div>
          </div>
        </div>
      </template>
    </Column>
    <Column header="Request">
      <template #body="slot">
        <Button
          icon="pi pi-eye"
          text
          @click="showJson(slot.data.request_payload)"
        />
      </template>
    </Column>
    <Column header="Status Geral">
      <template #body="slot">
        <div class="flex flex-col gap-1">
          <TestResultBadge :status="slot.data.last_result" />
          <span class="text-xs text-gray-500">
            {{ slot.data.passed_environments || 0 }} OK / {{ slot.data.failed_environments || 0 }} falha
          </span>
        </div>
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

const props = defineProps({
  cases: {
    type: Array,
    default: () => [],
  },
  environments: {
    type: Array,
    default: () => [],
  },
})

const emit = defineEmits(['edit', 'edit-endpoint'])

const jsonVisible = ref(false)
const selectedJson = ref(null)

function showJson(json) {
  selectedJson.value = json
  jsonVisible.value = true
}

function getEnvironmentResults(testCase) {
  const mappedResults = testCase.environment_results || []

  if (mappedResults.length > 0) {
    return mappedResults
  }

  return props.environments.map((environment) => ({
    environment_id: environment.id,
    environment_name: environment.name,
    last_result: null,
    status_received: null,
    response_body: null,
    executed_at: null,
  }))
}
</script>