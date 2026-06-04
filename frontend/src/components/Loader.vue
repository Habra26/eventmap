<script setup>
defineProps({
  message: {
    type: String,
    default: 'Chargement...',
  },
})
</script>

<template>
  <div class="text-center text-gray-500 py-12">
    <i class="ti ti-loader text-3xl animate-spin"></i>
    <p class="mt-2">{{ message }}</p>
  </div>
</template>

<style scoped></style>
