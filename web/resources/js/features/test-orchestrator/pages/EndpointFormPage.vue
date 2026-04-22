<template>
  <AdminLayout>
    <div class="p-4 max-w-2xl">
      <h1 class="text-2xl font-bold mb-1">
        {{ endpoint ? 'Editar Endpoint' : 'Novo Endpoint' }}
      </h1>
      <p class="text-sm text-gray-500 mb-6">
        Suite: {{ suite.name }}
      </p>

      <EndpointForm
        :suiteId="suite.id"
        :endpoint="endpoint"
        @saved="onSaved"
        @cancel="onCancel"
      />
    </div>
  </AdminLayout>
</template>

<script setup>
import { router } from '@inertiajs/vue3'
import AdminLayout from '../../../layouts/AdminLayout.vue'
import EndpointForm from '../components/EndpointForm.vue'

const props = defineProps({
  suite: {
    type: Object,
    required: true,
  },
  endpoint: {
    type: Object,
    default: null,
  },
})

function onSaved(endpointId) {
  if (props.endpoint) {
    router.visit(`/test-suites/${props.suite.id}`)
    return
  }

  router.visit(`/test-suites/${props.suite.id}/cases/create?endpoint_id=${endpointId}`)
}

function onCancel() {
  if (props.endpoint) {
    router.visit(`/test-suites/${props.suite.id}`)
    return
  }

  router.visit(`/test-suites/${props.suite.id}`)
}
</script>
