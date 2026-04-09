<template>
  <div class="p-4">

    <!-- HEADER -->
    <div class="flex justify-between items-center mb-4">
      <div>
        <h1 class="text-2xl font-bold">{{ suite.name }}</h1>
        <p class="text-sm text-gray-500">
          Base URL: {{ suite.base_url }}
        </p>
      </div>

      <TestRunnerButton :suiteId="suite.id" />
    </div>

    <!-- AÇÃO -->
    <div class="mb-4">
      <Button
        label="Novo Cenário"
        icon="pi pi-plus"
        @click="formVisible = true"
      />
    </div>

    <!-- TABELA -->
    <TestCaseTable :cases="suite.cases" @edit="openEdit" />

    <!-- MODAL CENÁRIO -->
    <TestCaseForm
      v-model="formVisible"
      :suiteId="suite.id"
      :testCase="editingCase"
      @saved="onSaved"
    />

  </div>
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import Button from 'primevue/button'
import TestCaseTable from '../components/TestCaseTable.vue'
import TestRunnerButton from '../components/TestRunnerButton.vue'
import TestCaseForm from '../components/TestCaseForm.vue'

defineProps({
  suite: Object
})

const formVisible = ref(false)
const editingCase = ref(null)

function openEdit(testCase) {
  editingCase.value = testCase
  formVisible.value = true
}

function onSaved() {
  editingCase.value = null
  router.reload({ only: ['suite'] })
}
</script>