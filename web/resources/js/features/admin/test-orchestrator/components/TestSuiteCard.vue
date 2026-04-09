<template>
  <Card class="cursor-pointer hover:shadow-lg transition"
        @click="$inertia.visit(`/test-suites/${suite.id}`)">

    <template #title>
      {{ suite.name }}
    </template>

    <template #content>
      <div class="flex justify-between items-center">
        <span>{{ suite.cases_count || 0 }} testes</span>

        <TestResultBadge :status="suite.last_status" />
      </div>
    </template>

  </Card>
</template>

<script setup>
import TestResultBadge from './TestResultBadge.vue'

defineProps({
  suite: Object
})
</script>