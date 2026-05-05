<template>
  <DataTable
    :value="sortedCases"
    stripedRows
    rowGroupMode="subheader"
    groupRowsBy="grupo"
    :sortField="'grupo'"
    :sortOrder="1"
    paginator
    :rows="10"
    :rowsPerPageOptions="[10, 20, 50]"
    paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink RowsPerPageDropdown"
  >
    <template #groupheader="slot">
      <span class="text-sm font-semibold text-gray-600">
        {{ slot.data.grupo || 'Sem grupo' }}
      </span>
    </template>
    <Column headerStyle="width: 3rem">
      <template #header>
        <input
          type="checkbox"
          :checked="allSelected"
          @change="toggleAllSelection($event)"
        >
      </template>
      <template #body="slot">
        <input
          type="checkbox"
          :checked="selectedCaseIds.includes(slot.data.id)"
          @change="toggleCaseSelection(slot.data.id, $event)"
        >
      </template>
    </Column>
    <Column header="Ações">
      <template #body="slot">
        <div class="flex">
          <Button icon="pi pi-pencil" text @click="emit('edit', slot.data)" />
          <Button icon="pi pi-copy" text severity="secondary" @click="emit('duplicate', slot.data)" />
          <Button icon="pi pi-trash" severity="danger" text />
        </div>
      </template>
    </Column>
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
        <div class="flex flex-col gap-2">
          <div
            v-for="environmentResult in getEnvironmentResults(slot.data)"
            :key="`${slot.data.id}-${environmentResult.environment_id}`"
            class="border rounded-md p-2 min-w-48"
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
  </DataTable>

  <!-- MODAL JSON -->
  <Dialog v-model:visible="jsonVisible" header="JSON">
    <JsonViewer :data="selectedJson" />
  </Dialog>
</template>

<script setup>
import { computed, ref } from 'vue'
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
  selectedCaseIds: {
    type: Array,
    default: () => [],
  },
})

const emit = defineEmits(['edit', 'duplicate', 'edit-endpoint', 'update:selectedCaseIds'])

const jsonVisible = ref(false)
const selectedJson = ref(null)
const allSelected = computed(() => props.cases.length > 0 && props.cases.every((testCase) => props.selectedCaseIds.includes(testCase.id)))

const sortedCases = computed(() => {
  return [...props.cases].sort((a, b) => {
    const ga = a.grupo || ''
    const gb = b.grupo || ''
    return ga.localeCompare(gb, 'pt')
  })
})

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

function toggleCaseSelection(caseId, event) {
  const nextSelectedIds = event.target.checked
    ? [...new Set([...props.selectedCaseIds, caseId])]
    : props.selectedCaseIds.filter((selectedId) => selectedId !== caseId)

  emit('update:selectedCaseIds', nextSelectedIds)
}

function toggleAllSelection(event) {
  emit('update:selectedCaseIds', event.target.checked ? props.cases.map((testCase) => testCase.id) : [])
}
</script>