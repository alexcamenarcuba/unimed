<template>
  <div class="relative">
    <button
      class="absolute top-2 right-2 flex items-center gap-1 text-xs text-gray-400 hover:text-white bg-gray-700 hover:bg-gray-600 rounded px-2 py-1 transition-colors"
      @click="copy"
    >
      <i :class="copied ? 'pi pi-check' : 'pi pi-copy'" />
      {{ copied ? 'Copiado!' : 'Copiar' }}
    </button>
    <pre class="bg-gray-900 text-green-400 p-3 rounded text-sm overflow-auto whitespace-pre">{{ formatted }}</pre>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'

const props = defineProps({
  data: [Object, Array]
})

const copied = ref(false)

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

function copy() {
  navigator.clipboard.writeText(formatted.value ?? '').then(() => {
    copied.value = true
    setTimeout(() => { copied.value = false }, 2000)
  })
}
</script>