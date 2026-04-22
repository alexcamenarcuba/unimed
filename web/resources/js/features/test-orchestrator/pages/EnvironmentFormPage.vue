<template>
  <AdminLayout>
    <div class="p-4 max-w-3xl">
      <h1 class="text-2xl font-bold mb-1">
        {{ environment ? 'Editar Ambiente' : 'Novo Ambiente' }}
      </h1>
      <p class="text-sm text-gray-500 mb-6">
        Suite: {{ suite.name }}
      </p>

      <EnvironmentForm
        :suiteId="suite.id"
        :environment="environment"
        @saved="onSaved"
        @cancel="onCancel"
      />
    </div>
  </AdminLayout>
</template>

<script setup>
import { router } from '@inertiajs/vue3'
import AdminLayout from '../../../layouts/AdminLayout.vue'
import EnvironmentForm from '../components/EnvironmentForm.vue'

const props = defineProps({
  suite: {
    type: Object,
    required: true,
  },
  environment: {
    type: Object,
    default: null,
  },
})

function onSaved() {
  router.visit(`/test-suites/${props.suite.id}`)
}

function onCancel() {
  router.visit(`/test-suites/${props.suite.id}`)
}
</script>
