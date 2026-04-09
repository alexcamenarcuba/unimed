<template>
  <Button
    label="Rodar Testes"
    icon="pi pi-play"
    severity="success"
    :loading="loading"
    @click="run"
  />
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import Button from 'primevue/button'

const props = defineProps({
  suiteId: [Number, String]
})

const loading = ref(false)

function run() {
  loading.value = true

  router.post(`/test-suites/${props.suiteId}/run`, {}, {
    onFinish: () => { loading.value = false },
  })
}
</script>