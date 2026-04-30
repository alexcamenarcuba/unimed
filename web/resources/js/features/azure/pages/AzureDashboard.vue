<template>
  <AdminLayout>
    <div class="p-4 max-w-7xl mx-auto">
      <!-- HEADER -->
      <div class="flex items-center justify-between mb-6">
        <div>
          <h1 class="text-2xl font-bold text-slate-800">Chamados Azure DevOps</h1>
          <p class="text-sm text-slate-500 mt-1">Itens atribuídos a você · atualizado {{ lastUpdated }}</p>
        </div>
        <Button
          label="Atualizar"
          icon="pi pi-refresh"
          :loading="loading"
          severity="secondary"
          @click="load(true)"
        />
      </div>

      <!-- ERRO -->
      <div v-if="error" class="border border-red-200 bg-red-50 rounded-lg p-4 text-sm text-red-700 mb-6">
        {{ error }}
      </div>

      <!-- SKELETON / LOADING -->
      <div v-if="loading && !items.length" class="flex flex-col gap-4">
        <div v-for="i in 4" :key="i" class="h-20 bg-slate-100 rounded-lg animate-pulse" />
      </div>

      <template v-else>
        <!-- SUMMARY CARDS -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
          <div class="bg-white border rounded-xl p-4 flex flex-col gap-1">
            <p class="text-xs text-slate-500 font-medium uppercase tracking-wide">Total abertos</p>
            <p class="text-3xl font-bold text-slate-800">{{ items.length }}</p>
          </div>
          <div class="bg-white border rounded-xl p-4 flex flex-col gap-1">
            <p class="text-xs text-slate-500 font-medium uppercase tracking-wide">Em andamento</p>
            <p class="text-3xl font-bold text-blue-600">{{ countByState('Active') + countByState('In Progress') }}</p>
          </div>
          <div class="bg-white border rounded-xl p-4 flex flex-col gap-1">
            <p class="text-xs text-slate-500 font-medium uppercase tracking-wide">A fazer</p>
            <p class="text-3xl font-bold text-slate-600">{{ countByState('New') + countByState('To Do') }}</p>
          </div>
          <div class="bg-white border rounded-xl p-4 flex flex-col gap-1 border-red-200 bg-red-50/50">
            <p class="text-xs text-red-500 font-medium uppercase tracking-wide">Em atraso</p>
            <p class="text-3xl font-bold text-red-600">{{ overdueCount }}</p>
          </div>
        </div>

        <!-- CHARTS ROW -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
          <!-- Por status -->
          <div class="bg-white border rounded-xl p-5">
            <p class="text-sm font-semibold text-slate-700 mb-4">Por status</p>
            <div class="flex flex-col gap-3">
              <div v-for="(count, state) in stateCounts" :key="state" class="flex items-center gap-3">
                <span class="text-xs text-slate-600 w-32 shrink-0 truncate" :title="state">{{ state }}</span>
                <div class="flex-1 bg-slate-100 rounded-full h-5 overflow-hidden">
                  <div
                    class="h-5 rounded-full transition-all duration-500"
                    :class="stateBarColor(state)"
                    :style="{ width: percent(count, items.length) + '%' }"
                  />
                </div>
                <span class="text-xs font-medium text-slate-700 w-6 text-right">{{ count }}</span>
              </div>
            </div>
          </div>

          <!-- Por tipo -->
          <div class="bg-white border rounded-xl p-5">
            <p class="text-sm font-semibold text-slate-700 mb-4">Por tipo</p>
            <div class="flex flex-col gap-3">
              <div v-for="(count, type) in typeCounts" :key="type" class="flex items-center gap-3">
                <span class="text-xs text-slate-600 w-32 shrink-0 truncate" :title="type">{{ type }}</span>
                <div class="flex-1 bg-slate-100 rounded-full h-5 overflow-hidden">
                  <div
                    class="h-5 rounded-full bg-emerald-400 transition-all duration-500"
                    :style="{ width: percent(count, items.length) + '%' }"
                  />
                </div>
                <span class="text-xs font-medium text-slate-700 w-6 text-right">{{ count }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Por solicitante -->
        <div class="bg-white border rounded-xl p-5 mb-6">
          <p class="text-sm font-semibold text-slate-700 mb-4">Por solicitante</p>
          <div v-if="Object.keys(solicitanteCounts).length" class="flex flex-col gap-3">
            <div v-for="(count, solicitante) in solicitanteCounts" :key="solicitante" class="flex items-center gap-3">
              <span class="text-xs text-slate-600 w-44 shrink-0 truncate" :title="solicitante">{{ solicitante }}</span>
              <div class="flex-1 bg-slate-100 rounded-full h-5 overflow-hidden">
                <div
                  class="h-5 rounded-full bg-violet-400 transition-all duration-500"
                  :style="{ width: percent(count, items.length) + '%' }"
                />
              </div>
              <span class="text-xs font-medium text-slate-700 w-6 text-right">{{ count }}</span>
            </div>
          </div>
          <p v-else class="text-sm text-slate-400">Nenhum solicitante identificado.</p>
        </div>

        <!-- FILTERS -->
        <div class="bg-white border rounded-xl p-4 mb-4 flex flex-wrap gap-3 items-end">
          <div class="flex-1 min-w-40">
            <label class="block text-xs text-slate-500 mb-1">Buscar</label>
            <InputText v-model="filters.search" placeholder="Título ou ID..." class="w-full" size="small" />
          </div>
          <div class="min-w-40">
            <label class="block text-xs text-slate-500 mb-1">Status</label>
            <Select
              v-model="filters.state"
              :options="stateOptions"
              optionLabel="label"
              optionValue="value"
              placeholder="Todos"
              class="w-full"
              size="small"
              showClear
            />
          </div>
          <div class="min-w-40">
            <label class="block text-xs text-slate-500 mb-1">Tipo</label>
            <Select
              v-model="filters.type"
              :options="typeOptions"
              optionLabel="label"
              optionValue="value"
              placeholder="Todos"
              class="w-full"
              size="small"
              showClear
            />
          </div>
          <div class="min-w-40">
            <label class="block text-xs text-slate-500 mb-1">Sprint</label>
            <Select
              v-model="filters.sprint"
              :options="sprintOptions"
              optionLabel="label"
              optionValue="value"
              placeholder="Todos"
              class="w-full"
              size="small"
              showClear
            />
          </div>
          <div class="min-w-40">
            <label class="block text-xs text-slate-500 mb-1">Solicitante</label>
            <Select
              v-model="filters.solicitante"
              :options="solicitanteOptions"
              optionLabel="label"
              optionValue="value"
              placeholder="Todos"
              class="w-full"
              size="small"
              showClear
            />
          </div>
          <div class="flex items-center gap-2 pb-0.5">
            <Checkbox v-model="filters.onlyOverdue" inputId="overdue-filter" binary />
            <label for="overdue-filter" class="text-sm text-slate-600 cursor-pointer select-none">Só atrasados</label>
          </div>
        </div>

        <!-- TABLE -->
        <div class="bg-white border rounded-xl overflow-hidden">
          <DataTable
            :value="filteredItems"
            :rows="20"
            paginator
            paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink RowsPerPageDropdown"
            :rowsPerPageOptions="[10, 20, 50]"
            stripedRows
            size="small"
            :sortField="'priority'"
            :sortOrder="1"
          >
            <Column field="id" header="ID" sortable style="width: 70px">
              <template #body="slot">
                <a :href="slot.data.url" target="_blank" class="text-blue-600 hover:underline font-mono text-xs">
                  #{{ slot.data.id }}
                </a>
              </template>
            </Column>

            <Column field="title" header="Título" sortable>
              <template #body="slot">
                <div class="flex items-start gap-2">
                  <div>
                    <a :href="slot.data.url" target="_blank" class="text-sm font-medium text-slate-800 hover:text-blue-600 hover:underline leading-tight">
                      {{ slot.data.title }}
                    </a>
                    <p v-if="slot.data.solicitante" class="text-xs text-slate-400 mt-0.5">Solicitante: {{ slot.data.solicitante }}</p>
                  </div>
                  <span v-if="slot.data.is_overdue" class="shrink-0 mt-0.5 inline-flex items-center gap-1 text-xs bg-red-100 text-red-600 px-1.5 py-0.5 rounded-full font-medium">
                    <i class="pi pi-clock text-[10px]" /> Atrasado
                  </span>
                </div>
              </template>
            </Column>

            <Column field="type" header="Tipo" sortable style="width: 130px">
              <template #body="slot">
                <Tag :value="slot.data.type" :severity="typeSeverity(slot.data.type)" />
              </template>
            </Column>

            <Column field="state" header="Status" sortable style="width: 130px">
              <template #body="slot">
                <Tag :value="slot.data.state" :severity="stateSeverity(slot.data.state)" />
              </template>
            </Column>

            <Column field="sprint" header="Sprint" sortable style="width: 130px">
              <template #body="slot">
                <span class="text-xs text-slate-600">{{ slot.data.sprint || '-' }}</span>
              </template>
            </Column>

            <Column field="priority" header="P" sortable style="width: 50px">
              <template #body="slot">
                <span class="text-xs font-bold" :class="priorityColor(slot.data.priority)">{{ slot.data.priority || '-' }}</span>
              </template>
            </Column>

            <Column field="due_date" header="Prazo" sortable style="width: 100px">
              <template #body="slot">
                <span v-if="slot.data.due_date" class="text-xs" :class="slot.data.is_overdue ? 'text-red-600 font-semibold' : 'text-slate-600'">
                  {{ formatDate(slot.data.due_date) }}
                </span>
                <span v-else class="text-xs text-slate-300">—</span>
              </template>
            </Column>

            <Column header="" style="width: 40px">
              <template #body="slot">
                <a :href="slot.data.url" target="_blank" class="text-slate-400 hover:text-blue-600">
                  <i class="pi pi-external-link text-xs" />
                </a>
              </template>
            </Column>

            <template #empty>
              <p class="text-center text-slate-400 py-8 text-sm">Nenhum item encontrado.</p>
            </template>
          </DataTable>
        </div>
      </template>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'
import AdminLayout from '../../../layouts/AdminLayout.vue'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import Select from 'primevue/select'
import Checkbox from 'primevue/checkbox'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Tag from 'primevue/tag'

const items = ref([])
const loading = ref(false)
const error = ref(null)
const lastUpdated = ref('—')

const filters = ref({
  search: '',
  state: null,
  type: null,
  sprint: null,
  solicitante: null,
  onlyOverdue: false,
})

async function load(forceRefresh = false) {
  loading.value = true
  error.value = null

  try {
    const { data } = await axios.get('/tickets/work-items', {
      params: forceRefresh ? { refresh: 1 } : {},
    })
    items.value = data
    lastUpdated.value = new Date().toLocaleTimeString('pt-BR')
  } catch (e) {
    error.value = 'Não foi possível carregar os itens do Azure DevOps.'
  } finally {
    loading.value = false
  }
}

onMounted(() => load())

// --- COMPUTED COUNTS ---

const stateCounts = computed(() => {
  const counts = {}
  for (const item of items.value) {
    counts[item.state] = (counts[item.state] ?? 0) + 1
  }
  return counts
})

const typeCounts = computed(() => {
  const counts = {}
  for (const item of items.value) {
    counts[item.type] = (counts[item.type] ?? 0) + 1
  }
  return counts
})

const solicitanteCounts = computed(() => {
  const counts = {}
  for (const item of items.value) {
    if (!item.solicitante) continue
    counts[item.solicitante] = (counts[item.solicitante] ?? 0) + 1
  }
  // Ordenar do maior para o menor
  return Object.fromEntries(
    Object.entries(counts).sort(([, a], [, b]) => b - a)
  )
})

const overdueCount = computed(() => items.value.filter((i) => i.is_overdue).length)

function countByState(...states) {
  return items.value.filter((i) => states.includes(i.state)).length
}

// --- FILTER OPTIONS ---

const stateOptions = computed(() =>
  [...new Set(items.value.map((i) => i.state))].sort().map((s) => ({ label: s, value: s }))
)
const typeOptions = computed(() =>
  [...new Set(items.value.map((i) => i.type))].sort().map((t) => ({ label: t, value: t }))
)
const sprintOptions = computed(() =>
  [...new Set(items.value.map((i) => i.sprint).filter(Boolean))].sort().map((s) => ({ label: s, value: s }))
)
const solicitanteOptions = computed(() =>
  [...new Set(items.value.map((i) => i.solicitante).filter(Boolean))].sort().map((f) => ({ label: f, value: f }))
)

// --- FILTERED ---

const filteredItems = computed(() => {
  let result = items.value

  if (filters.value.search) {
    const q = filters.value.search.toLowerCase()
    result = result.filter((i) =>
      String(i.id).includes(q) || i.title.toLowerCase().includes(q)
    )
  }
  if (filters.value.state) {
    result = result.filter((i) => i.state === filters.value.state)
  }
  if (filters.value.type) {
    result = result.filter((i) => i.type === filters.value.type)
  }
  if (filters.value.sprint) {
    result = result.filter((i) => i.sprint === filters.value.sprint)
  }
  if (filters.value.solicitante) {
    result = result.filter((i) => i.solicitante === filters.value.solicitante)
  }
  if (filters.value.onlyOverdue) {
    result = result.filter((i) => i.is_overdue)
  }

  return result
})

// --- HELPERS ---

function percent(value, total) {
  if (!total) return 0
  return Math.round((value / total) * 100)
}

function formatDate(dateStr) {
  if (!dateStr) return ''
  return new Date(dateStr).toLocaleDateString('pt-BR')
}

function stateBarColor(state) {
  const s = (state || '').toLowerCase()
  if (s.includes('progress') || s === 'active') return 'bg-blue-400'
  if (s === 'new' || s.includes('do')) return 'bg-slate-400'
  if (s.includes('done') || s.includes('closed')) return 'bg-emerald-400'
  return 'bg-violet-400'
}

function stateSeverity(state) {
  const s = (state || '').toLowerCase()
  if (s.includes('progress') || s === 'active') return 'info'
  if (s === 'new' || s.includes('to do')) return 'secondary'
  if (s.includes('done') || s.includes('closed')) return 'success'
  return 'warn'
}

function typeSeverity(type) {
  const t = (type || '').toLowerCase()
  if (t === 'bug') return 'danger'
  if (t === 'user story' || t === 'story') return 'success'
  if (t === 'task') return 'info'
  return 'secondary'
}

function priorityColor(priority) {
  if (priority === 1) return 'text-red-600'
  if (priority === 2) return 'text-orange-500'
  return 'text-slate-400'
}
</script>
