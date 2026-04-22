<template>
  <AdminLayout>
    <div class="p-4 flex flex-col gap-4">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold">Dashboard de Execucoes</h1>
          <p class="text-sm text-gray-500">Historico de runs e status por suite.</p>
        </div>
      </div>

      <Card>
        <template #content>
          <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
            <div class="flex flex-col gap-1">
              <label class="text-xs text-gray-500">Suite</label>
              <select v-model="filters.suite" class="border rounded-md px-3 py-2 text-sm">
                <option value="">Todas</option>
                <option v-for="name in props.suiteOptions" :key="name" :value="name">{{ name }}</option>
              </select>
            </div>

            <div class="flex flex-col gap-1">
              <label class="text-xs text-gray-500">Status</label>
              <select v-model="filters.status" class="border rounded-md px-3 py-2 text-sm">
                <option value="">Todos</option>
                <option value="success">Sucesso</option>
                <option value="failed">Com falha</option>
              </select>
            </div>

            <div class="flex flex-col gap-1">
              <label class="text-xs text-gray-500">Data inicial</label>
              <input v-model="filters.startDate" type="date" class="border rounded-md px-3 py-2 text-sm" />
            </div>

            <div class="flex flex-col gap-1">
              <label class="text-xs text-gray-500">Data final</label>
              <input v-model="filters.endDate" type="date" class="border rounded-md px-3 py-2 text-sm" />
            </div>
          </div>
          <div class="mt-3 flex gap-2">
            <Button label="Aplicar" icon="pi pi-filter" @click="applyFilters" />
            <Button label="Limpar" icon="pi pi-times" severity="secondary" @click="clearFilters" />
          </div>
        </template>
      </Card>

      <div class="grid grid-cols-1 md:grid-cols-3 xl:grid-cols-6 gap-3">
        <Card>
          <template #content>
            <p class="text-xs text-gray-500 mb-1">Runs Total</p>
            <p class="text-2xl font-bold">{{ props.summary.runs_total }}</p>
          </template>
        </Card>
        <Card>
          <template #content>
            <p class="text-xs text-gray-500 mb-1">Runs Sucesso</p>
            <p class="text-2xl font-bold text-green-600">{{ props.summary.runs_success }}</p>
          </template>
        </Card>
        <Card>
          <template #content>
            <p class="text-xs text-gray-500 mb-1">Runs com Falha</p>
            <p class="text-2xl font-bold text-red-600">{{ props.summary.runs_failed }}</p>
          </template>
        </Card>
        <Card>
          <template #content>
            <p class="text-xs text-gray-500 mb-1">Testes Total</p>
            <p class="text-2xl font-bold">{{ props.summary.tests_total }}</p>
          </template>
        </Card>
        <Card>
          <template #content>
            <p class="text-xs text-gray-500 mb-1">Testes Passaram</p>
            <p class="text-2xl font-bold text-green-600">{{ props.summary.tests_passed }}</p>
          </template>
        </Card>
        <Card>
          <template #content>
            <p class="text-xs text-gray-500 mb-1">Testes Falharam</p>
            <p class="text-2xl font-bold text-red-600">{{ props.summary.tests_failed }}</p>
          </template>
        </Card>
      </div>

      <Card>
        <template #title>Tendencia de Falhas (ultimos 14 dias filtrados)</template>
        <template #content>
          <div v-if="props.trendData.length" class="flex items-end gap-2 h-28">
            <div
              v-for="point in props.trendData"
              :key="point.date"
              class="flex-1 min-w-0 flex flex-col items-center justify-end gap-1"
            >
              <div
                class="w-full rounded-t bg-red-500/80"
                :style="{ height: `${point.height}px` }"
                :title="`${point.label}: ${point.failedRuns} runs com falha`"
              />
              <span class="text-[10px] text-gray-500">{{ point.shortLabel }}</span>
            </div>
          </div>
          <p v-else class="text-sm text-gray-500">Sem dados de falha no periodo filtrado.</p>
        </template>
      </Card>

      <div class="grid grid-cols-1 xl:grid-cols-3 gap-4">
        <Card class="xl:col-span-2">
          <template #title>Historico de Runs</template>
          <template #content>
            <DataTable :value="props.runs.data" stripedRows responsiveLayout="scroll">
              <Column field="suite_name" header="Suite" />
              <Column field="created_at" header="Data" />
              <Column field="total_tests" header="Total" />
              <Column field="passed" header="Passou" />
              <Column field="failed" header="Falhou" />
              <Column header="Status">
                <template #body="slotProps">
                  <Tag
                    :severity="slotProps.data.success ? 'success' : 'danger'"
                    :value="slotProps.data.success ? 'SUCESSO' : 'FALHAS'"
                  />
                </template>
              </Column>
              <Column header="Abrir">
                <template #body="slotProps">
                  <Button
                    icon="pi pi-external-link"
                    text
                    @click="router.visit(`/test-runs/${slotProps.data.id}`)"
                  />
                </template>
              </Column>
            </DataTable>

            <div class="mt-3 flex items-center justify-between gap-2">
              <p class="text-xs text-gray-500">
                Pagina {{ props.runs.current_page }} de {{ props.runs.last_page }}
              </p>
              <div class="flex gap-2">
                <Button
                  size="small"
                  label="Anterior"
                  severity="secondary"
                  :disabled="!props.runs.prev_page_url"
                  @click="goToPage(props.runs.prev_page_url)"
                />
                <Button
                  size="small"
                  label="Proxima"
                  severity="secondary"
                  :disabled="!props.runs.next_page_url"
                  @click="goToPage(props.runs.next_page_url)"
                />
              </div>
            </div>
          </template>
        </Card>

        <Card>
          <template #title>Ultima Execucao por Suite</template>
          <template #content>
            <div class="flex flex-col gap-2">
              <div
                v-for="item in props.latestBySuite"
                :key="`${item.suite_name}-${item.created_at}`"
                class="border rounded-lg p-3"
              >
                <p class="font-medium">{{ item.suite_name }}</p>
                <p class="text-xs text-gray-500 mb-2">{{ item.created_at }}</p>
                <Tag
                  :severity="item.failed === 0 ? 'success' : 'danger'"
                  :value="item.failed === 0 ? 'SUCESSO' : `FALHAS (${item.failed}/${item.total_tests})`"
                />
              </div>
            </div>
          </template>
        </Card>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { reactive } from 'vue'
