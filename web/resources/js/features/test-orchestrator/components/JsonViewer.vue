<template>
  <div class="relative">
    <button
      class="absolute top-2 right-2 flex items-center gap-1 text-xs text-gray-400 hover:text-white bg-gray-700 hover:bg-gray-600 rounded px-2 py-1 transition-colors"
      @click="copy"
    >
      <i :class="copied ? 'pi pi-check' : 'pi pi-copy'" />
      {{ copied ? 'Copiado!' : 'Copiar' }}
    </button>
    <span
      v-if="copyError"
      class="absolute top-2 right-24 text-xs text-red-300"
    >
      Falha ao copiar
    </span>
    <pre class="bg-gray-900 text-green-400 p-3 rounded text-sm overflow-auto whitespace-pre">{{ formatted }}</pre>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'

const props = defineProps({
  data: [Object, Array, String, Number, Boolean]
})

const copied = ref(false)
const copyError = ref(false)

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

function setCopiedState(success) {
  copied.value = success
  copyError.value = !success

  setTimeout(() => {
    copied.value = false
    copyError.value = false
  }, 2000)
}

function fallbackCopyText(text) {
  const textarea = document.createElement('textarea')
  textarea.value = text
  textarea.setAttribute('readonly', '')
  textarea.style.position = 'fixed'
  textarea.style.top = '-9999px'
  document.body.appendChild(textarea)
  textarea.select()

  let success = false

  try {
    success = document.execCommand('copy')
  } catch (e) {
    success = false
  }

  document.body.removeChild(textarea)

  return success
}

async function copy() {
  const text = String(formatted.value ?? '')

  try {
    if (navigator?.clipboard?.writeText) {
      await navigator.clipboard.writeText(text)
      setCopiedState(true)
      return
    }
  } catch (e) {
    // Falls back to legacy copy below.
  }

  setCopiedState(fallbackCopyText(text))
}
</script>