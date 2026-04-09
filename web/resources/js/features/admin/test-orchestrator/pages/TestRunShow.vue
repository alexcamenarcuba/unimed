<template>
  <div class="p-4">

    <!-- HEADER -->
    <div class="mb-4">
      <h1 class="text-2xl font-bold">
        Execução #{{ run.id }}
      </h1>

      <div class="flex gap-4 mt-2 text-sm text-gray-600">
        <span><b>Suite:</b> {{ run.suite.name }}</span>
        <span><b>Data:</b> {{ run.created_at }}</span>
      </div>
    </div>

    <!-- RESUMO -->
    <Card class="mb-4">
      <div class="flex justify-between items-center">

        <div class="flex gap-6">
          <div>
            <p class="text-sm text-gray-500">Total</p>
            <p class="text-xl font-bold">{{ run.results.length }}</p>
          </div>

          <div>
            <p class="text-sm text-gray-500">Passaram</p>
            <p class="text-xl font-bold text-green-600">
              {{ passedCount }}
            </p>
          </div>

          <div>
            <p class="text-sm text-gray-500">Falharam</p>
            <p class="text-xl font-bold text-red-600">
              {{ failedCount }}
            </p>
          </div>
        </div>

        <div>
          <Tag
            :severity="failedCount === 0 ? 'success' : 'danger'"
            :value="failedCount === 0 ? 'SUCESSO' : 'FALHAS'"
          />
        </div>

      </div>
    </Card>

    <!-- LISTA DE RESULTADOS -->
    <div class="flex flex-col gap-3">

      <Card
        v-for="result in run.results"
        :key="result.id"
        class="cursor-pointer hover:shadow-md transition"
        @click="openDetail(result)"
      >
        <div class="flex justify-between items-center">

          <div>
            <p class="font-medium">
              {{ result.test_case.name }}
            </p>

            <p class="text-sm text-gray-500">
              {{ result.test_case.method }}
              {{ result.test_case.endpoint }}
            </p>
          </div>

          <TestResultBadge :status="result.passed" />

        </div>
      </Card>

    </div>

    <!-- MODAL DETALHE -->
    <Dialog
      v-model:visible="detailVisible"
      header="Detalhes do Teste"
      :style="{ width: '800px' }"
      modal
    >

      <div v-if="selectedResult">

        <div class="mb-3">
          <h3 class="font-bold text-lg">
            {{ selectedResult.test_case.name }}
          </h3>

          <TestResultBadge :status="selectedResult.passed" />
        </div>

        <TabView>

          <TabPanel header="Request">
            <JsonViewer :data="selectedResult.request" />
          </TabPanel>

          <TabPanel header="Response">
            <JsonViewer :data="selectedResult.response" />
          </TabPanel>

          <TabPanel header="Expected">
            <JsonViewer :data="selectedResult.expected" />
          </TabPanel>

        </TabView>

      </div>

    </Dialog>

  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import TestResultBadge from '../components/TestResultBadge.vue'
import JsonViewer from '../components/JsonViewer.vue'

const props = defineProps({
  run: Object
})

const detailVisible = ref(false)
const selectedResult = ref(null)

/**
 * Contadores
 */
const passedCount = computed(() =>
  props.run.results.filter(r => r.passed).length
)

const failedCount = computed(() =>
  props.run.results.filter(r => !r.passed).length
)

/**
 * Abrir detalhe
 */
function openDetail(result) {
  selectedResult.value = result
  detailVisible.value = true
}
</script>