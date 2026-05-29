<script setup>
defineProps({
  message: {
    type: String,
    default: 'Une erreur est survenue',
  },
})
</script>

<template>
  <div class="text-center text-red-500 py-12">
    <i class="ti ti-alert-circle text-3xl"></i>
    <p class="mt-2">{{ message }}</p>
  </div>
</template>

<style scoped></style>
