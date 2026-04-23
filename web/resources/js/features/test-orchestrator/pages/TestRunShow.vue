<template>
  <AdminLayout>
    <div class="p-4 flex flex-col gap-4">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold">Execução {{ run.id }}</h1>
          <p class="text-sm text-gray-500">
            Suite: {{ run.suite?.name || '-' }} | Data: {{ run.created_at }}
          </p>
        </div>
        <Button label="Voltar ao Dashboard" icon="pi pi-chart-line" severity="secondary" @click="router.visit('/test-runs')" />
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
        <Card>
          <template #content>
            <p class="text-xs text-gray-500 mb-1">Total</p>
            <p class="text-2xl font-bold">{{ run.results.length }}</p>
          </template>
        </Card>
        <Card>
          <template #content>
            <p class="text-xs text-gray-500 mb-1">Passaram</p>
            <p class="text-2xl font-bold text-green-600">{{ passedCount }}</p>
          </template>
        </Card>
        <Card>
          <template #content>
            <p class="text-xs text-gray-500 mb-1">Falharam</p>
            <p class="text-2xl font-bold text-red-600">{{ failedCount }}</p>
          </template>
        </Card>
      </div>

      <Card>
        <template #title>Resultados</template>
        <template #content>
          <DataTable :value="run.results" stripedRows responsiveLayout="scroll">
            <Column field="test_case.name" header="Cenario" />
            <Column header="Endpoint">
              <template #body="slotProps">
                {{ slotProps.data.test_case.endpoint?.method || '-' }} {{ slotProps.data.test_case.endpoint?.path || '-' }}
              </template>
            </Column>
            <Column header="Ambiente">
              <template #body="slotProps">
                {{ slotProps.data.environment?.name || 'Base URL da Suite' }}
              </template>
            </Column>
            
            <Column field="status_received" header="HTTP" />
            <Column header="Status">
              <template #body="slotProps">
                <Tag
                  :severity="slotProps.data.passed ? 'success' : 'danger'"
                  :value="slotProps.data.passed ? 'PASSOU' : 'FALHOU'"
                />
              </template>
            </Column>
            <Column header="Detalhes">
              <template #body="slotProps">
                <Button icon="pi pi-eye" text @click="openDetail(slotProps.data)" />
              </template>
            </Column>
          </DataTable>
        </template>
      </Card>

      <Dialog
        v-model:visible="detailVisible"
        header="Detalhes da Execução"
        :style="{ width: '900px' }"
        modal
      >
        <div v-if="selectedResult" class="flex flex-col gap-3">
          <div class="flex items-center justify-between">
            <div>
              <h3 class="font-semibold text-lg">{{ selectedResult.test_case?.name }}</h3>
              <p class="text-sm text-gray-500">
                {{ selectedResult.test_case?.endpoint?.method || '-' }} {{ selectedResult.test_case?.endpoint?.path || '-' }}
                | Ambiente: {{ selectedResult.environment?.name || 'Base URL da Suite' }}
              </p>
            </div>
            <Tag
              :severity="selectedResult.passed ? 'success' : 'danger'"
              :value="selectedResult.passed ? 'PASSOU' : 'FALHOU'"
            />
          </div>

          <TabView>
            <TabPanel header="Request Enviado">
              <JsonViewer :data="getSentRequestPayload(selectedResult)" />
            </TabPanel>
            <TabPanel header="Response Recebido">
              <JsonViewer :data="selectedResult.response_body" />
            </TabPanel>
            <TabPanel header="Expected Response">
              <JsonViewer :data="selectedResult.test_case?.expected_response" />
            </TabPanel>
          </TabView>
        </div>
      </Dialog>
    </div>
  </AdminLayout>
</template>

<script setup>
import { computed, ref } from 'vue'
import { router } from '@inertiajs/vue3'
import AdminLayout from '../../../layouts/AdminLayout.vue'
import Card from 'primevue/card'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Tag from 'primevue/tag'
import Button from 'primevue/button'
import Dialog from 'primevue/dialog'
import TabView from 'primevue/tabview'
import TabPanel from 'primevue/tabpanel'
import JsonViewer from '../components/JsonViewer.vue'

const props = defineProps({
  run: {
    type: Object,
    required: true,
  }
})

const detailVisible = ref(false)
const selectedResult = ref(null)

const passedCount = computed(() =>
  props.run.results.filter((r) => r.passed).length
)

const failedCount = computed(() =>
  props.run.results.filter((r) => !r.passed).length
)

function openDetail(result) {
  selectedResult.value = result
  detailVisible.value = true
}

function getSentRequestPayload(result) {
  if (result?.request_payload !== null && result?.request_payload !== undefined) {
    return result.request_payload
  }

  return {
    message: 'Request enviado nao foi armazenado nesta execucao. Rode novamente para capturar este dado.',
  }
}
</script>