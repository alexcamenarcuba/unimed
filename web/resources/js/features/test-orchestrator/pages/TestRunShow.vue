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
        <div class="flex items-center gap-2">
          <Button
            v-if="run.suite?.id"
            label="Voltar para Suite"
            icon="pi pi-arrow-left"
            severity="secondary"
            @click="goToSuite"
          />
          <Button
            v-if="run.suite?.id"
            label="Executar Novamente"
            icon="pi pi-refresh"
            severity="success"
            :loading="rerunning"
            @click="rerunLastExecution"
          />
          <Button
            label="Exportar PDF"
            icon="pi pi-file-pdf"
            severity="secondary"
            :loading="generatingPdf"
            @click="generatePdf"
          />
          <Button label="Voltar ao Dashboard" icon="pi pi-chart-line" severity="secondary" @click="router.visit('/test-runs')" />
        </div>
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
            <Column field="test_case.name" header="Cenário" />
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
              <div class="mb-3 flex items-center gap-2 font-mono text-sm bg-gray-100 rounded px-3 py-2">
                <span class="font-bold text-blue-700">{{ selectedResult.test_case?.endpoint?.method || '-' }}</span>
                <span class="text-gray-700 break-all">{{ getFullUrl(selectedResult) }}</span>
              </div>
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
import { useToast } from 'primevue/usetoast'
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

const generatingPdf = ref(false)
const rerunning = ref(false)
const toast = useToast()

async function generatePdf() {
  generatingPdf.value = true

  try {
    const { default: jsPDF } = await import('jspdf')
    const { default: autoTable } = await import('jspdf-autotable')

    const doc = new jsPDF({ orientation: 'landscape', unit: 'mm', format: 'a4' })

    const suiteName = props.run.suite?.name || '-'
    const runDate = props.run.created_at || '-'
    const total = props.run.results.length
    const passed = passedCount.value
    const failed = failedCount.value

    // Cabeçalho
    doc.setFontSize(16)
    doc.setFont('helvetica', 'bold')
    doc.text('Relatório de Execução de Testes', 14, 16)

    doc.setFontSize(10)
    doc.setFont('helvetica', 'normal')
    doc.text(`Suite: ${suiteName}`, 14, 24)
    doc.text(`Data: ${runDate}`, 14, 30)
    doc.text(`ID da execução: ${props.run.id}`, 14, 36)

    doc.setFont('helvetica', 'bold')
    doc.text(`Total: ${total}   Passaram: ${passed}   Falharam: ${failed}`, 14, 44)

    // Tabela de resultados
    const rows = props.run.results.map((result) => {
      const method = result.test_case?.endpoint?.method || '-'
      const path = result.test_case?.endpoint?.path || '-'
      const ambiente = result.environment?.name || 'Base URL da Suite'
      const status = result.passed ? 'PASSOU' : 'FALHOU'
      const httpCode = result.status_received ?? '-'
      const request = result.request_payload != null
        ? JSON.stringify(result.request_payload, null, 2)
        : '(não registrado)'
      const response = result.response_body != null
        ? JSON.stringify(result.response_body, null, 2)
        : '(sem resposta)'

      return [
        result.test_case?.name || '-',
        `${method} ${path}`,
        ambiente,
        String(httpCode),
        status,
        request,
        response,
      ]
    })

    autoTable(doc, {
      startY: 50,
      head: [['Cenário', 'Endpoint', 'Ambiente', 'HTTP Code', 'Status', 'Request Enviado', 'Response Recebido']],
      body: rows,
      styles: { fontSize: 7, cellPadding: 2, overflow: 'linebreak', valign: 'top' },
      headStyles: { fillColor: [30, 150, 100], textColor: 255, fontStyle: 'bold' },
      columnStyles: {
        0: { cellWidth: 40 },
        1: { cellWidth: 40 },
        2: { cellWidth: 30 },
        3: { cellWidth: 18 },
        4: { cellWidth: 18 },
        5: { cellWidth: 60, font: 'courier' },
        6: { cellWidth: 60, font: 'courier' },
      },
      didParseCell(data) {
        if (data.column.index === 4) {
          if (data.cell.raw === 'PASSOU') {
            data.cell.styles.textColor = [22, 163, 74]
            data.cell.styles.fontStyle = 'bold'
          } else if (data.cell.raw === 'FALHOU') {
            data.cell.styles.textColor = [220, 38, 38]
            data.cell.styles.fontStyle = 'bold'
          }
        }
      },
    })

    const filename = `relatorio-execucao-${props.run.id.substring(0, 8)}.pdf`
    doc.save(filename)
  } catch (e) {
    console.error('Erro ao gerar PDF:', e)
    toast.add({
      severity: 'error',
      summary: 'Falha ao gerar PDF',
      detail: 'Erro ao gerar PDF. Tente novamente.',
      life: 4500,
    })
  } finally {
    generatingPdf.value = false
  }
}

const detailVisible = ref(false)
const selectedResult = ref(null)

const passedCount = computed(() =>
  props.run.results.filter((r) => r.passed).length
)

const failedCount = computed(() =>
  props.run.results.filter((r) => !r.passed).length
)

const lastExecutedCaseIds = computed(() => {
  const ids = props.run.results
    .map((result) => result?.test_case_id)
    .filter((id) => Boolean(id))

  return [...new Set(ids)]
})

function openDetail(result) {
  selectedResult.value = result
  detailVisible.value = true
}

function goToSuite() {
  if (!props.run.suite?.id) {
    return
  }

  router.visit(`/test-suites/${props.run.suite.id}`)
}

function rerunLastExecution() {
  if (!props.run.suite?.id) {
    return
  }

  rerunning.value = true

  router.post(`/test-suites/${props.run.suite.id}/run`, {
    test_case_ids: lastExecutedCaseIds.value,
  }, {
    onFinish: () => {
      rerunning.value = false
    },
  })
}

function getSentRequestPayload(result) {
  if (result?.request_payload !== null && result?.request_payload !== undefined) {
    return result.request_payload
  }

  return {
    message: 'Request enviado nao foi armazenado nesta execucao. Rode novamente para capturar este dado.',
  }
}

function getFullUrl(result) {
  const baseUrl = result?.environment?.base_url?.replace(/\/$/, '') ?? ''
  const path = result?.test_case?.endpoint?.path?.replace(/^\//, '') ?? ''
  if (!baseUrl && !path) return '-'
  return baseUrl ? `${baseUrl}/${path}` : path
}
</script>