<template>
  <div class="p-4">
    <h1 class="text-2xl font-bold mb-4">
      {{ testCase ? 'Editar' : 'Novo Cenário' }}
    </h1>

    <TestCaseForm
      :suiteId="suiteId"
      :testCase="testCase"
      @saved="onSaved"
    />
  </div>
</template>

<script setup>
import { router } from '@inertiajs/vue3'
import TestCaseForm from '../components/TestCaseForm.vue'

defineProps({
  suiteId: Number,
  testCase: Object
})

function onSaved() {
  router.visit(`/suites/${suiteId}`)
}
</script>