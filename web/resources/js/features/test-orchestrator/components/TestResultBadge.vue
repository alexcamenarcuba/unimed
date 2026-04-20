<template>
  <Tag
    :severity="status === null || status === undefined ? 'secondary' : (status ? 'success' : 'danger')"
    :value="status === null || status === undefined ? 'SEM EXECUCAO' : (status ? 'PASSOU' : 'FALHOU')"
  />
</template>

<script setup>
import Tag from 'primevue/tag'

defineProps({
  status: {
    type: [Boolean, Number, null],
    default: null,
  }
})
</script>