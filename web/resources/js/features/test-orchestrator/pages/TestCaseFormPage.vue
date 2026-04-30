<template>
  <AdminLayout>
  <div class="p-4">
    <div class="mb-4 flex items-center justify-between gap-3">
      <h1 class="text-2xl font-bold">
        {{ pageTitle }}
      </h1>

      <Button
        v-if="testCase"
        label="Duplicar"
        icon="pi pi-copy"
        severity="secondary"
        @click="duplicateCase"
      />
    </div>

    <TestCaseForm
      :suiteId="suiteId"
      :testCase="testCase"
      :duplicateSourceCase="duplicateSourceCase"
      :groups="groups"
      :environments="environments"
      :endpoints="endpoints"
      :initialEndpointId="initialEndpointId"
      @saved="onSaved"
      @cancel="onCancel"
    />
  </div>
  </AdminLayout>
</template>

<script setup>
import { computed } from 'vue'
import { router } from '@inertiajs/vue3'
import TestCaseForm from '../components/TestCaseForm.vue'
import AdminLayout from "../../../layouts/AdminLayout.vue";
import Button from 'primevue/button'

const props = defineProps({
  suiteId: [Number, String],
  testCase: Object,
  duplicateSourceCase: Object,
  groups: {
    type: Array,
    default: () => []
  },
  environments: {
    type: Array,
    default: () => []
  },
  endpoints: {
    type: Array,
    default: () => []
  },
  initialEndpointId: {
    type: [String, Number],
    default: null
  }
})

const pageTitle = computed(() => {
  if (props.testCase) {
    return 'Editar Cenário'
  }

  if (props.duplicateSourceCase) {
    return 'Duplicar Cenário'
  }

  return 'Novo Cenário'
})

function onSaved() {
  router.visit(`/test-suites/${props.suiteId}`)
}

function onCancel() {
  router.visit(`/test-suites/${props.suiteId}`)
}

function duplicateCase() {
  router.visit(`/test-suites/${props.suiteId}/cases/create?clone_from=${props.testCase.id}`)
}
</script>