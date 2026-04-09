<template>
  <pre class="bg-gray-900 text-green-400 p-3 rounded text-sm overflow-auto">
{{ formatted }}
  </pre>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  data: [Object, Array]
})

const formatted = computed(() =>
  JSON.stringify(props.data, null, 2)
)
</script>