import { router } from '@inertiajs/vue3'
import AdminLayout from '../../../layouts/AdminLayout.vue'
import Card from 'primevue/card'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Button from 'primevue/button'
import Tag from 'primevue/tag'

const props = defineProps({
  summary: {
    type: Object,
    required: true,
  },
  runs: {
    type: Object,
    required: true,
  },
  latestBySuite: {
    type: Array,
    default: () => [],
  },
  suiteOptions: {
    type: Array,
    default: () => [],
  },
  filters: {
    type: Object,
    default: () => ({
      suite: '',
      status: '',
      startDate: '',
      endDate: '',
    }),
  },
  trendData: {
    type: Array,
    default: () => [],
  },
})

const filters = reactive({
  suite: props.filters.suite || '',
  status: props.filters.status || '',
  startDate: props.filters.startDate || '',
  endDate: props.filters.endDate || '',
})

function applyFilters() {
  router.get('/test-runs', { ...filters }, {
    preserveState: true,
    replace: true,
  })
}

function clearFilters() {
  filters.suite = ''
  filters.status = ''
  filters.startDate = ''
  filters.endDate = ''
  applyFilters()
}

function goToPage(url) {
  if (!url) {
    return
  }

  router.visit(url, {
    preserveState: true,
    replace: true,
  })
}
</script>
