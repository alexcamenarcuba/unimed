<template>
  <pre class="bg-gray-900 text-green-400 p-3 rounded text-sm overflow-auto break-all whitespace-pre-wrap">
    {{ formatted }}
  </pre>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  data: [Object, Array]
})

const formatted = computed(() => {
  try {
    const parsed = typeof props.data === 'string'
      ? JSON.parse(props.data)
      : props.data

    return JSON.stringify(parsed, null, 2)
  } catch (e) {
    return props.data
  }
})
</script>