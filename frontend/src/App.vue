<template>
  <div>
    <p>{{ message }}</p>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const message = ref('Chargement...')

onMounted(async () => {
  const res = await fetch(`${import.meta.env.VITE_API_URL}/api/ping`)
  const data = await res.json()
  message.value = data.message
})
</script>

<style scoped></style>