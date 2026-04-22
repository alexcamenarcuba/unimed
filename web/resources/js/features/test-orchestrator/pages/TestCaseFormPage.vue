<template>
  <AdminLayout>
  <div class="p-4">
    <h1 class="text-2xl font-bold mb-4">
      {{ testCase ? 'Editar Cenário' : 'Novo Cenário' }}
    </h1>

    <TestCaseForm
      :suiteId="suiteId"
      :testCase="testCase"
      :endpoints="endpoints"
      :initialEndpointId="initialEndpointId"
      @saved="onSaved"
      @cancel="onCancel"
    />
  </div>
  </AdminLayout>
</template>

<script setup>
import { router } from '@inertiajs/vue3'
import TestCaseForm from '../components/TestCaseForm.vue'
import AdminLayout from "../../../layouts/AdminLayout.vue";

const props = defineProps({
  suiteId: [Number, String],
  testCase: Object,
  endpoints: {
    type: Array,
    default: () => []
  },
  initialEndpointId: {
    type: [String, Number],
    default: null
  }
})

function onSaved() {
  router.visit(`/test-suites/${props.suiteId}`)
}

function onCancel() {
  router.visit(`/test-suites/${props.suiteId}`)
}
</script>