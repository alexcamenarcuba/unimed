<template>
  <Button
    label="Rodar Testes"
    icon="pi pi-play"
    severity="success"
    :loading="loading"
    :disabled="disabled"
    @click="run"
  />
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import Button from 'primevue/button'

const props = defineProps({
  suiteId: [Number, String],
  testCaseIds: {
    type: Array,
    default: () => [],
  },
  disabled: {
    type: Boolean,
    default: false,
  },
})

const loading = ref(false)

function run() {
  loading.value = true

  router.post(`/test-suites/${props.suiteId}/run`, {
    test_case_ids: props.testCaseIds,
  }, {
    onFinish: () => { loading.value = false },
  })
}
</script>