<template>
  <div>
    <h1>{{ mensagem }}</h1>
    <p>Laravel + Inertia.js + Vue 3 funcionando! 🚀</p>
  </div>
</template>

<script setup>
defineProps({
  mensagem: String,
});
</script>